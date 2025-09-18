<?php

declare(strict_types=1);

/*
  Ontvangt beacons van de browser en logt pageviews.
  Verwacht JSON: { path: "/main/project.php", ref: "…" } (ref optioneel)
*/

require_once __DIR__ . '/../includes/config.php'; // $pdo, e()

// CORS/headers (alleen eigen site gebruikt dit, dus strak houden)
header('Content-Type: application/json; charset=UTF-8');
header('Referrer-Policy: no-referrer');

// Sessie-cookie voor “unieke bezoekers”
$cookieName = 'pv_sid';
if (empty($_COOKIE[$cookieName]) || !preg_match('/^[a-f0-9]{32}$/i', $_COOKIE[$cookieName])) {
    $sid = bin2hex(random_bytes(16)); // 32 hex
    setcookie($cookieName, $sid, [
        'expires' => time() + 60 * 60 * 24 * 180, // 180 dagen
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => false, // js mag lezen is ok
        'samesite' => 'Lax',
    ]);
    $_COOKIE[$cookieName] = $sid;
}
$sid = (string)$_COOKIE[$cookieName];

// Alleen POST/beacon accepteren
$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
$path = isset($data['path']) ? substr((string)$data['path'], 0, 255) : '';
$ref = isset($data['ref']) ? substr((string)$data['ref'], 0, 255) : '';

if ($path === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'err' => 'no-path']);
    exit;
}

// Dedup: dezelfde sessie + path binnen 30 minuten overslaan
$chk = $pdo->prepare("
  SELECT 1 FROM page_views
  WHERE session_id=:s AND path=:p AND occurred_at >= (NOW() - INTERVAL 30 MINUTE)
  LIMIT 1
");
$chk->execute([':s' => $sid, ':p' => $path]);
$exists = (bool)$chk->fetchColumn();

if (!$exists) {
    $ins = $pdo->prepare("INSERT INTO page_views (path, session_id, ref) VALUES (:p, :s, :r)");
    $ins->execute([':p' => $path, ':s' => $sid, ':r' => $ref ?: null]);
}

echo json_encode(['ok' => true]);
