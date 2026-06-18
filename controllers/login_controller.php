<?php

// ## Beginn generierter Code von Codex

require_once __DIR__ . '/_bootstrap.php';

$errorMessage = '';
$usernameValue = '';
$cookies_enabled = !empty($_COOKIE);
$returnTo = $_GET['return_to'] ?? $_POST['return_to'] ?? 'user.php';

$allowedTargets = ['cart.php', 'user.php', 'admin.php'];
if (!in_array($returnTo, $allowedTargets, true)) {
    $returnTo = 'user.php';
}

if (appIsLoggedIn()) {
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

require __DIR__ . '/../views/login_view.php';

// ## Schluss generierter Code von Codex
