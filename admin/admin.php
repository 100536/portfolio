<?php
declare(strict_types=1);

// Login verplicht
require_once __DIR__ . '/../auth/guard.php';

// DB + helpers + CSRF
require_once __DIR__ . '/../includes/config.php'; // bevat $pdo en e()
require_once __DIR__ . '/../includes/csrf.php';

$token  = csrf_token();
$base   = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); // bv. /portfolio

// Filters
$q       = trim((string)($_GET['q'] ?? ''));
$statusF = $_GET['status'] ?? 'all';
$allowed = ['all','live','concept'];
if (!in_array($statusF, $allowed, true)) { $statusF = 'all'; }

// Query
$sql = "SELECT id, title, status, updated_at, live_url FROM projects";
$where = [];
$params = [];

if ($statusF !== 'all') {
    $where[] = "status = :status";
    $params[':status'] = $statusF;
}
if ($q !== '') {
    $where[] = "(title LIKE :kw OR live_url LIKE :kw)";
    $params[':kw'] = "%{$q}%";
}
if ($where) { $sql .= " WHERE " . implode(" AND ", $where); }
$sql .= " ORDER BY updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kleine helper
function pill(string $status): string {
    $class = $status === 'live' ? 'pill pill--ok' : 'pill pill--warn';
    return '<span class="'.$class.'">'.htmlspecialchars(ucfirst($status), ENT_QUOTES).'</span>';
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Projecten — Portfolio Admin</title>
    <link rel="stylesheet" href="../css/admin.css" />
</head>
<body>

<!-- Zijbalk -->
<aside class="sidebar" aria-label="Zijbalk navigatie">
    <div class="sidebar__brand">
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M12 2l7 4v12l-7 4-7-4V6l7-4zm0 2.3L7 6.8v10.4l5 2.5 5-2.5V6.8l-5-2.5z"/>
        </svg>
        <span>Portfolio Admin</span>
    </div>

    <nav class="sidebar__nav">
        <a href="./dashboard.php" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="./admin.php" class="nav-link is-active">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg>
            <span>Projecten</span>
        </a>
        <a href="./contacts.php" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 8v10a2 2 0 0 1-2 2H5l-4 4V6a2 2 0 0 1 2-2h12"/></svg>
            <span>Contact</span>
        </a>
        <a href="../auth/logout.php" class="nav-link nav-link--danger">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 17l1.41-1.41L8.83 13H21v-2H8.83l2.58-2.59L10 7l-5 5 5 5zM4 19h6v2H4a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h6v2H4v14z"/></svg>
            <span>Uitloggen</span>
        </a>
    </nav>

    <div class="sidebar__footer">
        <small>© Lucas Askamp</small>
    </div>
</aside>

<!-- Bovenbalk -->
<header class="topbar">
    <button class="icon-btn" id="toggleSidebar" aria-label="Zijbalk openen/sluiten" aria-controls="sidebar">
        <span class="icon-bars"></span>
    </button>
    <h1 class="topbar__title">Projecten</h1>
</header>

<!-- Inhoud -->
<main class="main">
    <section class="section">
        <div class="card">
            <div class="table-toolbar projects-toolbar">
                <form method="get" class="filter-bar" role="search">
                    <div class="filter-field">
                        <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Zoek project…" />
                    </div>
                    <div class="filter-field" style="max-width:200px">
                        <select class="select" name="status">
                            <option value="all"     <?= $statusF==='all'?'selected':'' ?>>Alle</option>
                            <option value="live"    <?= $statusF==='live'?'selected':'' ?>>Live</option>
                            <option value="concept" <?= $statusF==='concept'?'selected':'' ?>>Concept</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <?php if ($q !== '' || $statusF !== 'all'): ?>
                            <a class="btn btn-ghost" href="./admin.php">Reset</a>
                        <?php endif; ?>
                        <a class="btn btn-primary" href="./project-new.php">Project toevoegen</a>
                    </div>
                </form>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Status</th>
                        <th>Laatst bijgewerkt</th>
                        <th>Acties</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="4">Geen projecten gevonden.</td></tr>
                    <?php else: foreach ($rows as $p): ?>
                        <tr>
                            <td><?= e($p['title']) ?></td>
                            <td><?= pill((string)$p['status']) ?></td>
                            <td><?= e(date('d-m-Y', strtotime((string)$p['updated_at']))) ?></td>
                            <td class="actions-col">
                                <a class="btn btn-ghost btn-sm" href="./project-edit.php?id=<?= (int)$p['id'] ?>">Bewerken</a>

                                <!-- Publish / Depublish -->
                                <form action="./project-toggle.php" method="post" style="display:inline">
                                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="to" value="<?= $p['status']==='live' ? 'concept' : 'live' ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">
                                        <?= $p['status']==='live' ? 'Depubliceren' : 'Publiceren' ?>
                                    </button>
                                </form>

                                <?php if (!empty($p['live_url'])): ?>
                                    <a class="btn btn-ghost btn-sm" href="<?= e((string)$p['live_url']) ?>" target="_blank" rel="noopener">Bekijken</a>
                                <?php endif; ?>

                                <form action="./project-delete.php" method="post" style="display:inline" onsubmit="return confirm('Project definitief verwijderen?');">
                                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>

<script src="../javascript/admin.js"></script>
</body>
</html>
