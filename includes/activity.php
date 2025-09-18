<?php

declare(strict_types=1);

function log_event(PDO $pdo, $userId, $username, string $action, string $entity, int $entityId, array $meta = []): void
{
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, username, action, entity, entity_id, meta)
        VALUES (:uid, :uname, :action, :entity, :eid, :meta)
    ");
    $stmt->execute([
        ':uid' => $userId,
        ':uname' => $username,
        ':action' => $action,
        ':entity' => $entity,
        ':eid' => $entityId,
        ':meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);
}
