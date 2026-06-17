<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/app.php';

if (isset($pdo)) {
    appEnsureSchema($pdo);
}
