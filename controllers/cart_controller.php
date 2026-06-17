<?php

require_once __DIR__ . '/_bootstrap.php';

$isLoggedIn = appIsLoggedIn();
$currentUser = null;

if ($isLoggedIn && isset($pdo)) {
    $currentUser = appLoadCurrentUser($pdo);
}

$isBlocked = (int) ($currentUser['is_blocked'] ?? ($_SESSION['is_blocked'] ?? 0)) === 1;
$discountConfig = isset($pdo)
    ? appGetDiscountConfig($pdo)
    : ['enabled' => false, 'ten_percent' => 10, 'twenty_percent' => 20];
$nextBookingDiscount = null;

if ($isLoggedIn && isset($pdo)) {
    $countStatement = $pdo->query('SELECT COUNT(*) FROM bookings');
    $nextBookingNumber = ((int) ($countStatement ? $countStatement->fetchColumn() : 0)) + 1;
    $nextBookingDiscount = appCalculateBookingDiscount($nextBookingNumber, 1.0, $discountConfig);
}

$loginReturnTo = 'login.php?return_to=cart.php';
$bookingStatus = $_GET['booking'] ?? '';
