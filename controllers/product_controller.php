<?php

require_once __DIR__ . '/_bootstrap.php';

if (!isset($_GET['pid']) || $_GET['pid'] === '') {
    die('Parameter is missing!');
}

if (!isset($pdo)) {
    die('Datenbankverbindung nicht verfügbar.');
}

$pid = (int) $_GET['pid'];
$pid2 = null;

if (isset($_GET['id2']) && $_GET['id2'] !== '') {
    $pid2 = (int) $_GET['id2'];
}

$product1 = appFetchProductById($pdo, $pid);
if (!$product1) {
    die('Product not found for ID: ' . $pid);
}

$product2 = null;
if ($pid2 !== null) {
    $product2 = appFetchProductById($pdo, $pid2);
}
