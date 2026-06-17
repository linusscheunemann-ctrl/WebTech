<?php

require_once __DIR__ . '/_bootstrap.php';

$errorMessage = '';
$successMessage = '';
$usernameValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameValue = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!isset($pdo)) {
        $errorMessage = 'Die Datenbankverbindung ist aktuell nicht verfügbar.';
    } elseif (!appIsValidUsername($usernameValue)) {
        $errorMessage = 'Der Benutzername muss mindestens 5 Zeichen lang sein und Groß- sowie Kleinbuchstaben enthalten.';
    } elseif (!appIsValidPassword($password)) {
        $errorMessage = 'Das Passwort muss mindestens 10 Zeichen lang sein.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Die Passwörter stimmen nicht überein.';
    } else {
        $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $usernameValue]);

        if ($statement->fetch()) {
            $errorMessage = 'Dieser Benutzername ist bereits vergeben.';
        } else {
            $insert = $pdo->prepare(
                'INSERT INTO users (username, password_hash, role, is_blocked)
                 VALUES (:username, :password_hash, :role, 0)'
            );

            $insert->execute([
                'username' => $usernameValue,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user',
            ]);

            $successMessage = 'Konto erfolgreich erstellt. Du kannst dich jetzt anmelden.';
            $usernameValue = '';
        }
    }
}
