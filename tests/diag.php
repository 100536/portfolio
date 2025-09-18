<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

echo "PHP: " . PHP_VERSION . PHP_EOL;

$cfg = __DIR__ . '/../includes/config.php';
echo "config.php: " . (is_file($cfg) ? "gevonden" : "NIET gevonden") . " => $cfg" . PHP_EOL;

require_once $cfg;

echo "pdo_mysql geladen: " . (extension_loaded('pdo_mysql') ? 'ja' : 'nee') . PHP_EOL;

try {
    $ok = $pdo->query("SELECT 1")->fetchColumn();
    echo "DB connectie: OK (SELECT 1 = $ok)" . PHP_EOL;
} catch (Throwable $e) {
    echo "DB fout: " . $e->getMessage() . PHP_EOL;
}

$up = __DIR__ . '/../uploads/projects';
echo "uploads/projects bestaat: " . (is_dir($up) ? 'ja' : 'nee') . " => $up" . PHP_EOL;
echo "uploads/projects schrijfbaar: " . (is_writable($up) ? 'ja' : 'nee') . PHP_EOL;
