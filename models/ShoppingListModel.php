<?php

// ## Beginn generierter Code von Codex

function appFetchShoppingLists(PDO $pdo, int $userId): array
{
    $statement = $pdo->prepare(
        'SELECT
            l.id AS list_id,
            l.list_name,
            l.updated_at,
            li.id AS item_id,
            li.product_id,
            li.product_name,
            li.unit_price,
            li.quantity,
            li.image
         FROM shopping_lists l
         LEFT JOIN shopping_list_items li ON li.list_id = l.id
         WHERE l.user_id = :user_id
         ORDER BY l.updated_at DESC, l.id DESC, li.id ASC'
    );

    $statement->execute(['user_id' => $userId]);
    $rows = $statement->fetchAll() ?: [];

    $shoppingLists = [];
    foreach ($rows as $row) {
        $listId = (int) ($row['list_id'] ?? 0);
        if ($listId <= 0) {
            continue;
        }

        if (!isset($shoppingLists[$listId])) {
            $shoppingLists[$listId] = [
                'id' => $listId,
                'list_name' => (string) ($row['list_name'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'items' => [],
            ];
        }

        if (!empty($row['item_id'])) {
            $shoppingLists[$listId]['items'][] = [
                'id' => (int) $row['item_id'],
                'product_id' => isset($row['product_id']) ? (int) $row['product_id'] : null,
                'product_name' => (string) ($row['product_name'] ?? ''),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
                'quantity' => (int) ($row['quantity'] ?? 0),
                'image' => (string) ($row['image'] ?? ''),
            ];
        }
    }

    return array_values($shoppingLists);
}

function appFetchShoppingListsByIds(PDO $pdo, int $userId, array $listIds): array
{
    $filteredIds = array_values(array_unique(array_filter(array_map('intval', $listIds), static fn(int $id): bool => $id > 0)));

    if ($filteredIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($filteredIds), '?'));
    $statement = $pdo->prepare(
        'SELECT
            l.id AS list_id,
            l.list_name,
            l.updated_at,
            li.id AS item_id,
            li.product_id,
            li.product_name,
            li.unit_price,
            li.quantity,
            li.image
         FROM shopping_lists l
         LEFT JOIN shopping_list_items li ON li.list_id = l.id
         WHERE l.user_id = ?
           AND l.id IN (' . $placeholders . ')
         ORDER BY l.updated_at DESC, l.id DESC, li.id ASC'
    );

    $statement->execute(array_merge([$userId], $filteredIds));
    $rows = $statement->fetchAll() ?: [];

    $shoppingLists = [];
    foreach ($rows as $row) {
        $listId = (int) ($row['list_id'] ?? 0);
        if ($listId <= 0) {
            continue;
        }

        if (!isset($shoppingLists[$listId])) {
            $shoppingLists[$listId] = [
                'id' => $listId,
                'list_name' => (string) ($row['list_name'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'items' => [],
            ];
        }

        if (!empty($row['item_id'])) {
            $shoppingLists[$listId]['items'][] = [
                'id' => (int) $row['item_id'],
                'product_id' => isset($row['product_id']) ? (int) $row['product_id'] : null,
                'product_name' => (string) ($row['product_name'] ?? ''),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
                'quantity' => (int) ($row['quantity'] ?? 0),
                'image' => (string) ($row['image'] ?? ''),
            ];
        }
    }

    return array_values($shoppingLists);
}

function appFlattenShoppingLists(array $lists): array
{
    $merged = [];

    foreach ($lists as $list) {
        $items = $list['items'] ?? [];

        foreach ($items as $item) {
            $itemName = trim((string) ($item['product_name'] ?? ''));
            $quantity = max(1, (int) ($item['quantity'] ?? 1));
            $price = max(0.0, (float) ($item['unit_price'] ?? 0));
            $image = trim((string) ($item['image'] ?? ''));
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : 0;
            $key = $productId > 0 ? 'product:' . $productId : md5($itemName . '|' . $price . '|' . $image);

            if ($itemName === '' || $price <= 0) {
                continue;
            }

            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'id' => $productId > 0 ? $productId : count($merged) + 1,
                    'name' => $itemName,
                    'price' => $price,
                    'image' => $image,
                    'menge' => 0,
                ];
            }

            $merged[$key]['menge'] += $quantity;
        }
    }

    return array_values($merged);
}

function appCreateShoppingList(PDO $pdo, int $userId, string $listName, array $cartItems): int
{
    $filteredItems = [];
    foreach ($cartItems as $item) {
        $name = trim((string) ($item['name'] ?? ''));
        $price = max(0.0, (float) ($item['price'] ?? 0));
        $quantity = max(1, (int) ($item['menge'] ?? 1));
        $image = trim((string) ($item['image'] ?? ''));
        $productId = isset($item['id']) ? (int) $item['id'] : null;

        if ($name === '' || $price <= 0) {
            continue;
        }

        $filteredItems[] = [
            'product_id' => $productId,
            'product_name' => $name,
            'unit_price' => $price,
            'quantity' => $quantity,
            'image' => $image !== '' ? $image : null,
        ];
    }

    if ($filteredItems === []) {
        throw new RuntimeException('Der Warenkorb ist leer, daher kann keine Sammelliste gespeichert werden.');
    }

    $pdo->beginTransaction();

    try {
        $insertList = $pdo->prepare(
            'INSERT INTO shopping_lists (user_id, list_name)
             VALUES (:user_id, :list_name)'
        );
        $insertList->execute([
            'user_id' => $userId,
            'list_name' => $listName,
        ]);

        $listId = (int) $pdo->lastInsertId();

        $insertItem = $pdo->prepare(
            'INSERT INTO shopping_list_items (
                list_id,
                product_id,
                product_name,
                unit_price,
                quantity,
                image
            ) VALUES (
                :list_id,
                :product_id,
                :product_name,
                :unit_price,
                :quantity,
                :image
            )'
        );

        foreach ($filteredItems as $item) {
            $insertItem->execute([
                'list_id' => $listId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'image' => $item['image'],
            ]);
        }

        $pdo->commit();

        return $listId;
    } catch (Throwable $throwable) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $throwable;
    }
}

// ## Schluss generierter Code von Codex
