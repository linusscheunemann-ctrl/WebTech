<?php

// Zentrale Hilfsfunktionen fuer Schema, Login, Formatierung, Rabatte, Mail und App-Logik.
// Diese Datei wird von vielen Seiten eingebunden, damit gemeinsame Regeln nur einmal gepflegt werden muessen.

// Prueft, ob eine Tabelle bereits eine bestimmte Spalte besitzt.
// ## Beginn KI generierter Code
function appTableHasColumn(PDO $pdo, string $table, string $column): bool
{
    $statement = $pdo->query(sprintf(
        'SHOW COLUMNS FROM `%s` LIKE %s',
        str_replace('`', '``', $table),
        $pdo->quote($column)
    ));

    return (bool) $statement->fetch();
}

// Fuegt eine Spalte nur dann hinzu, wenn sie noch nicht existiert.
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
// ## Schluss KI generierter Code
// ## Beginn Code von Linus
// Legt die benoetigten Tabellen an und erweitert sie bei Bedarf um fehlende Spalten.
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

// ## Schluss Code von Linus
// ## Beginn Code von Moritz
    // Ein Admin-Account soll immer existieren, damit das System verwaltet werden kann.
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

    $promoteAdmin = $pdo->prepare(
        'UPDATE users
         SET role = "admin",
             is_blocked = 0
         WHERE username = :username'
    );
    $promoteAdmin->execute(['username' => 'admin']);

    // Vorgabewerte fuer Rabatt-Einstellungen werden beim ersten Start angelegt.
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
// ## Schluss Code von Moritz
// Liest die aktuelle Nutzer-ID aus der Session
function appCurrentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

// Liest den aktuellen Benutzernamen aus der Session
function appCurrentUsername(): string
{
    return (string) ($_SESSION['username'] ?? '');
}

// Prüft, ob der Benutzer eingeloggt ist
function appIsLoggedIn(): bool
{
    return appCurrentUserId() > 0 && appCurrentUsername() !== '';
}

// Lädt den aktuellen Benutzer aus der Datenbank
function appLoadCurrentUser(PDO $pdo): ?array
{
    if (!appIsLoggedIn()) {
        return null;
    }

    // Holt Benutzer anhand der Session-ID
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

// Erzwingt Login und leitet sonst weiter
function appRequireLogin(string $returnTo = 'user.php'): void
{
    if (appIsLoggedIn()) {
        return;
    }

    // Validiert erlaubte Redirect-Ziele
    $target = in_array($returnTo, ['user.php', 'cart.php', 'admin.php'], true)
        ? $returnTo
        : 'user.php';

    header('Location: login.php?return_to=' . urlencode($target));
    exit;
}

// Erzwingt Admin-Zugriff
function appRequireAdmin(PDO $pdo): array
{
    appRequireLogin('admin.php');

    $user = appLoadCurrentUser($pdo);

    // Prüft Admin-Rolle
    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        header('Location: user.php');
        exit;
    }

    return $user;
}

// Wandelt Status in lesbaren Text um
function appBookingStatusLabel(string $status): string
{
    return match ($status) {
        'new' => 'Bestellt',
        'processing' => 'Versandt, aber nicht erhalten',
        'completed' => 'Fertig',
        'rejected', 'cancelled' => 'Storniert',
        default => ucfirst($status),
    };
}

// Liefert CSS-Klasse für Status
function appBookingStatusClass(string $status): string
{
    return match ($status) {
        'new' => 'status-new',
        'processing' => 'status-processing',
        'completed' => 'status-completed',
        'rejected' => 'status-rejected',
        'cancelled' => 'status-cancelled',
        default => 'status-unknown',
    };
}

// Prüft, ob Buchung storniert werden kann
function appBookingIsCancelable(array $booking): bool
{
    return ($booking['status'] ?? '') === 'new';
}

// Formatiert Geldbetrag
function appFormatMoney(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

// Formatiert Prozentwert
function appFormatPercent(float $percent): string
{
    return rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',');
}

// Pfad zum Coupon-File
function appCouponCatalogPath(): string
{
    return __DIR__ . '/../config/coupons.json';
}

// Lädt Coupon-Katalog mit Cache
function appLoadCouponCatalog(): array
{
    static $cachedCatalog = null;

    if (is_array($cachedCatalog)) {
        return $cachedCatalog;
    }

    $catalogPath = appCouponCatalogPath();

    if (!is_file($catalogPath)) {
        return $cachedCatalog = [];
    }

    $catalogContents = file_get_contents($catalogPath);

    if ($catalogContents === false || trim($catalogContents) === '') {
        return $cachedCatalog = [];
    }

    $decodedCatalog = json_decode($catalogContents, true);

    if (!is_array($decodedCatalog)) {
        return $cachedCatalog = [];
    }

    $normalizedCatalog = [];

    // Normalisiert Coupon-Codes
    foreach ($decodedCatalog as $couponCode => $couponData) {
        if (!is_array($couponData)) {
            continue;
        }

        $normalizedCode = strtoupper(trim((string) $couponCode));
        if ($normalizedCode === '') {
            continue;
        }

        $normalizedCatalog[$normalizedCode] = [
            'label' => (string) ($couponData['label'] ?? $normalizedCode),
            'percent' => max(0.0, min(100.0, (float) ($couponData['percent'] ?? 0))),
        ];
    }

    return $cachedCatalog = $normalizedCatalog;
}

// Erstellt Rabatt-Zusammenfassung
function appFormatDiscountSummary(?string $label, float $percent, float $amount): string
{
    if ($amount <= 0 || $percent <= 0) {
        return '-';
    }

    $prefix = $label ? $label . ': ' : '';
    return $prefix . '-' . appFormatMoney($amount) . ' (' . appFormatPercent($percent) . '%)';
}

// Liest die zentralen Rabatteinstellungen fuer den Adminbereich aus der Datenbank.
function appGetDiscountConfig(PDO $pdo): array
{
    return [
        'enabled' => appGetSetting($pdo, 'discount_enabled', '1') === '1',
        'ten_percent' => (float) appGetSetting($pdo, 'discount_10_percent', '10'),
        'twenty_percent' => (float) appGetSetting($pdo, 'discount_20_percent', '20'),
    ];
}

// Liest Setting aus DB
function appGetSetting(PDO $pdo, string $key, string $default = ''): string
{
    $statement = $pdo->prepare(
        'SELECT setting_value
         FROM app_settings
         WHERE setting_key = :setting_key
         LIMIT 1'
    );

    $statement->execute(['setting_key' => $key]);
    $value = $statement->fetchColumn();

    return $value === false ? $default : (string) $value;
}

// Speichert Setting in DB
function appSetSetting(PDO $pdo, string $key, string $value): void
{
    $statement = $pdo->prepare(
        'INSERT INTO app_settings (setting_key, setting_value)
         VALUES (:setting_key, :setting_value)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    $statement->execute([
        'setting_key' => $key,
        'setting_value' => $value,
    ]);
}

// Erstellt Notification
function appCreateNotification(PDO $pdo, int $userId, string $message, string $type = 'info', ?int $bookingId = null): void
{
    $statement = $pdo->prepare(
        'INSERT INTO user_notifications (
            user_id,
            booking_id,
            notification_type,
            message,
            is_read,
            created_at
        ) VALUES (
            :user_id,
            :booking_id,
            :notification_type,
            :message,
            0,
            NOW()
        )'
    );

    $statement->execute([
        'user_id' => $userId,
        'booking_id' => $bookingId,
        'notification_type' => $type,
        'message' => $message,
    ]);
}

// Zählt ungelesene Notifications
function appGetUnreadNotificationCount(PDO $pdo, int $userId): int
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM user_notifications
         WHERE user_id = :user_id
           AND is_read = 0'
    );

    $statement->execute(['user_id' => $userId]);

    return (int) $statement->fetchColumn();
}

// Holt letzte Notifications
function appFetchUserNotifications(PDO $pdo, int $userId, int $limit = 5): array
{
    $statement = $pdo->prepare(
        'SELECT id, booking_id, notification_type, message, is_read, created_at
         FROM user_notifications
         WHERE user_id = :user_id
         ORDER BY created_at DESC, id DESC
         LIMIT :limit'
    );

    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll() ?: [];
}

// Markiert Notifications als gelesen
function appMarkNotificationsRead(PDO $pdo, int $userId): void
{
    $statement = $pdo->prepare(
        'UPDATE user_notifications
         SET is_read = 1,
             read_at = NOW()
         WHERE user_id = :user_id
           AND is_read = 0'
    );

    $statement->execute(['user_id' => $userId]);
}
