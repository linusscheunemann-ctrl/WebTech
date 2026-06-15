<?php
session_start();

// Cookie-Test über die Session
$cookies_enabled = isset($_COOKIE[session_name()]);

if (!$cookies_enabled) {
 $errorMessage = 'Cookies sind deaktiviert. Bitte aktiviere Cookies.';
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}

$errorMessage = '';
$usernameValue = '';
$returnTo = $_GET['return_to'] ?? $_POST['return_to'] ?? 'user.php';

function resolveReturnTo(string $path): string
{
    $allowedTargets = ['cart.php', 'user.php', 'admin.php'];

    return in_array($path, $allowedTargets, true) ? $path : 'user.php';
}

$returnTo = resolveReturnTo($returnTo);

if (isset($_SESSION['username']) && $_SESSION['username'] !== '') {
    if (($_SESSION['role'] ?? 'user') === 'admin' && $returnTo === 'user.php') {
        $returnTo = 'admin.php';
    }

    header('Location: ' . $returnTo);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } elseif ($usernameValue === '' || $password === '') {
        $errorMessage = 'Bitte Benutzername und Passwort eingeben.';
    } else {
        $statement = $pdo->prepare(
            'SELECT id, username, password_hash, role, is_blocked
             FROM users
             WHERE username = :username
             LIMIT 1'
        );
        $statement->execute(['username' => $usernameValue]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errorMessage = 'Benutzername oder Passwort ist falsch.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['is_blocked'] = (int) ($user['is_blocked'] ?? 0);

            $target = $returnTo;

            if (($user['role'] ?? 'user') === 'admin' && $returnTo === 'user.php') {
                $target = 'admin.php';
            }

            header('Location: ' . $target);
            exit;
        }
    }
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
    <title>Login</title>
</head>
<body>
    
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>

    <h1>Benutzeranmeldung</h1>

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

    <?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
