<?php require_once __DIR__ . '/controllers/registration_controller.php'; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/mystyle.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="google-fonts">
    <script src="JavaScript/toggle-theme.js" defer></script>
    <script src="JavaScript/validate.js" defer></script>
    <script src="JavaScript/cart.js" defer></script>
    <title>Registrierung</title>
</head>
<body>
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <h1>Benutzerregistrierung</h1>
<!--## Beginn KI generierter Code -->
    <?php if ($errorMessage !== ''): ?>
        <p class="form-message error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <p class="form-message success-message">
            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
            <a href="login.php">Zum Login</a>
        </p>
    <?php endif; ?>
<!--## Schluss KI generierter Code -->
<!--## Beginn Code von Linus -->
    <form action="registration.php" method="post" novalidate>
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

        <label for="password">Passwort:</label>
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

        <button type="submit" id="register-submit" disabled>Registrieren</button>
        <button type="button" onclick="location.href='login.php'">Anmelden</button>
    </form>
<!--## Schluss Code von Linus -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
