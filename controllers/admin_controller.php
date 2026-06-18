<?php

// ## Beginn generierter Code von Codex

require_once __DIR__ . '/_bootstrap.php';

if (!isset($pdo)) {
    die('Die Datenbankverbindung ist aktuell nicht verfügbar.');
}

appRequireAdmin($pdo);

$tabs = [
    'new' => [
        'label' => 'Neue Aufträge',
        'statuses' => ['new'],
    ],
    'processing' => [
        'label' => 'In Bearbeitung',
        'statuses' => ['processing'],
    ],
    'rejected' => [
        'label' => 'Abgelehnt',
        'statuses' => ['rejected', 'cancelled'],
    ],
    'completed' => [
        'label' => 'Abgeschlossen',
        'statuses' => ['completed'],
    ],
];

$activeTab = $_GET['tab'] ?? 'new';
if (!array_key_exists($activeTab, $tabs)) {
    $activeTab = 'new';
}

$discountConfig = appGetDiscountConfig($pdo);
$flashMessage = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

function adminFlash(string $type, string $message): void
{
    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function adminImageExtensionFromMime(string $mimeType): ?string
{
    return match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => null,
    };
}

function adminEnsureWritableDirectory(string $directory): void
{
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException('Der Upload-Ordner konnte nicht angelegt werden.');
    }

    if (is_writable($directory)) {
        return;
    }

    @chmod($directory, 0777);

    if (!is_writable($directory)) {
        throw new RuntimeException('Der Upload-Ordner ist nicht beschreibbar.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $activeTab = $_POST['tab'] ?? $activeTab;
    if (!array_key_exists($activeTab, $tabs)) {
        $activeTab = 'new';
    }

    try {
        if ($action === 'advance_booking') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            $targetStatus = trim((string) ($_POST['target_status'] ?? ''));

            $statement = $pdo->prepare(
                'SELECT id, user_id, status
                 FROM bookings
                 WHERE id = :booking_id
                 LIMIT 1'
            );
            $statement->execute(['booking_id' => $bookingId]);
            $booking = $statement->fetch();

            if (!$booking) {
                throw new RuntimeException('Die Buchung wurde nicht gefunden.');
            }

            if ($targetStatus === 'processing' && ($booking['status'] ?? '') === 'new') {
                $update = $pdo->prepare(
                    'UPDATE bookings
                     SET status = "processing",
                         processed_at = COALESCE(processed_at, NOW()),
                         rejection_reason = NULL
                     WHERE id = :booking_id'
                );
                $update->execute(['booking_id' => $bookingId]);
                appCreateNotification(
                    $pdo,
                    (int) $booking['user_id'],
                    'Deine Buchung #' . $bookingId . ' wurde vom Administrator in Bearbeitung gesetzt.',
                    'info',
                    $bookingId
                );
                adminFlash('success', 'Die Buchung wurde in Bearbeitung verschoben.');
            } elseif ($targetStatus === 'completed' && ($booking['status'] ?? '') === 'processing') {
                $update = $pdo->prepare(
                    'UPDATE bookings
                     SET status = "completed",
                         processed_at = NOW(),
                         rejection_reason = NULL
                     WHERE id = :booking_id'
                );
                $update->execute(['booking_id' => $bookingId]);
                appCreateNotification(
                    $pdo,
                    (int) $booking['user_id'],
                    'Deine Buchung #' . $bookingId . ' wurde abgeschlossen.',
                    'success',
                    $bookingId
                );
                adminFlash('success', 'Die Buchung wurde abgeschlossen.');
            } else {
                throw new RuntimeException('Der Status konnte nicht geändert werden.');
            }
        } elseif ($action === 'reject_booking') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));

            if ($reason === '') {
                throw new RuntimeException('Bitte einen Ablehnungsgrund angeben.');
            }

            $statement = $pdo->prepare(
                'SELECT id, user_id, status
                 FROM bookings
                 WHERE id = :booking_id
                 LIMIT 1'
            );
            $statement->execute(['booking_id' => $bookingId]);
            $booking = $statement->fetch();

            if (!$booking) {
                throw new RuntimeException('Die Buchung wurde nicht gefunden.');
            }

            if (!in_array($booking['status'] ?? '', ['new', 'processing'], true)) {
                throw new RuntimeException('Diese Buchung kann nicht abgelehnt werden.');
            }

            $update = $pdo->prepare(
                'UPDATE bookings
                 SET status = "rejected",
                     rejection_reason = :reason,
                     processed_at = NOW()
                 WHERE id = :booking_id'
            );
            $update->execute([
                'reason' => $reason,
                'booking_id' => $bookingId,
            ]);

            appCreateNotification(
                $pdo,
                (int) $booking['user_id'],
                'Deine Buchung #' . $bookingId . ' wurde abgelehnt: ' . $reason,
                'error',
                $bookingId
            );

            adminFlash('success', 'Die Buchung wurde abgelehnt.');
        } elseif ($action === 'toggle_user_block') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $statement = $pdo->prepare(
                'SELECT id, username, role, is_blocked
                 FROM users
                 WHERE id = :user_id
                 LIMIT 1'
            );
            $statement->execute(['user_id' => $userId]);
            $user = $statement->fetch();

            if (!$user) {
                throw new RuntimeException('Der Nutzer wurde nicht gefunden.');
            }

            if (($user['role'] ?? 'user') === 'admin') {
                throw new RuntimeException('Admin-Konten werden nicht gesperrt.');
            }

            $newState = ((int) ($user['is_blocked'] ?? 0)) === 1 ? 0 : 1;
            $update = $pdo->prepare(
                'UPDATE users
                 SET is_blocked = :is_blocked
                 WHERE id = :user_id'
            );
            $update->execute([
                'is_blocked' => $newState,
                'user_id' => $userId,
            ]);

            appCreateNotification(
                $pdo,
                $userId,
                $newState === 1
                    ? 'Dein Konto wurde vom Administrator gesperrt.'
                    : 'Dein Konto wurde vom Administrator entsperrt.',
                $newState === 1 ? 'error' : 'success',
                null
            );

            adminFlash(
                'success',
                $newState === 1 ? 'Der Nutzer wurde gesperrt.' : 'Der Nutzer wurde entsperrt.'
            );
        } elseif ($action === 'update_discount_settings') {
            $enabled = isset($_POST['discount_enabled']) ? '1' : '0';
            $tenPercent = max(0, min(100, (float) ($_POST['discount_10_percent'] ?? 10)));
            $twentyPercent = max(0, min(100, (float) ($_POST['discount_20_percent'] ?? 20)));

            appSetSetting($pdo, 'discount_enabled', $enabled);
            appSetSetting($pdo, 'discount_10_percent', (string) $tenPercent);
            appSetSetting($pdo, 'discount_20_percent', (string) $twentyPercent);

            $discountConfig = appGetDiscountConfig($pdo);
            adminFlash('success', 'Die Rabatteinstellungen wurden gespeichert.');
        } elseif ($action === 'create_product') {
            $name = trim((string) ($_POST['product_name'] ?? $_POST['name'] ?? ''));
            $description = trim((string) ($_POST['product_description'] ?? $_POST['description'] ?? ''));
            $price = (float) str_replace(',', '.', trim((string) ($_POST['product_price'] ?? $_POST['price'] ?? '0')));
            $category = trim((string) ($_POST['product_category'] ?? $_POST['category'] ?? ''));
            $subcategory = trim((string) ($_POST['product_subcategory'] ?? $_POST['subcategory'] ?? ''));
            $imageId = (int) ($_POST['image_id'] ?? 0);

            if ($name === '') {
                throw new RuntimeException('Bitte einen Produktnamen eingeben.');
            }

            if ($description === '') {
                throw new RuntimeException('Bitte eine Produktbeschreibung eingeben.');
            }

            if ($price <= 0) {
                throw new RuntimeException('Bitte einen gültigen Preis eingeben.');
            }

            if ($category === '') {
                throw new RuntimeException('Bitte eine Kategorie angeben.');
            }

            if ($subcategory === '') {
                throw new RuntimeException('Bitte eine Unterkategorie angeben.');
            }

            if ($imageId > 0) {
                $imageCheck = $pdo->prepare(
                    'SELECT id
                     FROM product_images
                     WHERE id = :image_id
                     LIMIT 1'
                );
                $imageCheck->execute(['image_id' => $imageId]);

                if (!$imageCheck->fetch()) {
                    throw new RuntimeException('Das ausgewählte Bild wurde nicht gefunden.');
                }
            } else {
                $imageId = null;
            }

            $insertProduct = $pdo->prepare(
                'INSERT INTO products (
                    name,
                    description,
                    price,
                    category,
                    subcategory,
                    image_id
                ) VALUES (
                    :name,
                    :description,
                    :price,
                    :category,
                    :subcategory,
                    :image_id
                )'
            );
            $insertProduct->execute([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'category' => $category,
                'subcategory' => $subcategory,
                'image_id' => $imageId,
            ]);

            adminFlash('success', 'Das Produkt wurde angelegt.');
        } elseif ($action === 'delete_product') {
            $productId = (int) ($_POST['product_id'] ?? 0);
            if ($productId <= 0) {
                throw new RuntimeException('Das Produkt konnte nicht gefunden werden.');
            }

            $statement = $pdo->prepare('SELECT id, image_id FROM products WHERE id = :product_id LIMIT 1');
            $statement->execute(['product_id' => $productId]);
            $product = $statement->fetch();

            if (!$product) {
                throw new RuntimeException('Das Produkt konnte nicht gefunden werden.');
            }

            $deleteProduct = $pdo->prepare('DELETE FROM products WHERE id = :product_id');
            $deleteProduct->execute(['product_id' => $productId]);

            adminFlash('success', 'Das Produkt wurde gelöscht.');
        } elseif ($action === 'upload_product_image') {
            if (!isset($_FILES['product_image']) || !is_array($_FILES['product_image'])) {
                throw new RuntimeException('Bitte ein Bild auswählen.');
            }

            $uploadedFile = $_FILES['product_image'];
            $uploadError = (int) ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE);

            if ($uploadError !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Das Bild konnte nicht hochgeladen werden.');
            }

            $tmpName = (string) ($uploadedFile['tmp_name'] ?? '');
            if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                throw new RuntimeException('Der Upload ist ungültig.');
            }

            $imageInfo = @getimagesize($tmpName);
            if ($imageInfo === false || !isset($imageInfo['mime'])) {
                throw new RuntimeException('Bitte nur gültige Bilddateien hochladen.');
            }

            $extension = adminImageExtensionFromMime((string) $imageInfo['mime']);
            if ($extension === null) {
                throw new RuntimeException('Nur JPG, PNG, GIF oder WEBP sind erlaubt.');
            }

            $uploadDir = __DIR__ . '/../images/products/uploads';
            adminEnsureWritableDirectory($uploadDir);

            $safeName = 'product-' . bin2hex(random_bytes(8)) . '.' . $extension;
            $relativePath = 'images/products/uploads/' . $safeName;
            $absolutePath = $uploadDir . '/' . $safeName;

            if (!move_uploaded_file($tmpName, $absolutePath)) {
                throw new RuntimeException('Das Bild konnte nicht gespeichert werden. Bitte die Schreibrechte im Upload-Ordner prüfen.');
            }

            $insertImage = $pdo->prepare(
                'INSERT INTO product_images (file_path, original_name)
                 VALUES (:file_path, :original_name)'
            );
            $insertImage->execute([
                'file_path' => $relativePath,
                'original_name' => (string) ($uploadedFile['name'] ?? $safeName),
            ]);

            adminFlash('success', 'Das Produktbild wurde hochgeladen.');
        } else {
            throw new RuntimeException('Die Aktion konnte nicht ausgeführt werden.');
        }
    } catch (Throwable $throwable) {
        adminFlash('error', $throwable->getMessage());
    }

    header('Location: admin.php?tab=' . urlencode($activeTab));
    exit;
}

$bookingStatuses = $tabs[$activeTab]['statuses'];
$placeholders = implode(',', array_fill(0, count($bookingStatuses), '?'));

$bookingQuery = $pdo->prepare(
    "SELECT b.id, b.user_id, b.subtotal_amount, b.total_amount, b.discount_percent, b.discount_amount, b.discount_label, b.status, b.rejection_reason, b.processed_at, b.cancelled_at, b.created_at, u.username
     FROM bookings b
     INNER JOIN users u ON u.id = b.user_id
     WHERE b.status IN ($placeholders)
     ORDER BY b.created_at DESC, b.id DESC"
);
$bookingQuery->execute($bookingStatuses);
$bookings = $bookingQuery->fetchAll() ?: [];

$bookingItems = [];
if ($bookings !== []) {
    $bookingIds = array_map(static fn(array $booking): int => (int) $booking['id'], $bookings);
    $placeholders = implode(',', array_fill(0, count($bookingIds), '?'));

    $itemsStatement = $pdo->prepare(
        "SELECT booking_id, product_name, quantity
         FROM booking_items
         WHERE booking_id IN ($placeholders)
         ORDER BY id ASC"
    );
    $itemsStatement->execute($bookingIds);

    foreach ($itemsStatement->fetchAll() ?: [] as $item) {
        $bookingId = (int) $item['booking_id'];
        $bookingItems[$bookingId][] = $item;
    }
}

$users = $pdo->query(
    'SELECT id, username, role, is_blocked
     FROM users
     ORDER BY role DESC, username ASC'
)->fetchAll() ?: [];

$products = appFetchProducts($pdo);
$productImages = appFetchProductImages($pdo);

require __DIR__ . '/../views/admin_view.php';

// ## Schluss generierter Code von Codex
