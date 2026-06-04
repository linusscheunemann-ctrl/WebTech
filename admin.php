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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
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
                <p class="account-bookings-empty">Sperren blockiert neue Buchungen, entsperren hebt die Einschränkung wieder auf.</p>
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
