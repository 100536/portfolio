<?php
declare(strict_types=1);
require __DIR__ . '/includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Bad request'); }

$stmt = $pdo->prepare("
  SELECT pi.mime, pi.size, pi.data, pi.updated_at
  FROM project_images pi
  WHERE pi.project_id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) { http_response_code(404); exit('Not found'); }

$ts = strtotime($row['updated_at'] ?? 'now');
$lastModified = gmdate('D, d M Y H:i:s', $ts) . ' GMT';
header('Last-Modified: ' . $lastModified);
header('Cache-Control: public, max-age=86400, immutable');
header('Content-Type: ' . $row['mime']);
header('Content-Length: ' . (string)$row['size']);

echo $row['data'];
