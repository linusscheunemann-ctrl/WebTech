<?php

function appBookingStatusLabel(string $status): string
{
    return match ($status) {
        'new' => 'Bestellt',
        'processing' => 'Versandt, aber nicht erhalten',
        'completed' => 'Fertig',
        'rejected', 'cancelled' => 'Storniert',
        default => ucfirst($status),
    };
}

function appBookingStatusClass(string $status): string
{
    return match ($status) {
        'new' => 'status-new',
        'processing' => 'status-processing',
        'completed' => 'status-completed',
        'rejected' => 'status-rejected',
        'cancelled' => 'status-cancelled',
        default => 'status-unknown',
    };
}

function appBookingIsCancelable(array $booking): bool
{
    return ($booking['status'] ?? '') === 'new';
}

function appFormatMoney(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

function appFormatPercent(float $percent): string
{
    return rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',');
}

function appCalculateBookingDiscount(int $bookingNumber, float $subtotalAmount, array $discountConfig): array
{
    $enabled = (bool) ($discountConfig['enabled'] ?? true);
    $tenPercent = max(0.0, (float) ($discountConfig['ten_percent'] ?? 10));
    $twentyPercent = max(0.0, (float) ($discountConfig['twenty_percent'] ?? 20));

    if (!$enabled || $subtotalAmount <= 0 || $bookingNumber <= 0) {
        return [
            'code' => '',
            'label' => null,
            'percent' => 0.0,
            'amount' => 0.0,
            'valid' => false,
        ];
    }

    if ($bookingNumber % 20 === 0 && $twentyPercent > 0) {
        $amount = round($subtotalAmount * ($twentyPercent / 100), 2);

        return [
            'code' => 'booking_20',
            'label' => '20. Bestellung',
            'percent' => $twentyPercent,
            'amount' => $amount,
            'valid' => true,
        ];
    }

    if ($bookingNumber % 10 === 0 && $tenPercent > 0) {
        $amount = round($subtotalAmount * ($tenPercent / 100), 2);

        return [
            'code' => 'booking_10',
            'label' => '10. Bestellung',
            'percent' => $tenPercent,
            'amount' => $amount,
            'valid' => true,
        ];
    }

    return [
        'code' => '',
        'label' => null,
        'percent' => 0.0,
        'amount' => 0.0,
        'valid' => false,
    ];
}

function appFormatDiscountSummary(?string $label, float $percent, float $amount): string
{
    if ($amount <= 0 || $percent <= 0) {
        return '-';
    }

    $prefix = $label ? $label . ': ' : '';
    return $prefix . '-' . appFormatMoney($amount) . ' (' . appFormatPercent($percent) . '%)';
}

function appGetDiscountConfig(PDO $pdo): array
{
    return [
        'enabled' => appGetSetting($pdo, 'discount_enabled', '1') === '1',
        'ten_percent' => (float) appGetSetting($pdo, 'discount_10_percent', '10'),
        'twenty_percent' => (float) appGetSetting($pdo, 'discount_20_percent', '20'),
    ];
}

function appFetchBookingCartItems(PDO $pdo, int $bookingId, int $userId): array
{
    $statement = $pdo->prepare(
        'SELECT
            bi.product_id,
            bi.product_name,
            bi.unit_price,
            bi.quantity,
            bi.image
         FROM booking_items bi
         INNER JOIN bookings b ON b.id = bi.booking_id
         WHERE bi.booking_id = :booking_id
           AND b.user_id = :user_id
         ORDER BY bi.id ASC'
    );

    $statement->execute([
        'booking_id' => $bookingId,
        'user_id' => $userId,
    ]);

    $items = $statement->fetchAll() ?: [];
    $cart = [];

    foreach ($items as $item) {
        $productName = trim((string) ($item['product_name'] ?? ''));
        $unitPrice = max(0.0, (float) ($item['unit_price'] ?? 0));
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $image = trim((string) ($item['image'] ?? ''));
        $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;

        if ($productName === '' || $unitPrice <= 0) {
            continue;
        }

        $cart[] = [
            'id' => $productId > 0 ? $productId : count($cart) + 1,
            'name' => $productName,
            'price' => $unitPrice,
            'image' => $image,
            'menge' => $quantity,
        ];
    }

    return $cart;
}
