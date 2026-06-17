<?php

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
