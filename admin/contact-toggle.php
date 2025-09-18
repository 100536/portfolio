<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/activity.php';

session_write_close();

function back(string $suffix=''): never {
    $to = $_SERVER['HTTP_REFERER'] ?? './contacts.php';
    if ($suffix !== '') $to .= (str_contains($to,'?')?'&':'?').$suffix;
    header('Location: '.$to);
    exit;
}

// Tolerante CSRF: als er een token is moet hij kloppen, maar geen 500
$postCsrf = (string)($_POST['csrf'] ?? '');
if (isset($_SESSION['csrf']) && !hash_equals((string)$_SESSION['csrf'], $postCsrf)) {
    error_log('contact-toggle: CSRF mismatch');
    back('err=csrf');
}

$id = (int)($_POST['id'] ?? 0);
$to = (string)($_POST['to'] ?? 'open');
$allowed = ['open','read','archived'];
if (!in_array($to,$allowed,true)) $to='open';
if ($id<=0) back('err=badid');

try{
    $cur = $pdo->prepare("SELECT status FROM contact_messages WHERE id=:id LIMIT 1");
    $cur->execute([':id'=>$id]);
    $from = $cur->fetchColumn();
    if ($from===false) back('err=notfound');

    $upd = $pdo->prepare("UPDATE contact_messages SET status=:to WHERE id=:id LIMIT 1");
    $upd->execute([':to'=>$to, ':id'=>$id]);

    log_event($pdo, $_SESSION['user_id']??null, $_SESSION['username']??null, 'toggle', 'contact', $id, ['from'=>(string)$from,'to'=>$to]);

    back('ok=toggle');
} catch(Throwable $e){
    error_log('contact-toggle failed: '.$e->getMessage());
    back('err=toggle');
}
