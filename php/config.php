<?php
/**
 * Mailconfiguratie
 * - SMTP_USE: true = gebruik PHPMailer + SMTP, false = gebruik mail()
 * - Vul SMTP-gegevens in als je SMTP wilt gebruiken
 */

return [
    'SMTP_USE'   => false,              // zet op true als je SMTP via PHPMailer wilt gebruiken
    'SMTP_HOST'  => 'smtp.example.com',
    'SMTP_PORT'  => 587,
    'SMTP_USER'  => 'no-reply@example.com',
    'SMTP_PASS'  => '***************',
    'SMTP_SECURE'=> 'tls',              // 'tls' of 'ssl'

    // Van/naar
    'MAIL_FROM'  => 'no-reply@example.com',   // gebruik een adres van je eigen domein (DMARC/SPF)
    'MAIL_FROM_NAME' => 'Portfolio Website',
    'MAIL_TO'    => 'lucas.werk@gmail.com',   // ontvangstadres
];
