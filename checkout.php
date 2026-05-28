<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}

if (empty($_SESSION['username']) || empty($_SESSION['user_id'])) {
    header('Location: login.php?return_to=cart.php');
    exit;
}

if (!isset($pdo)) {
    header('Location: cart.php?booking=database_error');
    exit;
}

$currentUser = appLoadCurrentUser($pdo);

if (!$currentUser) {
    header('Location: login.php?return_to=cart.php');
    exit;
}

if ((int) ($currentUser['is_blocked'] ?? 0) === 1) {
    header('Location: cart.php?booking=blocked');
    exit;
}

$cartPayload = $_POST['cart_payload'] ?? '';
$cartItems = json_decode($cartPayload, true);

if (!is_array($cartItems) || count($cartItems) === 0) {
    header('Location: cart.php?booking=empty');
    exit;
}

$normalizedItems = [];
$subtotalAmount = 0.0;

foreach ($cartItems as $item) {
    $productId = isset($item['id']) ? (int) $item['id'] : null;
    $productName = trim((string) ($item['name'] ?? ''));
    $unitPrice = (float) ($item['price'] ?? 0);
    $quantity = (int) ($item['menge'] ?? 0);
    $image = trim((string) ($item['image'] ?? ''));

    if ($productName === '' || $unitPrice <= 0 || $quantity <= 0) {
        continue;
    }

    $lineTotal = $unitPrice * $quantity;
    $subtotalAmount += $lineTotal;

    $normalizedItems[] = [
        'product_id' => $productId,
        'product_name' => $productName,
        'unit_price' => $unitPrice,
        'quantity' => $quantity,
        'image' => $image !== '' ? $image : null,
    ];
}

if ($normalizedItems === []) {
    header('Location: cart.php?booking=empty');
    exit;
}

try {
    $pdo->beginTransaction();

    $bookingInsert = $pdo->prepare(
        'INSERT INTO bookings (user_id, subtotal_amount, total_amount, discount_percent, discount_amount, discount_label, status)
         VALUES (:user_id, :subtotal_amount, :total_amount, :discount_percent, :discount_amount, :discount_label, :status)'
    );
    $bookingInsert->execute([
        'user_id' => (int) $_SESSION['user_id'],
        'subtotal_amount' => $subtotalAmount,
        'total_amount' => $subtotalAmount,
        'discount_percent' => 0,
        'discount_amount' => 0,
        'discount_label' => null,
        'status' => 'new',
    ]);

    $bookingId = (int) $pdo->lastInsertId();
    $discountConfig = appGetDiscountConfig($pdo);
    $discount = appCalculateBookingDiscount($bookingId, $subtotalAmount, $discountConfig);

    if (($discount['amount'] ?? 0) > 0) {
        $updateBooking = $pdo->prepare(
            'UPDATE bookings
             SET total_amount = :total_amount,
                 discount_percent = :discount_percent,
                 discount_amount = :discount_amount,
                 discount_label = :discount_label
             WHERE id = :booking_id'
        );
        $updateBooking->execute([
            'total_amount' => $discount['final_total'],
            'discount_percent' => $discount['percent'],
            'discount_amount' => $discount['amount'],
            'discount_label' => $discount['label'],
            'booking_id' => $bookingId,
        ]);
    } else {
        $updateBooking = $pdo->prepare(
            'UPDATE bookings
             SET total_amount = :total_amount
             WHERE id = :booking_id'
        );
        $updateBooking->execute([
            'total_amount' => $subtotalAmount,
            'booking_id' => $bookingId,
        ]);
    }

    $itemInsert = $pdo->prepare(
        'INSERT INTO booking_items (
            booking_id,
            product_id,
            product_name,
            unit_price,
            quantity,
            image
        ) VALUES (
            :booking_id,
            :product_id,
            :product_name,
            :unit_price,
            :quantity,
            :image
        )'
    );

    foreach ($normalizedItems as $item) {
        $itemInsert->execute([
            'booking_id' => $bookingId,
            'product_id' => $item['product_id'],
            'product_name' => $item['product_name'],
            'unit_price' => $item['unit_price'],
            'quantity' => $item['quantity'],
            'image' => $item['image'],
        ]);
    }

    $pdo->commit();
} catch (Throwable $throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: cart.php?booking=error');
    exit;
}

header('Location: cart.php?booking=success');
exit;
