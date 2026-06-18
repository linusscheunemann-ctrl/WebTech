<?php

// ## Beginn generierter Code von Codex

function appCurrentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

function appCurrentUsername(): string
{
    return (string) ($_SESSION['username'] ?? '');
}

function appIsLoggedIn(): bool
{
    return appCurrentUserId() > 0 && appCurrentUsername() !== '';
}

function appLoadCurrentUser(PDO $pdo): ?array
{
    if (!appIsLoggedIn()) {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT id, username, role, is_blocked
         FROM users
         WHERE id = :id
         LIMIT 1'
    );

    $statement->execute(['id' => appCurrentUserId()]);
    $user = $statement->fetch();

    return $user ?: null;
}

function appRequireLogin(string $returnTo = 'user.php'): void
{
    if (appIsLoggedIn()) {
        return;
    }

    $target = in_array($returnTo, ['user.php', 'cart.php', 'admin.php'], true)
        ? $returnTo
        : 'user.php';

    header('Location: login.php?return_to=' . urlencode($target));
    exit;
}

function appRequireAdmin(PDO $pdo): array
{
    appRequireLogin('admin.php');

    $user = appLoadCurrentUser($pdo);

    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        header('Location: user.php');
        exit;
    }

    return $user;
}

// ## Schluss generierter Code von Codex
