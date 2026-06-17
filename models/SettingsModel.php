<?php

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
