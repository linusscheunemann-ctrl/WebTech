<?php
session_start();
require_once __DIR__ . '/db.php';

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

if (empty($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$errorMessage = '';
$successMessage = '';
$usernameValue = $_SESSION['username'];
$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    <?php if ($errorMessage !== ''): ?>
        <p class="form-message error-message"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <p class="form-message success-message"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form action="user.php" method="post" novalidate>
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
