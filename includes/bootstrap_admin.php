<?php

declare(strict_types=1);

// Login verplicht
require_once __DIR__ . '/../auth/guard.php';

// DB + housekeeping
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/housekeeping.php';

// Stilletjes opruimen als het “due” is
try {
    hk_maybe($pdo);
} catch (Throwable $e) { /* stil */
}
