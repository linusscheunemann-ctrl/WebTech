<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}

// ## Beginn KI generierter Code
// Prüft, ob der Benutzername gültig ist
// Bedingungen:
// - mindestens 5 Zeichen
// - mindestens ein Großbuchstabe
// - mindestens ein Kleinbuchstabe
function isValidUsername(string $username): bool
{
    return strlen($username) >= 5
        && preg_match('/[A-Z]/', $username)
        && preg_match('/[a-z]/', $username);
}

// Prüft, ob das Passwort gültig ist
// Bedingung:
// - mindestens 10 Zeichen lang
function isValidPassword(string $password): bool
{
    return strlen($password) >= 10;
}

// Variablen für Fehlermeldungen, Erfolgsmeldungen
// und den eingegebenen Benutzernamen initialisieren
$errorMessage = '';
$successMessage = '';
$usernameValue = '';

// Verarbeitung nur durchführen, wenn das Formular per POST abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Benutzereingaben aus dem Formular auslesen
    $usernameValue = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Prüfen, ob die Datenbankverbindung vorhanden ist
    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';

    // Benutzername validieren
    } elseif (!isValidUsername($usernameValue)) {
        $errorMessage = 'Der Benutzername muss mindestens 5 Zeichen lang sein und Groß- sowie Kleinbuchstaben enthalten.';

    // Passwort validieren
    } elseif (!isValidPassword($password)) {
        $errorMessage = 'Das Passwort muss mindestens 10 Zeichen lang sein.';

    // Prüfen, ob beide Passwörter übereinstimmen
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Die Passwörter stimmen nicht überein.';

    } else {

        // Überprüfen, ob der Benutzername bereits existiert
        $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $usernameValue]);

        // Falls ein Benutzer mit diesem Namen gefunden wurde
        if ($statement->fetch()) {
            $errorMessage = 'Dieser Benutzername ist bereits vergeben.';

        } else {

            // SQL-Statement zum Anlegen eines neuen Benutzers vorbereiten
            $insert = $pdo->prepare(
                'INSERT INTO users (username, password_hash, role, is_blocked)
                 VALUES (:username, :password_hash, :role, 0)'
            );

            // Benutzer in die Datenbank einfügen
            $insert->execute([
                'username' => $usernameValue,

                // Passwort sicher als Hash speichern
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),

                // Standardrolle für neue Benutzer
                'role' => 'user',
            ]);

            // Erfolgsmeldung setzen
            $successMessage = 'Konto erfolgreich erstellt. Du kannst dich jetzt anmelden.';

            // Eingabefeld für den Benutzernamen zurücksetzen
            $usernameValue = '';
        }
    }
}
// ## Schluss KI generierter Code
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
