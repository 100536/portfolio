<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

const MAX_ATTEMPTS = 5;
const LOCK_SECONDS = 120;

$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); // bv. /portfolio

$now = time();
if (!isset($_SESSION['auth'])) $_SESSION['auth'] = ['attempts'=>0,'until'=>0];

if ($_SESSION['auth']['until'] > $now) {
    header("Location: {$base}/main/login.php?err=locked");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base}/main/login.php");
    exit;
}

$csrf = $_POST['csrf'] ?? '';
if (!csrf_verify($csrf)) {
    header("Location: {$base}/main/login.php?err=csrf");
    exit;
}

$user = trim((string)($_POST['user'] ?? ''));
$pass = (string)($_POST['password'] ?? '');
$next = isset($_POST['next']) ? (string)$_POST['next'] : '';

if ($user === '' || $pass === '') {
    header("Location: {$base}/main/login.php?err=bad");
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, password_hash, role, is_active FROM users WHERE LOWER(username)=LOWER(:u) LIMIT 1");
$stmt->execute([':u' => $user]);
$row = $stmt->fetch();

$ok = false;
if ($row && (int)$row['is_active'] === 1) {
    $ok = password_verify($pass, (string)$row['password_hash']);
}

if (!$ok) {
    $_SESSION['auth']['attempts'] = (int)$_SESSION['auth']['attempts'] + 1;
    if ($_SESSION['auth']['attempts'] >= MAX_ATTEMPTS) {
        $_SESSION['auth']['attempts'] = 0;
        $_SESSION['auth']['until'] = $now + LOCK_SECONDS;
        header("Location: {$base}/main/login.php?err=locked");
    } else {
        header("Location: {$base}/main/login.php?err=bad");
    }
    exit;
}

// success
$_SESSION['auth'] = ['attempts'=>0, 'until'=>0];
$_SESSION['user_id'] = (int)$row['id'];
$_SESSION['username'] = (string)$row['username'];
$_SESSION['role'] = (string)$row['role'];
$pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([ (int)$row['id'] ]);
session_regenerate_id(true);

// LOG: login
log_event(
    $pdo,
    (int)$_SESSION['user_id'],
    (string)$_SESSION['username'],
    'login',
    'user',
    (int)$_SESSION['user_id'],
    ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']
);

$target = $next !== '' ? $next : "{$base}/admin/dashboard.php";
header("Location: " . $target);
exit;
