<?php require __DIR__ . '/../includes/config.php'; ?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Project toevoegen</title>
    <link rel="stylesheet" href="../css/site.css" />
</head>
<body>
<main class="container" style="max-width:900px;margin:32px auto;padding:0 16px">
    <h1>Project toevoegen</h1>
    <form action="project-store.php" method="post" enctype="multipart/form-data">
        <?php $mode='new'; $data=[]; include __DIR__ . '/project-form.inc.php'; ?>
        <div class="actions">
            <button class="btn" type="submit">Opslaan</button>
            <a class="btn ghost" href="admin.php">Annuleren</a>
        </div>
    </form>
</main>
</body>
</html>
