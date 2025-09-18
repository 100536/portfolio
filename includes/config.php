<?php
declare(strict_types=1);

// 1) Probeer geheime lokale config te laden (staat in .gitignore)
$local = __DIR__ . '/config.local.php';
if (is_file($local)) {
    require $local; // zet $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME
} else {
    // Fallback via ENV (optioneel)
    $DB_HOST = getenv('DB_HOST') ?: 'localhost';
    $DB_USER = getenv('DB_USER') ?: '';
    $DB_PASS = getenv('DB_PASS') ?: '';
    $DB_NAME = getenv('DB_NAME') ?: 'portifolio';
}

if (empty($DB_USER)) {
    http_response_code(500);
    exit('Database-config ontbreekt. Zet includes/config.local.php neer of ENV vars.');
}

// 2) PDO
$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// 3) Helpers
if (!function_exists('e')) {
    function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('slugify')) {
    function slugify(string $t): string {
        $t = strtolower(trim($t));
        $t = preg_replace('~[^\pL\d]+~u', '-', $t);
        $t = preg_replace('~^-+|-+$~', '', $t);
        return $t !== '' ? $t : uniqid('p-', true);
    }
}
