<?php
session_start();
require_once __DIR__ . '/db.php';

if (empty($_SESSION['username']) || empty($_SESSION['user_id'])) {
    header('Location: login.php?return_to=cart.php');
    exit;
}

if (!isset($pdo)) {
    header('Location: cart.php?booking=database_error');
    exit;
}

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS bookings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS booking_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_id INT UNSIGNED NOT NULL,
        product_id INT UNSIGNED NULL,
        product_name VARCHAR(255) NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        quantity INT UNSIGNED NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (booking_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$cartPayload = $_POST['cart_payload'] ?? '';
$cartItems = json_decode($cartPayload, true);

if (!is_array($cartItems) || count($cartItems) === 0) {
    header('Location: cart.php?booking=empty');
    exit;
}

$normalizedItems = [];
$totalAmount = 0.0;

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
    $totalAmount += $lineTotal;

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
        'INSERT INTO bookings (user_id, total_amount)
         VALUES (:user_id, :total_amount)'
    );
    $bookingInsert->execute([
        'user_id' => (int) $_SESSION['user_id'],
        'total_amount' => $totalAmount,
    ]);

    $bookingId = (int) $pdo->lastInsertId();

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
