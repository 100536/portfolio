<?php

declare(strict_types=1);

/**
 * Schrijf een event naar activity_log.
 * $meta is vrij: bv. ['title'=>$title,'status'=>$status,'ip'=>...]
 */
function log_event(PDO $pdo, ?int $userId, ?string $username, string $action, string $entity, int $entityId = 0, array $meta = []): void
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
