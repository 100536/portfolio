<?php
declare(strict_types=1);
require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/log.php';

$title   = trim($_POST['title'] ?? '');
$excerpt = trim($_POST['excerpt'] ?? '');
$tech    = trim($_POST['tech'] ?? '');
$live    = trim($_POST['live_url'] ?? '');
$status  = ($_POST['status'] ?? 'concept') === 'live' ? 'live' : 'concept';

if ($title === '') { exit('Titel is verplicht.'); }

$slug = slugify($title);

// 1) Maak project-record
$ins = $pdo->prepare("
  INSERT INTO projects (title, slug, excerpt, tech, live_url, status, hero_image)
  VALUES (:title, :slug, :excerpt, :tech, :live, :status, NULL)
");
$ins->execute([
    ':title'=>$title, ':slug'=>$slug, ':excerpt'=>$excerpt,
    ':tech'=>$tech, ':live'=>$live, ':status'=>$status
]);

$projectId = (int)$pdo->lastInsertId();

// 2) Optioneel: upload als BLOB opslaan
if (!empty($_FILES['hero']['name'])) {
    $f = $_FILES['hero'];
    if ($f['error'] === UPLOAD_ERR_OK) {
        if ($f['size'] > 3*1024*1024) exit('Bestand te groot (max 3MB).');

        $mime = function_exists('mime_content_type')
            ? mime_content_type($f['tmp_name'])
            : (getimagesize($f['tmp_name'])['mime'] ?? null);

        $allowed = ['image/jpeg','image/png','image/webp'];
        if (!in_array($mime, $allowed, true)) exit('Alleen jpg/png/webp toegestaan.');

        $data = file_get_contents($f['tmp_name']);
        if ($data === false) exit('Upload lezen mislukt.');

        $pdo->prepare("
          INSERT INTO project_images (project_id, mime, size, data)
          VALUES (:pid, :mime, :size, :data)
        ")->execute([
            ':pid'  => $projectId,
            ':mime' => $mime,
            ':size' => filesize($f['tmp_name']),
            ':data' => $data,
        ]);
    } else {
        exit('Upload error: ' . $f['error']);
    }
}

// LOG: create
log_event($pdo, $_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, 'create', 'project', (int)$projectId, [
    'title' => $title,
    'status'=> $status
]);

header('Location: admin.php');
