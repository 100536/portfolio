<?php

declare(strict_types=1);

// Veilige sessie-instellingen
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name('portfolio_sess');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',      // laat leeg
    'secure' => $secure, // true op HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
