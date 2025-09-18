<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';

try {
    csrf_require($_POST['csrf'] ?? '');

    $id = (int)($_POST['id'] ?? 0);
    $to = (string)($_POST['to'] ?? 'open');
    $allowed = ['open','read','archived'];
    if (!in_array($to, $allowed, true)) $to = 'open';
    if ($id <= 0) { http_response_code(400); exit('bad id'); }

    $cur = $pdo->prepare("SELECT status FROM contact_messages WHERE id=:id");
    $cur->execute([':id'=>$id]);
    $from = $cur->fetchColumn();
    if ($from === false) { http_response_code(404); exit('not found'); }

    $upd = $pdo->prepare("UPDATE contact_messages SET status=:to WHERE id=:id");
    $upd->execute([':to'=>$to, ':id'=>$id]);

    log_event($pdo, $_SESSION['user_id']??null, $_SESSION['username']??null, 'toggle', 'contact', $id, ['from'=>(string)$from,'to'=>$to]);

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? './contacts.php'));
} catch (Throwable $e) {
    error_log('contact-toggle failed: '.$e->getMessage());
    header('Location: ./contacts.php?err=toggle');
}
