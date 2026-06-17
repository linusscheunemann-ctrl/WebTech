<?php

require_once __DIR__ . '/_bootstrap.php';

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

    if ($formAction === 'update_profile') {
        $usernameValue = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!isset($pdo)) {
            $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
        } elseif ($usernameValue === '' || $password === '' || $confirmPassword === '') {
            $errorMessage = 'Bitte alle Felder ausfüllen.';
        } elseif (!appIsValidUsername($usernameValue)) {
            $errorMessage = 'Der Benutzername muss mindestens 5 Zeichen lang sein und Groß- sowie Kleinbuchstaben enthalten.';
        } elseif (!appIsValidPassword($password)) {
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
