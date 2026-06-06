<?php
// Der Adminbereich verwaltet Buchungen, Nutzer und Rabatte.
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

// Schema nur dann initialisieren, wenn die Datenbankverbindung zur Verfuegung steht.
if (isset($pdo)) {
    appEnsureSchema($pdo);
}

// Ohne Datenbank macht der Adminbereich keinen Sinn.
if (!isset($pdo)) {
    die('Die Datenbankverbindung ist aktuell nicht verfügbar.');
}

// Nur Administratoren duerfen diese Seite aufrufen.
appRequireAdmin($pdo);

// Die Tabs gruppieren Buchungen nach Status, damit die Ansicht uebersichtlich bleibt.
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

// Unbekannte Tab-Werte fallen auf die Standardansicht zurueck.
$activeTab = $_GET['tab'] ?? 'new';
if (!array_key_exists($activeTab, $tabs)) {
    $activeTab = 'new';
}

// Aktuelle Rabatt-Einstellungen fuer den separaten Einstellungsbereich laden.
$discountConfig = appGetDiscountConfig($pdo);

// Flash-Meldungen werden nach einem Redirect genau einmal angezeigt.
$flashMessage = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

// Speichert eine Meldung in der Session, damit sie beim naechsten Seitenaufruf erscheint.
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

// Alle Aenderungen laufen ueber POST, damit Statusaenderungen und Sperren sauber verarbeitet werden.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $activeTab = $_POST['tab'] ?? $activeTab;
    if (!array_key_exists($activeTab, $tabs)) {
        $activeTab = 'new';
    }

    try {
        // Buchungen koennen Schritt fuer Schritt vom Neuzustand bis zum Abschluss weitergeschaltet werden.
        if ($action === 'advance_booking') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            $targetStatus = trim((string) ($_POST['target_status'] ?? ''));

            // Zuerst den aktuellen Status laden, damit nur gueltige Uebergaenge erlaubt sind.
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
                // Der erste Schritt setzt die Buchung auf "processing".
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
                // Der zweite Schritt schliesst die Buchung ab.
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
        // Buchungen koennen auch mit einer Begruendung abgelehnt werden.
        } elseif ($action === 'reject_booking') {
            $bookingId = (int) ($_POST['booking_id'] ?? 0);
            $reason = trim((string) ($_POST['rejection_reason'] ?? ''));

            if ($reason === '') {
                throw new RuntimeException('Bitte einen Ablehnungsgrund angeben.');
            }

            // Erneut den Datensatz laden, um den Status vor dem Ablehnen zu pruefen.
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

            // Ablehnung speichert Status, Grund und Bearbeitungszeitpunkt.
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
        // Normale Nutzer koennen gesperrt oder entsperrt werden.
        } elseif ($action === 'toggle_user_block') {
            $userId = (int) ($_POST['user_id'] ?? 0);
            // Vor dem Umschalten wird geprueft, ob der Nutzer existiert und kein Admin ist.
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
            // Der Sperrstatus wird einfach umgedreht.
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
        // Die Rabatteinstellungen werden zentral in app_settings gespeichert.
        } elseif ($action === 'update_discount_settings') {
            $enabled = isset($_POST['discount_enabled']) ? '1' : '0';
            $tenPercent = max(0, min(100, (float) ($_POST['discount_10_percent'] ?? 10)));
            $twentyPercent = max(0, min(100, (float) ($_POST['discount_20_percent'] ?? 20)));

            appSetSetting($pdo, 'discount_enabled', $enabled);
            appSetSetting($pdo, 'discount_10_percent', (string) $tenPercent);
            appSetSetting($pdo, 'discount_20_percent', (string) $twentyPercent);

            adminFlash('success', 'Die Rabatteinstellungen wurden gespeichert.');
        // Produkte werden direkt in der Datenbank verwaltet.
        } elseif ($action === 'create_product') {
            $name = trim((string) ($_POST['product_name'] ?? ''));
            $description = trim((string) ($_POST['product_description'] ?? ''));
            $price = (float) str_replace(',', '.', trim((string) ($_POST['product_price'] ?? '0')));
            $category = trim((string) ($_POST['product_category'] ?? ''));
            $subcategory = trim((string) ($_POST['product_subcategory'] ?? ''));
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

            $productCheck = $pdo->prepare(
                'SELECT id
                 FROM products
                 WHERE id = :product_id
                 LIMIT 1'
            );
            $productCheck->execute(['product_id' => $productId]);

            if (!$productCheck->fetch()) {
                throw new RuntimeException('Das Produkt wurde nicht gefunden.');
            }

            $deleteProduct = $pdo->prepare(
                'DELETE FROM products
                 WHERE id = :product_id'
            );
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

            $uploadDir = __DIR__ . '/images/products/uploads';
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
            throw new RuntimeException('Unbekannte Aktion.');
        }
    } catch (Throwable $throwable) {
        adminFlash('error', $throwable->getMessage());
    }

    header('Location: admin.php?tab=' . urlencode($activeTab));
    exit;
}

// Fuer die aktive Ansicht nur die relevanten Statuswerte laden.
$bookingStatuses = $tabs[$activeTab]['statuses'];
$placeholders = implode(',', array_fill(0, count($bookingStatuses), '?'));

// Buchungen werden mit dem Nutzer verknuepft, damit der Admin den Besitzer sieht.
$bookingQuery = $pdo->prepare(
    "SELECT b.id, b.user_id, b.subtotal_amount, b.total_amount, b.discount_percent, b.discount_amount, b.discount_label, b.status, b.rejection_reason, b.processed_at, b.cancelled_at, b.created_at, u.username
     FROM bookings b
     INNER JOIN users u ON u.id = b.user_id
     WHERE b.status IN ($placeholders)
     ORDER BY b.created_at DESC, b.id DESC"
);
$bookingQuery->execute($bookingStatuses);
$bookings = $bookingQuery->fetchAll() ?: [];

// Die Positionen jeder Buchung werden separat geladen und nach Buchung gruppiert.
$bookingItems = [];
if ($bookings !== []) {
    $bookingIds = array_map(static fn(array $booking): int => (int) $booking['id'], $bookings);
    $itemPlaceholders = implode(',', array_fill(0, count($bookingIds), '?'));

    $itemsQuery = $pdo->prepare(
        "SELECT booking_id, product_name, quantity
         FROM booking_items
         WHERE booking_id IN ($itemPlaceholders)
         ORDER BY id ASC"
    );
    $itemsQuery->execute($bookingIds);

    foreach ($itemsQuery->fetchAll() ?: [] as $item) {
        $bookingItems[(int) $item['booking_id']][] = $item;
    }
}

// Die vollstaendige Nutzerliste dient fuer Sperren und Entsperren.
$usersQuery = $pdo->query(
    'SELECT id, username, role, is_blocked
     FROM users
     ORDER BY role DESC, username ASC'
);
$users = $usersQuery ? ($usersQuery->fetchAll() ?: []) : [];

// Produkte und Bildbibliothek werden mit einer JOIN-Abfrage geladen.
$products = appFetchProducts($pdo);
$productImages = appFetchProductImages($pdo);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <script src="JavaScript/admin-products.js" defer></script>
    <title>Adminbereich</title>
</head>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <main class="admin-dashboard">
        <h1>Auftragsverwaltung</h1>
        <a href="logout.php" class="logout-button admin-logout-link">Abmelden</a>

        <?php if ($flashMessage): ?>
            <p class="form-message <?php echo htmlspecialchars($flashMessage['type'] === 'error' ? 'error-message' : 'success-message', ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <div class="admin-tabs">
            <?php foreach ($tabs as $tabKey => $tabConfig): ?>
                <a class="admin-tab <?php echo $tabKey === $activeTab ? 'is-active' : ''; ?>" href="admin.php?tab=<?php echo htmlspecialchars($tabKey, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($tabConfig['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="admin-panel">
            <h2><?php echo htmlspecialchars($tabs[$activeTab]['label'], ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if ($bookings === []): ?>
                <p class="account-bookings-empty">Für diese Ansicht liegen aktuell keine Aufträge vor.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Nutzer</th>
                        <th>Datum</th>
                        <th>Positionen</th>
                        <th>Brutto</th>
                        <th>Rabatt</th>
                        <th>Gesamt</th>
                        <th>Status</th>
                        <th>Hinweis</th>
                        <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                                $bookingId = (int) $booking['id'];
                                $items = $bookingItems[$bookingId] ?? [];
                                $itemLines = array_map(
                                    static fn(array $item): string => (int) $item['quantity'] . 'x ' . $item['product_name'],
                                    $items
                                );
                                $subtotal = (float) ($booking['subtotal_amount'] ?? $booking['total_amount']);
                                $discountAmount = (float) ($booking['discount_amount'] ?? 0);
                                $discountPercent = (float) ($booking['discount_percent'] ?? 0);
                                $discountLabel = (string) ($booking['discount_label'] ?? '');
                                $status = (string) ($booking['status'] ?? 'new');
                                $statusLabel = appBookingStatusLabel($status);
                                $statusClass = appBookingStatusClass($status);
                                $reason = (string) ($booking['rejection_reason'] ?? '');
                            ?>
                            <tr>
                                <td><?php echo $bookingId; ?></td>
                                <td><?php echo htmlspecialchars((string) $booking['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $booking['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($itemLines === [] ? '-' : implode(', ', $itemLines), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatMoney($subtotal), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatDiscountSummary($discountLabel !== '' ? $discountLabel : null, $discountPercent, $discountAmount), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(appFormatMoney((float) $booking['total_amount']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="admin-status <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars($reason !== '' ? $reason : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($status === 'new'): ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="advance_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="hidden" name="target_status" value="processing">
                                            <button type="submit" class="admin-action-button">In Bearbeitung</button>
                                        </form>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="reject_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="text" name="rejection_reason" placeholder="Ablehnungsgrund" required>
                                            <button type="submit" class="admin-action-button">Ablehnen</button>
                                        </form>
                                    <?php elseif ($status === 'processing'): ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="advance_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="hidden" name="target_status" value="completed">
                                            <button type="submit" class="admin-action-button">Abschließen</button>
                                        </form>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="reject_booking">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                            <input type="text" name="rejection_reason" placeholder="Ablehnungsgrund" required>
                                            <button type="submit" class="admin-action-button">Ablehnen</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <div class="admin-users-toolbar">
                <h2>Nutzerliste</h2>
            </div>

            <?php if ($users === []): ?>
                <p class="account-bookings-empty">Es sind noch keine Nutzer vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nr.</th>
                            <th>Nutzer</th>
                            <th>Rolle</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $blocked = (int) ($user['is_blocked'] ?? 0) === 1;
                                $isAdminAccount = ($user['role'] ?? 'user') === 'admin';
                            ?>
                            <tr>
                                <td><?php echo (int) $user['id']; ?></td>
                                <td><?php echo htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="admin-status <?php echo $blocked ? 'status-rejected' : 'status-completed'; ?>">
                                        <?php echo $blocked ? 'Gesperrt' : 'Aktiv'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isAdminAccount): ?>
                                        -
                                    <?php else: ?>
                                        <form action="admin.php" method="post" class="admin-inline-form">
                                            <input type="hidden" name="action" value="toggle_user_block">
                                            <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                            <button type="submit" class="admin-action-button">
                                                <?php echo $blocked ? 'Entsperren' : 'Sperren'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <div class="admin-users-toolbar">
                <h2>Produkte</h2>
            </div>

            <div class="admin-filter-bar">
                <label for="admin-product-filter">Produkte filtern</label>
                <input
                    type="search"
                    id="admin-product-filter"
                    class="admin-product-filter"
                    placeholder="Name, Kategorie oder Unterkategorie"
                    data-products-endpoint="api/products.php"
                >
                <span class="admin-filter-count" id="admin-product-filter-count"><?php echo count($products); ?> Produkte</span>
            </div>

            <?php if ($products === []): ?>
                <p class="account-bookings-empty">Es sind noch keine Produkte vorhanden.</p>
            <?php endif; ?>

            <table class="admin-table" id="admin-products-table">
                <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Name</th>
                        <th>Bild</th>
                        <th>Preis</th>
                        <th>Kategorie</th>
                        <th>Unterkategorie</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody id="admin-products-table-body">
                    <?php if ($products === []): ?>
                        <tr>
                            <td colspan="7" class="account-bookings-empty">Keine Produkte vorhanden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <?php $imagePath = (string) ($product['image_path'] ?? ''); ?>
                            <tr>
                                <td><?php echo (int) $product['id']; ?></td>
                                <td><?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?php if ($imagePath !== ''): ?>
                                        <img
                                            src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>"
                                            alt="<?php echo htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            class="product-thumb"
                                        >
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(appFormatMoney((float) $product['price']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $product['subcategory'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form action="admin.php" method="post" class="admin-inline-form">
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                        <button type="submit" class="admin-action-button" onclick="return confirm('Dieses Produkt wirklich löschen?');">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="admin-product-grid">
                <form action="admin.php" method="post" class="admin-form-grid admin-product-form">
                    <input type="hidden" name="action" value="create_product">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                    <label for="product_name">Produktname</label>
                    <input type="text" id="product_name" name="product_name" required>

                    <label for="product_price">Preis</label>
                    <input type="text" id="product_price" name="product_price" inputmode="decimal" placeholder="z. B. 19,90" required>

                    <label for="product_category">Kategorie</label>
                    <input type="text" id="product_category" name="product_category" required>

                    <label for="product_subcategory">Unterkategorie</label>
                    <input type="text" id="product_subcategory" name="product_subcategory" required>

                    <label class="full-width" for="product_description">Beschreibung</label>
                    <textarea id="product_description" name="product_description" rows="5" class="full-width" required></textarea>

                    <label class="full-width" for="image_id">Produktbild</label>
                    <select id="image_id" name="image_id" class="full-width">
                        <option value="">Ohne Bild</option>
                        <?php foreach ($productImages as $image): ?>
                            <option value="<?php echo (int) $image['id']; ?>">
                                <?php echo htmlspecialchars('#' . (int) $image['id'] . ' - ' . (string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="admin-form-actions full-width">
                        <button type="submit" class="admin-action-button">Produkt anlegen</button>
                    </div>
                </form>

                <!--Upload formular für die Bildbibliothek -->
                <form action="admin.php" method="post" enctype="multipart/form-data" class="admin-form-grid admin-product-form">
                    <input type="hidden" name="action" value="upload_product_image">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                    <label class="full-width" for="product_image">Neues Produktbild hochladen</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*" class="full-width" required>

                    <p class="account-bookings-empty full-width">
                        Erlaubt sind JPG, PNG, GIF und WEBP. Das Bild landet in der Bildbibliothek und kann danach für neue Produkte verwendet werden.
                    </p>

                    <div class="admin-form-actions full-width">
                        <button type="submit" class="admin-action-button">Bild hochladen</button>
                    </div>
                </form>
            </div>

            <h3 class="admin-subheading">Bildbibliothek</h3>
            <?php if ($productImages === []): ?>
                <p class="account-bookings-empty">Es wurden noch keine Produktbilder hochgeladen.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nr.</th>
                            <th>Vorschau</th>
                            <th>Datei</th>
                            <th>Hochgeladen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productImages as $image): ?>
                            <tr>
                                <td><?php echo (int) $image['id']; ?></td>
                                <td>
                                    <img
                                        src="<?php echo htmlspecialchars((string) $image['file_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="<?php echo htmlspecialchars((string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?>"
                                        class="product-thumb"
                                    >
                                </td>
                                <td><?php echo htmlspecialchars((string) ($image['original_name'] ?: $image['file_path']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $image['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="admin-panel">
            <h2>Rabatteinstellungen</h2>

            <form action="admin.php" method="post" class="admin-inline-form">
                <input type="hidden" name="action" value="update_discount_settings">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($activeTab, ENT_QUOTES, 'UTF-8'); ?>">

                <label>
                    <input type="checkbox" name="discount_enabled" <?php echo $discountConfig['enabled'] ? 'checked' : ''; ?>>
                    Rabatt aktivieren
                </label>

                <label for="discount_10_percent">10. Bestellung</label>
                <input type="text" inputmode="decimal" name="discount_10_percent" id="discount_10_percent" value="<?php echo htmlspecialchars((string) $discountConfig['ten_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <label for="discount_20_percent">20. Bestellung</label>
                <input type="text" inputmode="decimal" name="discount_20_percent" id="discount_20_percent" value="<?php echo htmlspecialchars((string) $discountConfig['twenty_percent'], ENT_QUOTES, 'UTF-8'); ?>">

                <button type="submit" class="admin-action-button">Speichern</button>
            </form>

            <p class="account-bookings-empty">
                Aktuell gilt: <?php echo $discountConfig['enabled'] ? 'aktiv' : 'deaktiviert'; ?>,
                10. Bestellung: <?php echo htmlspecialchars((string) $discountConfig['ten_percent'], ENT_QUOTES, 'UTF-8'); ?> %,
                20. Bestellung: <?php echo htmlspecialchars((string) $discountConfig['twenty_percent'], ENT_QUOTES, 'UTF-8'); ?> %.
            </p>
        </section>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
