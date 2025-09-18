<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
if ($userId || $username) {
    log_event($pdo, $userId ? (int)$userId : null, $username ? (string)$username : null, 'logout', 'user', (int)($userId ?? 0), [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
}

// sessie beëindigen
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'] ?? true, $params['httponly'] ?? true);
}
session_destroy();

// terug naar login
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
header("Location: {$base}/main/login.php");
exit;
