<?php

// Zentrale Hilfsfunktionen fuer Schema, Login, Formatierung, Rabatte, Mail und App-Logik.
// Diese Datei wird von vielen Seiten eingebunden, damit gemeinsame Regeln nur einmal gepflegt werden muessen.

// Prueft, ob eine Tabelle bereits eine bestimmte Spalte besitzt.
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

// Liest die aktuelle Nutzer-ID aus der Session.
function appCurrentUserId(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

// Liest den aktuellen Benutzernamen aus der Session.
function appCurrentUsername(): string
{
    return (string) ($_SESSION['username'] ?? '');
}

// Prueft, ob eine gueltige Login-Session vorhanden ist.
function appIsLoggedIn(): bool
{
    return appCurrentUserId() > 0 && appCurrentUsername() !== '';
}

// Laedt den aktuellen Nutzer aus der Datenbank, sofern eine Session existiert.
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

// Erzwingt einen Login und leitet nicht angemeldete Nutzer weiter.
function appRequireLogin(string $returnTo = 'user.php'): void
{
    if (appIsLoggedIn()) {
        return;
    }

    $target = in_array($returnTo, ['user.php', 'cart.php', 'admin.php'], true) ? $returnTo : 'user.php';

    header('Location: login.php?return_to=' . urlencode($target));
    exit;
}

// Erzwingt Admin-Rechte und blockiert alle anderen Nutzer.
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

// Wandelt einen internen Buchungsstatus in eine lesbare Beschriftung um.
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

// Liefert die CSS-Klasse fuer den jeweiligen Buchungsstatus.
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

// Eine Buchung darf nur im Status "new" storniert werden.
function appBookingIsCancelable(array $booking): bool
{
    return ($booking['status'] ?? '') === 'new';
}

// Einheitliche Ausgabe von Geldbetragen mit deutschem Dezimaltrennzeichen.
function appFormatMoney(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' €';
}

// Formatiert Prozentwerte ohne ueberfluessige Nachkommastellen.
function appFormatPercent(float $percent): string
{
    return rtrim(rtrim(number_format($percent, 2, ',', '.'), '0'), ',');
}

// Speicherort des zentralen Rabattkatalogs.
function appCouponCatalogPath(): string
{
    return __DIR__ . '/../config/coupons.json';
}

// Laedt und normalisiert den Rabattkatalog aus JSON und merkt das Ergebnis fuer weitere Aufrufe.
function appLoadCouponCatalog(): array
{
    static $cachedCatalog = null;

    if (is_array($cachedCatalog)) {
        return $cachedCatalog;
    }

    $catalogPath = appCouponCatalogPath();

    if (!is_file($catalogPath)) {
        $cachedCatalog = [];
        return $cachedCatalog;
    }

    $catalogContents = file_get_contents($catalogPath);

    if ($catalogContents === false || trim($catalogContents) === '') {
        $cachedCatalog = [];
        return $cachedCatalog;
    }

    $decodedCatalog = json_decode($catalogContents, true);

    if (!is_array($decodedCatalog)) {
        $cachedCatalog = [];
        return $cachedCatalog;
    }

    $normalizedCatalog = [];

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

    $cachedCatalog = $normalizedCatalog;

    return $cachedCatalog;
}

// Erstellt eine kurze Rabatt-Zusammenfassung fuer Tabellen und Hinweise.
function appFormatDiscountSummary(?string $label, float $percent, float $amount): string
{
    if ($amount <= 0 || $percent <= 0) {
        return '-';
    }

    $prefix = $label ? $label . ': ' : '';

    return $prefix . '-' . appFormatMoney($amount) . ' (' . appFormatPercent($percent) . '%)';
}

// Liest SMTP-Konfiguration aus Datei oder aus Umgebungsvariablen.
function appGetMailConfig(): array
{
    $config = [
        'host' => '',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'tls',
        'from_email' => 'no-reply@localhost',
        'from_name' => 'WebTech Kontakt',
    ];

    $localConfigFile = __DIR__ . '/../config/mail.php';
    if (is_file($localConfigFile)) {
        $localConfig = require $localConfigFile;
        if (is_array($localConfig)) {
            $config = array_merge($config, array_intersect_key($localConfig, $config));
        }
    }

    $envFallbacks = [
        'host' => getenv('SMTP_HOST') ?: '',
        'port' => (int) (getenv('SMTP_PORT') ?: 587),
        'username' => getenv('SMTP_USERNAME') ?: '',
        'password' => getenv('SMTP_PASSWORD') ?: '',
        'encryption' => strtolower((string) (getenv('SMTP_ENCRYPTION') ?: 'tls')),
        'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'no-reply@localhost',
        'from_name' => getenv('SMTP_FROM_NAME') ?: 'WebTech Kontakt',
    ];

    foreach ($envFallbacks as $key => $value) {
        if (($config[$key] ?? '') === '' || $config[$key] === 587 || $config[$key] === 'tls' || $config[$key] === 'no-reply@localhost' || $config[$key] === 'WebTech Kontakt') {
            $config[$key] = $value;
        }
    }

    return $config;
}

// Liest eine SMTP-Serverantwort zeilenweise ein, bis der Abschlusscode erreicht ist.
function appSmtpReadResponse($connection): array
{
    $lines = [];

    while (!feof($connection)) {
        $line = fgets($connection, 515);

        if ($line === false) {
            break;
        }

        $lines[] = trim($line);

        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }

    $code = 0;
    if ($lines !== [] && preg_match('/^(\d{3})/', $lines[count($lines) - 1], $matches)) {
        $code = (int) $matches[1];
    }

    return [
        'code' => $code,
        'lines' => $lines,
    ];
}

// Schreibt einen SMTP-Befehl und prueft optional den erwarteten Antwortcode.
function appSmtpWriteCommand($connection, string $command, ?int $expectedCode = null): array
{
    fwrite($connection, $command . "\r\n");
    $response = appSmtpReadResponse($connection);

    if ($expectedCode !== null && $response['code'] !== $expectedCode) {
        throw new RuntimeException('SMTP-Fehler bei "' . $command . '": ' . implode(' | ', $response['lines']));
    }

    return $response;
}

// Sendet eine Mail direkt per SMTP ohne externe Bibliothek.
function appSendMailSmtp(array $config, string $toEmail, string $toName, string $subject, string $body, string $replyToEmail = '', string $replyToName = ''): array
{
    if (($config['host'] ?? '') === '' || ($config['username'] ?? '') === '' || ($config['password'] ?? '') === '') {
        return [
            'success' => false,
            'error' => 'SMTP ist nicht konfiguriert. Bitte SMTP_HOST, SMTP_USERNAME und SMTP_PASSWORD setzen.',
        ];
    }

    $host = (string) $config['host'];
    $port = (int) ($config['port'] ?? 587);
    $encryption = (string) ($config['encryption'] ?? 'tls');
    $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $remote = $transport . $host . ':' . $port;
    $timeout = 15;

    $connection = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

    if (!$connection) {
        return [
            'success' => false,
            'error' => 'SMTP-Verbindung fehlgeschlagen: ' . $errstr,
        ];
    }

    stream_set_timeout($connection, $timeout);

    try {
        $greeting = appSmtpReadResponse($connection);
        if (!in_array($greeting['code'], [220], true)) {
            throw new RuntimeException('SMTP-Server hat nicht korrekt geantwortet.');
        }

        $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
        appSmtpWriteCommand($connection, 'EHLO ' . $hostname, 250);

        if ($encryption === 'tls') {
            appSmtpWriteCommand($connection, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('TLS konnte nicht aktiviert werden.');
            }
            appSmtpWriteCommand($connection, 'EHLO ' . $hostname, 250);
        }

        appSmtpWriteCommand($connection, 'AUTH LOGIN', 334);
        appSmtpWriteCommand($connection, base64_encode((string) $config['username']), 334);
        appSmtpWriteCommand($connection, base64_encode((string) $config['password']), 235);

        $fromEmail = (string) ($config['from_email'] ?? $config['username']);
        $fromName = (string) ($config['from_name'] ?? 'WebTech Kontakt');
        $encodedSubject = function_exists('mb_encode_mimeheader')
            ? mb_encode_mimeheader($subject, 'UTF-8')
            : $subject;

        appSmtpWriteCommand($connection, 'MAIL FROM:<' . $fromEmail . '>', 250);
        appSmtpWriteCommand($connection, 'RCPT TO:<' . $toEmail . '>', 250);
        appSmtpWriteCommand($connection, 'DATA', 354);

        $headers = [
            'From: ' . $fromName . ' <' . $fromEmail . '>',
            'To: ' . $toName . ' <' . $toEmail . '>',
            'Subject: ' . $encodedSubject,
            'Date: ' . date(DATE_RFC2822),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        if ($replyToEmail !== '') {
            $replyName = $replyToName !== '' ? $replyToName : $replyToEmail;
            $headers[] = 'Reply-To: ' . $replyName . ' <' . $replyToEmail . '>';
        }

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $message = preg_replace("/\r\n|\r|\n/", "\r\n", $message);
        $message = preg_replace('/^\./m', '..', $message);

        fwrite($connection, $message . "\r\n.\r\n");
        $dataResponse = appSmtpReadResponse($connection);
        if (!in_array($dataResponse['code'], [250], true)) {
            throw new RuntimeException('SMTP konnte die Nachricht nicht akzeptieren.');
        }

        appSmtpWriteCommand($connection, 'QUIT', 221);

        fclose($connection);

        return [
            'success' => true,
            'error' => null,
        ];
    } catch (Throwable $throwable) {
        @fclose($connection);

        return [
            'success' => false,
            'error' => $throwable->getMessage(),
        ];
    }
}

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

function appFetchShoppingLists(PDO $pdo, int $userId): array
{
    $statement = $pdo->prepare(
        'SELECT id, list_name, created_at, updated_at
         FROM shopping_lists
         WHERE user_id = :user_id
         ORDER BY updated_at DESC, id DESC'
    );
    $statement->execute(['user_id' => $userId]);
    $lists = $statement->fetchAll() ?: [];

    if ($lists === []) {
        return [];
    }

    $listIds = array_map(static fn(array $list): int => (int) $list['id'], $lists);
    $placeholders = implode(',', array_fill(0, count($listIds), '?'));
    $itemsStatement = $pdo->prepare(
        "SELECT list_id, product_id, product_name, unit_price, quantity, image
         FROM shopping_list_items
         WHERE list_id IN ($placeholders)
         ORDER BY id ASC"
    );
    $itemsStatement->execute($listIds);
    $items = $itemsStatement->fetchAll() ?: [];

    $grouped = [];
    foreach ($items as $item) {
        $grouped[(int) $item['list_id']][] = $item;
    }

    foreach ($lists as &$list) {
        $list['items'] = $grouped[(int) $list['id']] ?? [];
    }
    unset($list);

    return $lists;
}

function appCreateShoppingList(PDO $pdo, int $userId, string $listName, array $cartItems): int
{
    $statement = $pdo->prepare(
        'INSERT INTO shopping_lists (user_id, list_name)
         VALUES (:user_id, :list_name)'
    );
    $statement->execute([
        'user_id' => $userId,
        'list_name' => $listName,
    ]);

    $listId = (int) $pdo->lastInsertId();
    $itemStatement = $pdo->prepare(
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

    foreach ($cartItems as $item) {
        $productName = trim((string) ($item['name'] ?? ''));
        $unitPrice = (float) ($item['price'] ?? 0);
        $quantity = (int) ($item['menge'] ?? 0);

        if ($productName === '' || $unitPrice <= 0 || $quantity <= 0) {
            continue;
        }

        $itemStatement->execute([
            'list_id' => $listId,
            'product_id' => isset($item['id']) ? (int) $item['id'] : null,
            'product_name' => $productName,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'image' => trim((string) ($item['image'] ?? '')) ?: null,
        ]);
    }

    return $listId;
}

function appFetchShoppingListsByIds(PDO $pdo, int $userId, array $listIds): array
{
    $listIds = array_values(array_unique(array_map('intval', $listIds)));

    if ($listIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($listIds), '?'));
    $statement = $pdo->prepare(
        "SELECT id, list_name
         FROM shopping_lists
         WHERE user_id = ?
           AND id IN ($placeholders)"
    );
    $statement->execute(array_merge([$userId], $listIds));
    $lists = $statement->fetchAll() ?: [];

    if ($lists === []) {
        return [];
    }

    $foundIds = array_map(static fn(array $list): int => (int) $list['id'], $lists);
    $itemPlaceholders = implode(',', array_fill(0, count($foundIds), '?'));
    $itemsStatement = $pdo->prepare(
        "SELECT list_id, product_id, product_name, unit_price, quantity, image
         FROM shopping_list_items
         WHERE list_id IN ($itemPlaceholders)
         ORDER BY id ASC"
    );
    $itemsStatement->execute($foundIds);
    $items = $itemsStatement->fetchAll() ?: [];

    $grouped = [];
    foreach ($items as $item) {
        $grouped[(int) $item['list_id']][] = $item;
    }

    foreach ($lists as &$list) {
        $list['items'] = $grouped[(int) $list['id']] ?? [];
    }
    unset($list);

    return $lists;
}

function appFlattenShoppingLists(array $lists): array
{
    $cart = [];

    foreach ($lists as $list) {
        foreach (($list['items'] ?? []) as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $foundIndex = null;

            foreach ($cart as $index => $entry) {
                if ((int) $entry['id'] === $productId) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex !== null) {
                $cart[$foundIndex]['menge'] += (int) $item['quantity'];
            } else {
                $cart[] = [
                    'id' => $productId,
                    'name' => (string) ($item['product_name'] ?? ''),
                    'price' => (float) ($item['unit_price'] ?? 0),
                    'image' => (string) ($item['image'] ?? ''),
                    'menge' => (int) ($item['quantity'] ?? 0),
                ];
            }
        }
    }

    return $cart;
}

function appFetchBookingCartItems(PDO $pdo, int $bookingId, int $userId): array
{
    $statement = $pdo->prepare(
        'SELECT b.id
         FROM bookings b
         WHERE b.id = :booking_id
           AND b.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'booking_id' => $bookingId,
        'user_id' => $userId,
    ]);

    if (!$statement->fetch()) {
        return [];
    }

    $itemsStatement = $pdo->prepare(
        'SELECT product_id, product_name, unit_price, quantity, image
         FROM booking_items
         WHERE booking_id = :booking_id
         ORDER BY id ASC'
    );
    $itemsStatement->execute(['booking_id' => $bookingId]);
    $items = $itemsStatement->fetchAll() ?: [];

    $cart = [];
    foreach ($items as $item) {
        $cart[] = [
            'id' => (int) ($item['product_id'] ?? 0),
            'name' => (string) ($item['product_name'] ?? ''),
            'price' => (float) ($item['unit_price'] ?? 0),
            'image' => (string) ($item['image'] ?? ''),
            'menge' => (int) ($item['quantity'] ?? 0),
        ];
    }

    return $cart;
}

function appGetDiscountConfig(PDO $pdo): array
{
    return [
        'enabled' => appGetSetting($pdo, 'discount_enabled', '1') === '1',
        'ten_percent' => max(0.0, min(100.0, (float) appGetSetting($pdo, 'discount_10_percent', '10'))),
        'twenty_percent' => max(0.0, min(100.0, (float) appGetSetting($pdo, 'discount_20_percent', '20'))),
    ];
}

function appCalculateBookingDiscount(int $bookingNumber, float $subtotal, array $config): array
{
    $percent = 0.0;
    $label = null;

    if (!($config['enabled'] ?? false)) {
        return [
            'percent' => 0.0,
            'amount' => 0.0,
            'label' => null,
            'final_total' => $subtotal,
        ];
    }

    if ($bookingNumber > 0 && $bookingNumber % 20 === 0) {
        $percent = (float) ($config['twenty_percent'] ?? 20);
        $label = '20. Bestellung';
    } elseif ($bookingNumber > 0 && $bookingNumber % 10 === 0) {
        $percent = (float) ($config['ten_percent'] ?? 10);
        $label = '10. Bestellung';
    }

    $amount = round($subtotal * ($percent / 100), 2);
    $finalTotal = max(0, round($subtotal - $amount, 2));

    return [
        'percent' => $percent,
        'amount' => $amount,
        'label' => $label,
        'final_total' => $finalTotal,
    ];
}

function appGetCouponCatalog(): array
{
    return appLoadCouponCatalog();
}

function appNormalizeCouponCode(string $couponCode): string
{
    return strtoupper(trim($couponCode));
}

function appCalculateCouponDiscount(string $couponCode, float $subtotal): array
{
    $normalizedCode = appNormalizeCouponCode($couponCode);
    $catalog = appGetCouponCatalog();

    if ($normalizedCode === '' || !isset($catalog[$normalizedCode])) {
        return [
            'code' => '',
            'label' => null,
            'percent' => 0.0,
            'amount' => 0.0,
            'final_total' => $subtotal,
            'valid' => false,
        ];
    }

    $coupon = $catalog[$normalizedCode];
    $percent = max(0.0, min(100.0, (float) ($coupon['percent'] ?? 0)));
    $amount = round($subtotal * ($percent / 100), 2);
    $finalTotal = max(0, round($subtotal - $amount, 2));

    return [
        'code' => $normalizedCode,
        'label' => (string) ($coupon['label'] ?? $normalizedCode),
        'percent' => $percent,
        'amount' => $amount,
        'final_total' => $finalTotal,
        'valid' => true,
    ];
}
