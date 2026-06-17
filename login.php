<?php require_once __DIR__ . '/controllers/login_controller.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/validate.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Login</title>
</head>
<body>
<noscript>
    <div class="js-warning">
        ⚠️ JavaScript ist deaktiviert. Bitte aktiviere JavaScript in deinem Browser, um dich anmelden zu können.
    </div>
    </noscript>

    <?php if ($errorMessage !== ''): ?>
        <p class="form-message error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <?php if (!$cookies_enabled): ?>
        <div class="cookie-warning">
            ⚠️ Cookies sind deaktiviert. Bitte aktiviere Cookies in deinem Browser, um dich anzumelden.
    </div>
    <?php endif;?>


    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <!--## Beginn Code von Linus -->
    <h1>Benutzeranmeldung</h1>

    <?php if ($errorMessage !== ''): ?>
        <p class="form-message error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($returnTo, ENT_QUOTES, 'UTF-8'); ?>">
        <label for="username">Benutzername:</label>
        <input
            type="text"
            name="username"
            id="username"
            autocomplete="username"
            required
            value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <label for="password">Passwort:</label>
        <input
            type="password"
            name="password"
            id="password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Anmelden</button>
        <button type="button" onclick="location.href='registration.php'">Registrieren</button>
    </form>
<!--## Schluss Code von Linus -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
