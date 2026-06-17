<?php

require_once __DIR__ . '/_bootstrap.php';

appRequireLogin('cart.php');

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
    $automaticDiscount = appCalculateBookingDiscount($bookingId, $subtotalAmount, $discountConfig);
    $discountAmount = (float) ($automaticDiscount['amount'] ?? 0);
    $discountPercent = $subtotalAmount > 0
        ? round(($discountAmount / $subtotalAmount) * 100, 2)
        : 0.0;

    $discountLabels = [];
    if (($automaticDiscount['amount'] ?? 0) > 0 && !empty($automaticDiscount['label'])) {
        $discountLabels[] = (string) $automaticDiscount['label'];
    }

    $finalTotal = max(0, round($subtotalAmount - $discountAmount, 2));
    $discountLabel = $discountLabels !== [] ? implode(' + ', $discountLabels) : null;

    $updateBooking = $pdo->prepare(
        'UPDATE bookings
         SET total_amount = :total_amount,
             discount_percent = :discount_percent,
             discount_amount = :discount_amount,
             discount_label = :discount_label
         WHERE id = :booking_id'
    );
    $updateBooking->execute([
        'total_amount' => $finalTotal,
        'discount_percent' => $discountPercent,
        'discount_amount' => $discountAmount,
        'discount_label' => $discountLabel,
        'booking_id' => $bookingId,
    ]);

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

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=cart.php?booking=success">
    <title>Buchung abgeschlossen</title>
</head>
<body>
    <script>
        try {
            localStorage.removeItem('cart');
        } catch (error) {
        }
        window.location.replace('cart.php?booking=success');
    </script>
    <noscript>
        <p>Die Buchung wurde abgeschlossen. <a href="cart.php?booking=success">Weiter</a>.</p>
    </noscript>
</body>
</html>
<?php
exit;
