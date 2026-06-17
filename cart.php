<?php require_once __DIR__ . '/controllers/cart_controller.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <script src="JavaScript/toggle-theme.js"></script>
    <script src="JavaScript/cart.js" defer></script>
    <script src="JavaScript/shop.js" defer></script>
    <title>Warenkorb</title>
</head>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>
<!--## Beginn Code von Linus -->
    <div class="cart-container">
        <h1>Warenkorb</h1>

        <?php if ($bookingStatus === 'success'): ?>
            <p class="form-message success-message">Die Buchung wurde erfolgreich gespeichert.</p>
        <?php elseif ($bookingStatus === 'empty'): ?>
            <p class="form-message error-message">Dein Warenkorb war leer, deshalb konnte keine Buchung gespeichert werden.</p>
        <?php elseif ($bookingStatus === 'error'): ?>
            <p class="form-message error-message">Die Buchung konnte nicht gespeichert werden. Bitte versuche es erneut.</p>
        <?php elseif ($bookingStatus === 'database_error'): ?>
            <p class="form-message error-message">Die Datenbank ist aktuell nicht verfügbar.</p>
        <?php elseif ($bookingStatus === 'blocked'): ?>
            <p class="form-message error-message">Ihr Konto ist vom Administrator gesperrt. Buchungen sind derzeit deaktiviert.</p>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <p class="form-message success-message">Eingeloggt als <?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>. Du kannst die Buchung jetzt abschließen.</p>
            <p class="form-message success-message">
                <?php if (($discountConfig['enabled'] ?? false) && ($nextBookingDiscount['amount'] ?? 0) > 0): ?>
                    Die nächste passende Bestellung erhält automatisch Rabatt. Aktuell wären es <?php echo htmlspecialchars((string) ($nextBookingDiscount['label'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> mit <?php echo htmlspecialchars(appFormatPercent((float) ($nextBookingDiscount['percent'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?> %.
                <?php else: ?>
                    Rabatte können im Adminbereich aktiviert und angepasst werden.
                <?php endif; ?>
            </p>
            <?php if ($isBlocked): ?>
                <p class="form-message error-message">Ihr Konto ist vom Administrator gesperrt. Die Kasse wurde deaktiviert.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="form-message error-message">Für die Buchung musst du eingeloggt sein. Bitte melde dich an oder registriere dich.</p>
        <?php endif; ?>

<!--## Schluss Code von Linus -->

<!--## Beginn Code von Nils -->
        <p id="empty-msg">Ihr Warenkorb ist leer. Fügen Sie Produkte hinzu, um fortzufahren.</p>
        <table id="cart-table">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Produkt</th>
                    <th>Einzelpreis</th>
                    <th>Anzahl</th>
                    <th>Gesamt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cart-body"></tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Nettobetrag</td>
                    <td id="netto-price" colspan="2"></td>
                </tr>
                <tr>
                    <td colspan="4">zzgl. MwSt. (19%)</td>
                    <td id="mwst-price" colspan="2"></td>
                </tr>
                <tr class="total-row">
                    <td colspan="4">Gesamt (inkl. MwSt.):</td>
                    <td id="total-price" colspan="2">0,00 €</td>
                </tr>
            </tfoot>
        </table>

        <?php if ($isLoggedIn): ?>
            <form id="checkout-form" action="checkout.php" method="post" class="cart-actions">
                <input type="hidden" name="cart_payload" id="cart-payload">
                <button type="button" class="btn-back" onclick="location.href='shop.php'">Weiter einkaufen</button>
                <button type="button" class="btn-clear" onclick="clearCart()">Warenkorb leeren</button>
                <button type="button" class="btn-checkout" onclick="checkout()" <?php echo $isBlocked ? 'disabled' : ''; ?>>Zur Kasse</button>
            </form>

            <form id="save-list-form" action="user.php" method="post" class="cart-actions cart-list-form">
                <input type="hidden" name="action" value="save_cart_list">
                <input type="hidden" name="cart_payload" id="list-cart-payload">
                <input type="text" id="list-name" name="list_name" placeholder="Name der Sammelliste" value="Meine Sammelliste">
                <button type="button" class="btn-back" onclick="saveCartAsList()">Als Sammelliste speichern</button>
            </form>
        <?php else: ?>
            <div class="cart-actions">
                <button type="button" class="btn-back" onclick="location.href='shop.php'">Weiter einkaufen</button>
                <button type="button" class="btn-clear" onclick="clearCart()">Warenkorb leeren</button>
                <button type="button" class="btn-checkout" onclick="location.href='<?php echo $loginReturnTo; ?>'">Anmelden zum Buchen</button>
            </div>
        <?php endif; ?>
    </div>
<!--## Schluss Code von Nils -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
