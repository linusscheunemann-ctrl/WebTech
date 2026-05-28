<?php
session_start();
require_once __DIR__ . '/db.php';

$errorMessage = '';
$usernameValue = '';
$returnTo = $_GET['return_to'] ?? $_POST['return_to'] ?? 'user.php';

function resolveReturnTo(string $path): string
{
    $allowedTargets = ['cart.php', 'user.php'];

    return in_array($path, $allowedTargets, true) ? $path : 'user.php';
}

$returnTo = resolveReturnTo($returnTo);

if (isset($_SESSION['username']) && $_SESSION['username'] !== '') {
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
            'SELECT id, username, password_hash
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

            header('Location: ' . $returnTo);
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
