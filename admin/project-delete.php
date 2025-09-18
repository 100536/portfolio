<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';

try {
    csrf_require($_POST['csrf'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); exit('bad id'); }

    $pdo->beginTransaction();
    $del = $pdo->prepare("DELETE FROM contact_messages WHERE id=:id");
    $del->execute([':id'=>$id]);

    log_event($pdo, $_SESSION['user_id']??null, $_SESSION['username']??null, 'delete', 'contact', $id, []);

    $pdo->commit();
    header('Location: ./contacts.php?deleted=1');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('contact-delete failed: '.$e->getMessage());
    header('Location: ./contacts.php?err=delete');
}
