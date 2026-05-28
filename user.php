<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}

function isValidUsername(string $username): bool
{
    return strlen($username) >= 5
        && preg_match('/[A-Z]/', $username)
        && preg_match('/[a-z]/', $username);
}

function isValidPassword(string $password): bool
{
    return strlen($password) >= 10;
}

function renderCartRedirectPage(array $cart, string $message = 'Der Warenkorb wurde aktualisiert.', string $targetUrl = 'cart.php'): void
{
    $jsonCart = json_encode(array_values($cart), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonCart === false) {
        $jsonCart = '[]';
    }

    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Weiterleitung</title>
    </head>
    <body>
        <script>
            localStorage.setItem('cart', <?php echo $jsonCart; ?>);
            alert(<?php echo json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
            window.location.href = <?php echo json_encode($targetUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        </script>
    </body>
    </html>
    <?php
    exit;
}

if (empty($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$currentUser = isset($pdo) ? appLoadCurrentUser($pdo) : null;
$userId = (int) ($_SESSION['user_id'] ?? 0);
$isBlocked = (int) ($currentUser['is_blocked'] ?? ($_SESSION['is_blocked'] ?? 0)) === 1;

$errorMessage = '';
$successMessage = '';
$usernameValue = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_booking') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);

    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } elseif ($bookingId <= 0) {
        $errorMessage = 'Die Buchung konnte nicht gefunden werden.';
    } else {
        $statement = $pdo->prepare(
            'SELECT id, status
             FROM bookings
             WHERE id = :booking_id
               AND user_id = :user_id
             LIMIT 1'
        );
        $statement->execute([
            'booking_id' => $bookingId,
            'user_id' => $userId,
        ]);
        $booking = $statement->fetch();

        if (!$booking) {
            $errorMessage = 'Die Buchung konnte nicht gefunden werden.';
        } elseif (!appBookingIsCancelable($booking)) {
            $errorMessage = 'Diese Buchung kann nicht mehr storniert werden.';
        } else {
            $update = $pdo->prepare(
                'UPDATE bookings
                 SET status = :status,
                     rejection_reason = :reason,
                     cancelled_at = NOW()
                 WHERE id = :booking_id
                   AND user_id = :user_id'
            );
            $update->execute([
                'status' => 'cancelled',
                'reason' => 'Vom Nutzer storniert',
                'booking_id' => $bookingId,
                'user_id' => $userId,
            ]);

            header('Location: user.php?booking=cancelled');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_notifications_read') {
    if (isset($pdo)) {
        appMarkNotificationsRead($pdo, $userId);
    }

    header('Location: user.php#notifications');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_cart_list') {
    $listName = trim((string) ($_POST['list_name'] ?? ''));
    $cartPayload = (string) ($_POST['cart_payload'] ?? '');
    $cartItems = json_decode($cartPayload, true);

    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } elseif ($listName === '') {
        $errorMessage = 'Bitte einen Namen für die Sammelliste angeben.';
    } elseif (!is_array($cartItems) || $cartItems === []) {
        $errorMessage = 'Der Warenkorb ist leer, daher kann keine Sammelliste gespeichert werden.';
    } else {
        appCreateShoppingList($pdo, $userId, $listName, $cartItems);
        $successMessage = 'Die Sammelliste "' . $listName . '" wurde gespeichert.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'merge_lists') {
    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } else {
        $selectedLists = $_POST['list_ids'] ?? [];
        $lists = appFetchShoppingListsByIds($pdo, $userId, is_array($selectedLists) ? $selectedLists : []);

        if ($lists === []) {
            $errorMessage = 'Bitte mindestens eine Sammelliste auswählen.';
        } else {
            $cart = appFlattenShoppingLists($lists);
            renderCartRedirectPage(
                $cart,
                'Die ausgewählten Sammellisten wurden in den Warenkorb übernommen.',
                'cart.php'
            );
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reorder_booking') {
    $bookingId = (int) ($_POST['booking_id'] ?? 0);

    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } elseif ($bookingId <= 0) {
        $errorMessage = 'Die Buchung konnte nicht gefunden werden.';
    } else {
        $cart = appFetchBookingCartItems($pdo, $bookingId, $userId);

        if ($cart === []) {
            $errorMessage = 'Die Buchung konnte nicht gefunden werden oder enthält keine Positionen.';
        } else {
            renderCartRedirectPage(
                $cart,
                'Die Buchung wurde erneut in den Warenkorb gelegt.',
                'cart.php'
            );
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['action'] ?? 'update_profile';

    if ($formAction !== 'update_profile') {
        // Andere POST-Aktionen werden oben bereits behandelt.
    } else {
        $usernameValue = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!isset($pdo)) {
            $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
        } elseif ($usernameValue === '' || $password === '' || $confirmPassword === '') {
            $errorMessage = 'Bitte alle Felder ausfüllen.';
        } elseif (!isValidUsername($usernameValue)) {
            $errorMessage = 'Der Benutzername muss mindestens 5 Zeichen lang sein und Groß- sowie Kleinbuchstaben enthalten.';
        } elseif (!isValidPassword($password)) {
            $errorMessage = 'Das Passwort muss mindestens 10 Zeichen lang sein.';
        } elseif ($password !== $confirmPassword) {
            $errorMessage = 'Die Passwörter stimmen nicht überein.';
        } else {
            $statement = $pdo->prepare(
                'SELECT id
                 FROM users
                 WHERE username = :username
                   AND id <> :user_id
                 LIMIT 1'
            );
            $statement->execute([
                'username' => $usernameValue,
                'user_id' => $userId,
            ]);

            if ($statement->fetch()) {
                $errorMessage = 'Dieser Benutzername ist bereits vergeben.';
            } else {
                $update = $pdo->prepare(
                    'UPDATE users
                     SET username = :username,
                         password_hash = :password_hash
                     WHERE id = :user_id'
                );

                $update->execute([
                    'username' => $usernameValue,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'user_id' => $userId,
                ]);

                $_SESSION['username'] = $usernameValue;
                $successMessage = 'Dein Profil wurde erfolgreich aktualisiert.';
            }
        }
    }
}

$bookings = [];
$bookingItems = [];
$shoppingLists = [];
$notifications = [];
$unreadNotificationCount = 0;

if ($userId > 0 && isset($pdo)) {
    $bookingStatement = $pdo->prepare(
        'SELECT id, user_id, subtotal_amount, total_amount, discount_percent, discount_amount, discount_label, status, rejection_reason, processed_at, cancelled_at, created_at
         FROM bookings
         WHERE user_id = :user_id
         ORDER BY created_at DESC, id DESC'
    );
    $bookingStatement->execute(['user_id' => $userId]);
    $bookings = $bookingStatement->fetchAll() ?: [];

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

    $shoppingLists = appFetchShoppingLists($pdo, $userId);
    $notifications = appFetchUserNotifications($pdo, $userId, 8);
    $unreadNotificationCount = appGetUnreadNotificationCount($pdo, $userId);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/validate.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Benutzerbereich</title>
</head>
<body>
    <a href="index.php">
        <img src="images/logo.png" alt="Autohaus" class="logo">
    </a>

    <nav>
        <div class="nav-center">
            <div class="dropdown">
                <a href="bestand.php" class="nav-button" id="dropbtn">CARS & BIKES</a>
                <div class="dropdown-content">
                    <a href="autos.php">Autos</a>
                    <a href="motorraeder.php">Motorräder</a>
                </div>
            </div>
            <a href="shop.php" class="nav-button">SHOP</a>
            <a href="about.php" class="nav-button">ABOUT</a>
        </div>
        <div class="nav-right">
            <a href="cart.php" class="cart-icon">
                <img src="images/cart.webp" class="cart-img">
            </a>
            <button onclick="myFunction()" id="theme-toggle" class="theme-toggle">🌕</button>
            <a href="login.php">
                <img src="images/login.png" alt="Login" class="login-icon">
            </a>
        </div>
    </nav>

    <h1>Mein Profil</h1>

    <?php if (($currentUser['role'] ?? 'user') === 'admin'): ?>
        <button type="button" onclick="location.href='admin.php'">Zur Auftragsverwaltung</button>
    <?php endif; ?>

    <?php if ($isBlocked): ?>
        <p class="form-message error-message">Ihr Konto ist vom Administrator gesperrt. Neue Buchungen sind derzeit deaktiviert.</p>
    <?php endif; ?>

    <?php if (($_GET['booking'] ?? '') === 'cancelled'): ?>
        <p class="form-message success-message">Die Buchung wurde storniert.</p>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <p class="form-message error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <p class="form-message success-message"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="user.php" method="post" novalidate>
        <input type="hidden" name="action" value="update_profile">
        <label for="username">Benutzername:</label>
        <input
            type="text"
            name="username"
            id="username"
            required
            autocomplete="username"
            value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES, 'UTF-8'); ?>"
        >
        <p id="msg-user"></p>

        <label for="password">Neues Passwort:</label>
        <input
            type="password"
            name="password"
            id="password"
            required
            autocomplete="new-password"
        >

        <label for="confirm_password">Passwort bestätigen:</label>
        <input
            type="password"
            name="confirm_password"
            id="confirm_password"
            required
            autocomplete="new-password"
        >
        <p id="msg-pw"></p>

        <button type="submit" id="profile-submit" disabled>Aktualisieren</button>
        <button type="button" onclick="window.location.href='logout.php'">Abmelden</button>
    </form>

    <section class="account-notifications" id="notifications">
        <div class="section-head">
            <h2>Benachrichtigungen</h2>
            <span class="notification-count"><?php echo (int) $unreadNotificationCount; ?> ungelesen</span>
        </div>

        <?php if ($notifications === []): ?>
            <p class="account-bookings-empty">Noch keine Benachrichtigungen vorhanden.</p>
        <?php else: ?>
            <ul class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <li class="notification-item <?php echo (int) $notification['is_read'] === 1 ? 'is-read' : 'is-unread'; ?>">
                        <span class="notification-type"><?php echo htmlspecialchars((string) $notification['notification_type'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <div class="notification-body">
                            <strong><?php echo htmlspecialchars((string) $notification['message'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $notification['created_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <form action="user.php" method="post" class="notification-actions">
                <input type="hidden" name="action" value="mark_notifications_read">
                <button type="submit">Alle als gelesen markieren</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="account-lists">
        <div class="section-head">
            <h2>Sammellisten</h2>
            <p>Lege im Warenkorb eine Sammelliste an und füge hier mehrere Listen gemeinsam wieder zum Warenkorb hinzu.</p>
        </div>

        <?php if ($shoppingLists === []): ?>
            <p class="account-bookings-empty">Du hast noch keine Sammellisten gespeichert.</p>
        <?php else: ?>
            <form action="user.php" method="post" class="shopping-lists-form">
                <input type="hidden" name="action" value="merge_lists">

                <table class="booking-table shopping-list-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Positionen</th>
                            <th>Gesamtartikel</th>
                            <th>Zuletzt geändert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shoppingLists as $list): ?>
                            <?php
                                $listItems = $list['items'] ?? [];
                                $totalQuantity = array_reduce($listItems, static function (int $carry, array $item): int {
                                    return $carry + (int) $item['quantity'];
                                }, 0);
                                $itemNames = array_map(static fn(array $item): string => (string) $item['product_name'], $listItems);
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="list_ids[]" value="<?php echo (int) $list['id']; ?>">
                                </td>
                                <td><?php echo htmlspecialchars((string) $list['list_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($itemNames === [] ? '-' : implode(', ', $itemNames), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $totalQuantity; ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $list['updated_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="submit">Ausgewählte Listen zum Warenkorb hinzufügen</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="account-bookings">
        <h2>Meine Buchungen</h2>

        <?php if ($bookings === []): ?>
            <p class="account-bookings-empty">Du hast noch keine Buchungen gespeichert.</p>
        <?php else: ?>
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Nr.</th>
                        <th>Datum</th>
                        <th>Positionen</th>
                        <th>Brutto</th>
                        <th>Rabatt</th>
                        <th>Gesamt</th>
                        <th>Status</th>
                        <th>Hinweis</th>
                        <th></th>
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
                            $note = (string) ($booking['rejection_reason'] ?? '');
                        ?>
                        <tr>
                            <td><?php echo $bookingId; ?></td>
                            <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $booking['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($itemLines === [] ? '-' : implode(', ', $itemLines), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(appFormatMoney($subtotal), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(appFormatDiscountSummary($discountLabel !== '' ? $discountLabel : null, $discountPercent, $discountAmount), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(appFormatMoney((float) $booking['total_amount']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="booking-status <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars($note !== '' ? $note : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if (appBookingIsCancelable($booking)): ?>
                                    <form action="user.php" method="post" class="booking-action-form">
                                        <input type="hidden" name="action" value="cancel_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                        <button type="submit" class="booking-cancel-button">Stornieren</button>
                                    </form>
                                <?php else: ?>
                                    <form action="user.php" method="post" class="booking-action-form">
                                        <input type="hidden" name="action" value="reorder_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $bookingId; ?>">
                                        <button type="submit" class="booking-cancel-button">Erneut bestellen</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <footer id="footer-wrapper">
        <div id="footersocial">
            <ul>
                <li><a href="#"><img src="images/footer-facebook.png" alt="Facebook"></a></li>
                <li><a href="#"><img src="images/footer-email.png" alt="Instagram"></a></li>
            </ul>
        </div>

        <div id="claim-footer">
            <span id="head-footer">Kontakt</span>
            <p>Auto Union Straße 1</p>
            <p>85053 Ingolstadt</p>
            <p>Email: info@deinautohaus.de</p>
            <p>Telefon: 01234-567890</p>
            <p>Öffnungszeiten: Mo-Fr 9-18 Uhr, Sa 10-14 Uhr</p>
        </div>

        <div id="copyright">
            <p>© 2026 Dein Autohaus. Alle Rechte vorbehalten.</p>
        </div>
    </footer>
</body>
</html>
