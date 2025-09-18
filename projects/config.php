<?php
// config.php — PDO
$DB_HOST = 'localhost';
$DB_USER = 'jouw_db_user';
$DB_PASS = 'jouw_db_wachtwoord';
$DB_NAME = 'jouw_db_naam';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    exit('Database-verbinding mislukt: ' . $e->getMessage());
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~^-+|-+$~', '', $text);
    return $text ?: uniqid('p-');
}
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
