<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';

if (empty($_SESSION['user_id'])) {
    $base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
    $next = $base . $_SERVER['REQUEST_URI'];
    header("Location: {$base}/main/login.php?next=" . rawurlencode($next));
    exit;
}

// Na succesvolle guard: housekeeping laden en draaien
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/housekeeping.php';
try { hk_maybe($pdo); } catch (Throwable $e) { /* stil */ }
