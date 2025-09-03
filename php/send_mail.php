<?php
declare(strict_types=1);
session_start();

// --- pad naar config ---
$config = require __DIR__ . '/config.php';

// --- eenvoudige rate limit: max 1 mail per 60s per sessie ---
if (!empty($_SESSION['last_mail_time']) && time() - $_SESSION['last_mail_time'] < 60) {
    header('Location: ../html/contact.html?status=error'); exit;
}

// --- alleen POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/contact.html?status=error'); exit;
}

// --- honeypot (bots vullen dit) ---
if (!empty($_POST['website'] ?? '')) {
    header('Location: ../html/contact.html?status=ok'); exit; // stilletjes negeren
}

// --- helper: schoon input en blokkeer header injectie ---
function clean(string $v): string {
    $v = trim($v);
    $v = str_replace(["\r", "\n", "%0a", "%0d"], '', $v); // header-injectie voorkomen
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$name    = clean($_POST['name']    ?? '');
$email   = clean($_POST['email']   ?? '');
$subject = clean($_POST['subject'] ?? '');
$message = trim($_POST['message']  ?? '');

// validatie
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    header('Location: ../html/contact.html?status=error'); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../html/contact.html?status=error'); exit;
}

// mailinhoud
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ua      = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$bodyTxt = "Nieuw bericht via portfolio:\n\n".
           "Naam: {$name}\n".
           "E-mail: {$email}\n".
           "Onderwerp: {$subject}\n".
           "Bericht:\n{$message}\n\n".
           "-----\nIP: {$ip}\nUA: {$ua}\n";

// --- kies verzendmethode ---
$ok = false;

if (!empty($config['SMTP_USE'])) {
    // --- SMTP via PHPMailer ---
    // Voorwaarden: composer + PHPMailer geïnstalleerd
    // composer require phpmailer/phpmailer
    require_once __DIR__ . '/vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['SMTP_USER'];
        $mail->Password   = $config['SMTP_PASS'];
        $mail->SMTPSecure = $config['SMTP_SECURE'] ?? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$config['SMTP_PORT'];

        $mail->setFrom($config['MAIL_FROM'], $config['MAIL_FROM_NAME'] ?? 'Website');
        $mail->addAddress($config['MAIL_TO']);

        // als reply-to van afzender, zodat je direct kunt beantwoorden
        $mail->addReplyTo($email, $name);

        $mail->Subject = $subject;
        $mail->Body    = $bodyTxt;

        $ok = $mail->send();
    } catch (Throwable $e) {
        $ok = false;
        // log eventueel: error_log('MAILERR: '.$e->getMessage());
    }
} else {
    // --- Eenvoudige mail() ---
    $to      = $config['MAIL_TO'];
    $from    = $config['MAIL_FROM'];
    $fromName= $config['MAIL_FROM_NAME'] ?? 'Website';

    $headers = [];
    $headers[] = 'From: '.$fromName.' <'.$from.'>';
    $headers[] = 'Reply-To: '.$name.' <'.$email.'>';
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headersStr = implode("\r\n", $headers);

    $ok = @mail($to, $subject, $bodyTxt, $headersStr);
}

// --- klaar ---
if ($ok) {
    $_SESSION['last_mail_time'] = time();
    header('Location: ../html/contact.html?status=ok'); exit;
} else {
    header('Location: ../html/contact.html?status=error'); exit;
}
