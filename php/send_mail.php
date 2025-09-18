<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/../includes/config.php';     // $pdo
require_once __DIR__ . '/../includes/activity.php';   // log_event()

function redirect(string $to): void { header('Location: '.$to); exit; }

$backOk  = '../main/contact.php?sent=1';
$backBad = '../main/contact.php?err=input';
$backSrv = '../main/contact.php?err=server';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }

    // Honeypot
    if (!empty($_POST['website'] ?? '')) {
        redirect($backOk);
    }

    // Input
    $name    = trim((string)($_POST['name'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    // Validatie volgens jouw kolomnamen/lengtes
    if ($name === '' || mb_strlen($name) > 120)        redirect($backBad);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))     redirect($backBad);
    if (mb_strlen($email) > 190)                        redirect($backBad);
    if ($subject === '' || mb_strlen($subject) > 150)   redirect($backBad);
    if ($message === '' || mb_strlen($message) > 5000)  redirect($backBad);

    // Client info
    $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $ipRaw = null;
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $packed = @inet_pton($_SERVER['REMOTE_ADDR']);
        if ($packed !== false) $ipRaw = $packed; // binair voor VARBINARY(16)
    }

    // INSERT (let op: géén updated_at)
    $sql = "INSERT INTO contact_messages
              (name, email, subject, message, status, created_at, ip, user_agent)
            VALUES
              (:n, :e, :s, :m, 'open', NOW(), :ip, :ua)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':n',  $name);
    $stmt->bindValue(':e',  $email);
    $stmt->bindValue(':s',  $subject);
    $stmt->bindValue(':m',  $message);
    if ($ipRaw === null) {
        $stmt->bindValue(':ip', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':ip', $ipRaw, PDO::PARAM_LOB);
    }
    $stmt->bindValue(':ua', $ua);
    $stmt->execute();

    $id = (int)$pdo->lastInsertId();

    // Log create
    log_event(
        $pdo,
        $_SESSION['user_id']  ?? null,
        $_SESSION['username'] ?? null,
        'create',
        'contact',
        $id,
        ['name'=>$name,'email'=>$email,'subject'=>$subject]
    );

    // (optioneel) mail naar jezelf; fouten negeren
    if (function_exists('mail')) {
        $to   = 'lucas.werk@gmail.com';
        $host = $_SERVER['HTTP_HOST'] ?? 'site';
        $hdrs = "From: no-reply@{$host}\r\n".
            "Reply-To: {$email}\r\n".
            "Content-Type: text/plain; charset=UTF-8\r\n";
        $body = "Nieuw bericht via het contactformulier:\n\n".
            "Naam: {$name}\nE-mail: {$email}\nOnderwerp: {$subject}\n\n".
            "Bericht:\n{$message}\n";
        @mail($to, "[Portfolio] {$subject}", $body, $hdrs);
    }

    redirect($backOk);

} catch (Throwable $e) {
    error_log('send_mail.php error: '.$e->getMessage());
    redirect($backSrv);
}
