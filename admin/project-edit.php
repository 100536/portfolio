<?php
require __DIR__ . '/../includes/config.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) exit('Geen id.');

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) exit('Niet gevonden.');
?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Project bewerken</title>
    <link rel="stylesheet" href="../css/site.css" />
</head>
<body>
<main class="container" style="max-width:900px;margin:32px auto;padding:0 16px">
    <h1>Project bewerken</h1>
    <form action="project-update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
        <?php $mode='edit'; include __DIR__ . '/project-form.inc.php'; ?>
        <div class="actions">
            <button class="btn" type="submit">Opslaan</button>
            <a class="btn ghost" href="admin.php">Terug</a>
        </div>
    </form>
</main>
</body>
</html>
