<?php

// ## Beginn generierter Code von Codex

function appFetchProducts(PDO $pdo, string $search = ''): array
{
    $search = trim($search);
    $sql = '
        SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.category,
            p.subcategory,
            p.image_id,
            i.file_path AS image_path,
            i.original_name AS image_original_name
        FROM products p
        LEFT JOIN product_images i ON i.id = p.image_id';
    $params = [];

    if ($search !== '') {
        $sql .= '
         WHERE (
            p.name LIKE :search
            OR p.description LIKE :search
            OR p.category LIKE :search
            OR p.subcategory LIKE :search
         )';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY p.id ASC';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll() ?: [];
}

function appFetchProductById(PDO $pdo, int $productId): ?array
{
    $statement = $pdo->prepare(
        'SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.category,
            p.subcategory,
            p.image_id,
            i.file_path AS image_path,
            i.original_name AS image_original_name
         FROM products p
         LEFT JOIN product_images i ON i.id = p.image_id
         WHERE p.id = :product_id
         LIMIT 1'
    );

    $statement->execute(['product_id' => $productId]);
    $product = $statement->fetch();

    return $product ?: null;
}

function appFetchProductImages(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT id, file_path, original_name, created_at
         FROM product_images
         ORDER BY id DESC'
    );

    return $statement ? ($statement->fetchAll() ?: []) : [];
}

// ## Schluss generierter Code von Codex
