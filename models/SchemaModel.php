<?php

// ## Beginn generierter Code von Codex

function appTableHasColumn(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->query(sprintf(
        'SHOW COLUMNS FROM `%s` LIKE %s',
        str_replace('`', '``', $table),
        $pdo->quote($column)
    ));

    return (bool) $statement->fetch();
}

function appEnsureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    if (appTableHasColumn($pdo, $table, $column)) {
        return;
    }

    $pdo->exec(sprintf(
        'ALTER TABLE `%s` ADD COLUMN `%s` %s',
        str_replace('`', '``', $table),
        str_replace('`', '``', $column),
        $definition
    ));
}

function appDefaultAdmin1PasswordHash(): string
{
    return '$2y$10$G8OWFxUeOXPrcI9n4EaeR.cQfNc8kNXAv.osJ4IdRXp4m7ZNNNmru';
}

function appSeedProductCatalog(PDO $pdo): void
{
    if (appGetSetting($pdo, 'product_catalog_seeded', '0') === '1') {
        return;
    }

    $catalogPath = __DIR__ . '/../config/product.json';
    if (!is_file($catalogPath)) {
        return;
    }

    $catalogContents = file_get_contents($catalogPath);
    if ($catalogContents === false || trim($catalogContents) === '') {
        return;
    }

    $catalogData = json_decode($catalogContents, true);
    if (!is_array($catalogData) || !isset($catalogData['products']) || !is_array($catalogData['products'])) {
        return;
    }

    $imageStatement = $pdo->prepare(
        'INSERT INTO product_images (file_path, original_name)
         VALUES (:file_path, :original_name)
         ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)'
    );

    $productStatement = $pdo->prepare(
        'INSERT INTO products (
            name,
            description,
            price,
            category,
            subcategory,
            image_id
        ) VALUES (
            :name,
            :description,
            :price,
            :category,
            :subcategory,
            :image_id
        )'
    );

    foreach ($catalogData['products'] as $product) {
        if (!is_array($product)) {
            continue;
        }

        $imagePath = trim((string) ($product['image'] ?? ''));
        $imageId = null;

        if ($imagePath !== '') {
            $imageStatement->execute([
                'file_path' => $imagePath,
                'original_name' => basename($imagePath),
            ]);
            $imageId = (int) $pdo->lastInsertId();
        }

        $productStatement->execute([
            'name' => trim((string) ($product['name'] ?? '')),
            'description' => trim((string) ($product['description'] ?? '')),
            'price' => (float) ($product['price'] ?? 0),
            'category' => trim((string) ($product['category'] ?? '')),
            'subcategory' => trim((string) ($product['subcategory'] ?? '')),
            'image_id' => $imageId,
        ]);
    }

    appSetSetting($pdo, 'product_catalog_seeded', '1');
}

function appEnsureSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT "user",
            is_blocked TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    appEnsureColumn($pdo, 'users', 'role', 'VARCHAR(20) NOT NULL DEFAULT "user"');
    appEnsureColumn($pdo, 'users', 'is_blocked', 'TINYINT(1) NOT NULL DEFAULT 0');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS app_settings (
            setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
            setting_value VARCHAR(255) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS bookings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            subtotal_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            total_amount DECIMAL(10,2) NOT NULL,
            discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            discount_label VARCHAR(100) DEFAULT NULL,
            status VARCHAR(32) NOT NULL DEFAULT "new",
            rejection_reason VARCHAR(255) DEFAULT NULL,
            processed_at DATETIME DEFAULT NULL,
            cancelled_at DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    appEnsureColumn($pdo, 'bookings', 'status', 'VARCHAR(32) NOT NULL DEFAULT "new"');
    appEnsureColumn($pdo, 'bookings', 'rejection_reason', 'VARCHAR(255) DEFAULT NULL');
    appEnsureColumn($pdo, 'bookings', 'processed_at', 'DATETIME DEFAULT NULL');
    appEnsureColumn($pdo, 'bookings', 'cancelled_at', 'DATETIME DEFAULT NULL');
    appEnsureColumn($pdo, 'bookings', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    appEnsureColumn($pdo, 'bookings', 'subtotal_amount', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    appEnsureColumn($pdo, 'bookings', 'discount_percent', 'DECIMAL(5,2) NOT NULL DEFAULT 0.00');
    appEnsureColumn($pdo, 'bookings', 'discount_amount', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    appEnsureColumn($pdo, 'bookings', 'discount_label', 'VARCHAR(100) DEFAULT NULL');

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS booking_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NULL,
            product_name VARCHAR(255) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INT UNSIGNED NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (booking_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS shopping_lists (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            list_name VARCHAR(150) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS shopping_list_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            list_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NULL,
            product_name VARCHAR(255) NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            quantity INT UNSIGNED NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (list_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS user_notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            booking_id INT UNSIGNED NULL,
            notification_type VARCHAR(50) NOT NULL DEFAULT "info",
            message VARCHAR(255) NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME DEFAULT NULL,
            INDEX (user_id),
            INDEX (booking_id),
            INDEX (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS product_images (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            file_path VARCHAR(255) NOT NULL UNIQUE,
            original_name VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            description TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            category VARCHAR(100) NOT NULL DEFAULT "",
            subcategory VARCHAR(100) NOT NULL DEFAULT "",
            image_id INT UNSIGNED DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (image_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    appSeedProductCatalog($pdo);

    $adminStatement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $adminStatement->execute(['username' => 'admin']);
    $adminUser = $adminStatement->fetch();

    if (!$adminUser) {
        $createAdmin = $pdo->prepare(
            'INSERT INTO users (username, password_hash, role, is_blocked)
             VALUES (:username, :password_hash, :role, 0)'
        );
        $createAdmin->execute([
            'username' => 'admin',
            'password_hash' => password_hash('Admin12345!', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
        return;
    }

    $ensureAdmin = $pdo->prepare(
        'UPDATE users
         SET role = "admin",
             is_blocked = 0
         WHERE username = :username'
    );
    $ensureAdmin->execute(['username' => 'admin']);

    $secondaryAdminStatement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
    $secondaryAdminStatement->execute(['username' => 'admin1']);
    $secondaryAdmin = $secondaryAdminStatement->fetch();

    if (!$secondaryAdmin) {
        $createSecondaryAdmin = $pdo->prepare(
            'INSERT INTO users (username, password_hash, role, is_blocked)
             VALUES (:username, :password_hash, :role, 0)'
        );
        $createSecondaryAdmin->execute([
            'username' => 'admin1',
            'password_hash' => appDefaultAdmin1PasswordHash(),
            'role' => 'admin',
        ]);
    } else {
        $promoteSecondaryAdmin = $pdo->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 role = "admin",
                 is_blocked = 0
             WHERE username = :username'
        );
        $promoteSecondaryAdmin->execute([
            'username' => 'admin1',
            'password_hash' => appDefaultAdmin1PasswordHash(),
        ]);
    }

    $defaultSettings = [
        'discount_enabled' => '1',
        'discount_10_percent' => '10',
        'discount_20_percent' => '20',
    ];

    $ensureSetting = $pdo->prepare(
        'INSERT INTO app_settings (setting_key, setting_value)
         VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = setting_value'
    );

    foreach ($defaultSettings as $settingKey => $settingValue) {
        $ensureSetting->execute([
            'setting_key' => $settingKey,
            'setting_value' => $settingValue,
        ]);
    }
}

// ## Schluss generierter Code von Codex
