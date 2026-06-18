<?php
// ## Beginn generierter Code von Codex
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
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

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

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php
// ## Schluss generierter Code von Codex
