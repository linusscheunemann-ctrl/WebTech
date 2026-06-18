<?php
// ## Beginn generierter Code von Codex
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/app.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo)) {
    echo json_encode(['products' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

appEnsureSchema($pdo);

$search = trim((string) ($_GET['q'] ?? ''));
$products = appFetchProducts($pdo, $search);
$responseProducts = array_map(static function (array $product): array {
    return [
        'id' => (int) $product['id'],
        'name' => (string) $product['name'],
        'description' => (string) $product['description'],
        'image' => (string) ($product['image_path'] ?? ''),
        'price' => (float) $product['price'],
        'category' => (string) $product['category'],
        'subcategory' => (string) $product['subcategory'],
    ];
}, $products);

echo json_encode([
    'products' => $responseProducts,
    'count' => count($responseProducts),
    'search' => $search,
], JSON_UNESCAPED_UNICODE);
// ## Schluss generierter Code von Codex