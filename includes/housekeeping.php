<?php

declare(strict_types=1);

/**
 * Purge van activity_log om de 15 dagen (zonder cron).
 * - Leegt de log in één keer (TRUNCATE). Als dat niet mag op je host, valt hij terug op batched DELETE.
 * - Draait alleen bij een admin-bezoek én alleen als de laatste purge ≥ 15 dagen geleden is.
 * - Onthoudt de laatste run in app_state.
 */

const HK_PURGE_EVERY_DAYS = 15;    // om de 15 dagen lege log
const HK_CHECK_INTERVAL_MINUTES = 60;    // niet vaker dan eens per uur checken
const HK_DELETE_BATCH = 10000; // fallback batchgrootte bij DELETE

function hk_maybe(PDO $pdo): array
{
    // Tabel voor state bijhouden
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS app_state (
          k VARCHAR(64) PRIMARY KEY,
          v TEXT,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Huidige state ophalen
    $stmt = $pdo->prepare("SELECT v FROM app_state WHERE k='hk_purge' LIMIT 1");
    $stmt->execute();
    $stateRaw = $stmt->fetchColumn();
    $state = $stateRaw ? json_decode((string)$stateRaw, true) : null;

    $now = time();
    $lastPurge = isset($state['last']) ? (int)$state['last'] : 0;
    $lastCheck = isset($state['last_check']) ? (int)$state['last_check'] : 0;
    $dueSeconds = HK_PURGE_EVERY_DAYS * 86400;
    $checkWindow = HK_CHECK_INTERVAL_MINUTES * 60;

    // Niet aan de beurt? Alleen af en toe de 'last_check' bijwerken en stoppen
    if ($now - $lastPurge < $dueSeconds) {
        if ($now - $lastCheck >= $checkWindow) {
            $new = json_encode(['last' => $lastPurge, 'last_check' => $now], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $up = $pdo->prepare("INSERT INTO app_state (k, v) VALUES ('hk_purge', :v)
                                  ON DUPLICATE KEY UPDATE v=VALUES(v), updated_at=CURRENT_TIMESTAMP");
            $up->execute([':v' => $new]);
        }
        return ['skipped' => true, 'reason' => 'not_due'];
    }

    // Aan de beurt: hele log leegmaken
    $countBefore = (int)$pdo->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
    $purged = 0;

    try {
        // Snelste pad: TRUNCATE (reset ook AUTO_INCREMENT)
        $pdo->exec("TRUNCATE TABLE activity_log");
        $purged = $countBefore;
    } catch (Throwable $t) {
        // Geen TRUNCATE-rechten? Dan in batches verwijderen
        do {
            $deleted = (int)$pdo->exec("DELETE FROM activity_log LIMIT " . (int)HK_DELETE_BATCH);
            $purged += $deleted;
        } while ($deleted === HK_DELETE_BATCH && $deleted > 0);
    }

    // State bijwerken
    $new = json_encode(['last' => $now, 'last_check' => $now, 'purged' => $purged], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $up = $pdo->prepare("INSERT INTO app_state (k, v) VALUES ('hk_purge', :v)
                          ON DUPLICATE KEY UPDATE v=VALUES(v), updated_at=CURRENT_TIMESTAMP");
    $up->execute([':v' => $new]);

    return ['skipped' => false, 'purged' => $purged];
}
