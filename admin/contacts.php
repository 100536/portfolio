<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$token = csrf_token();

// Filters
$q      = trim((string)($_GET['q'] ?? ''));
$status = (string)($_GET['status'] ?? 'all');
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 20;
$off    = ($page - 1) * $per;

$where = [];
$args  = [];
if ($q !== '') { $where[]="(name LIKE :q OR email LIKE :q OR subject LIKE :q)"; $args[':q']="%{$q}%"; }
if (in_array($status,['open','read','archived'],true)) { $where[]="status=:status"; $args[':status']=$status; }
$whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$cnt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $whereSql");
$cnt->execute($args);
$total = (int)$cnt->fetchColumn();
$pages = max(1,(int)ceil($total/$per));

$sql = "SELECT id,name,email,subject,status,created_at FROM contact_messages $whereSql ORDER BY created_at DESC LIMIT :lim OFFSET :off";
$st  = $pdo->prepare($sql);
foreach ($args as $k=>$v) $st->bindValue($k,$v);
$st->bindValue(':lim',$per,PDO::PARAM_INT);
$st->bindValue(':off',$off,PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$openCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='open'")->fetchColumn();
$readCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='read'")->fetchColumn();
$archCount = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='archived'")->fetchColumn();

$currentUser = $_SESSION['username'] ?? 'Gebruiker';
function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES); }
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact — Portfolio Admin</title>
    <link rel="stylesheet" href="../css/admin.css" />
</head>
<body>

<aside class="sidebar" aria-label="Zijbalk navigatie">
    <div class="sidebar__brand"><svg width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2l7 4v12l-7 4-7-4V6l7-4z"/></svg><span>Portfolio Admin</span></div>
    <nav class="sidebar__nav">
        <a href="./dashboard.php#dashboard" class="nav-link"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg><span>Dashboard</span></a>
        <a href="./admin.php" class="nav-link"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg><span>Projecten</span></a>
        <a href="./contacts.php" class="nav-link is-active"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M21 8v10a2 2 0 0 1-2 2H5l-4 4V6a2 2 0 0 1 2-2h12"/></svg><span>Contact</span></a>
        <a href="../auth/logout.php" class="nav-link nav-link--danger"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M10 17l1.41-1.41L8.83 13H21v-2H8.83l2.58-2.59L10 7l-5 5 5 5zM4 19h6v2H4a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h6v2H4v14z"/></svg><span>Uitloggen</span></a>
    </nav>
    <div class="sidebar__footer"><small>© Lucas Askamp</small></div>
</aside>

<header class="topbar">
    <button class="icon-btn" id="toggleSidebar" aria-label="Zijbalk openen/sluiten"><span class="icon-bars"></span></button>
    <h1 class="topbar__title">Contactberichten</h1>
    <div class="topbar__user"><div class="avatar">LA</div><span class="topbar__user-name"><?= e($currentUser) ?></span></div>
</header>

<main class="main">
    <section class="section">
        <h2>Contact</h2>

        <div class="card" style="padding:16px">
            <div class="actions" style="justify-content:space-between; gap:12px; flex-wrap:wrap">
                <!-- Mooie metrics -->
                <div class="metrics">
                    <div class="metric-chip metric--warn">
                        <span class="metric-chip__dot"></span>
                        <span class="metric-chip__label">Open</span>
                        <span class="metric-chip__value"><?= (int)$openCount ?></span>
                    </div>
                    <div class="metric-chip metric--ok">
                        <span class="metric-chip__dot"></span>
                        <span class="metric-chip__label">Gelezen</span>
                        <span class="metric-chip__value"><?= (int)$readCount ?></span>
                    </div>
                    <div class="metric-chip metric--info">
                        <span class="metric-chip__dot"></span>
                        <span class="metric-chip__label">Gearchiveerd</span>
                        <span class="metric-chip__value"><?= (int)$archCount ?></span>
                    </div>
                    <div class="metric-chip">
                        <span class="metric-chip__dot"></span>
                        <span class="metric-chip__label">Totaal</span>
                        <span class="metric-chip__value"><?= (int)($openCount+$readCount+$archCount) ?></span>
                    </div>
                </div>

                <!-- Filter -->
                <form class="filter-bar" method="get" action="./contacts.php" style="margin-left:auto">
                    <div class="filter-field"><input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Zoek naam, e-mail, onderwerp" /></div>
                    <div class="filter-field" style="flex:0 0 160px">
                        <select class="select" name="status">
                            <option value="all" <?= $status==='all'?'selected':'' ?>>Alle</option>
                            <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
                            <option value="read" <?= $status==='read'?'selected':'' ?>>Gelezen</option>
                            <option value="archived" <?= $status==='archived'?'selected':'' ?>>Gearchiveerd</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-primary" type="submit">Filter</button>
                        <a class="btn" href="./contacts.php">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Naam</th><th>E-mail</th><th>Onderwerp</th><th>Ontvangen</th><th>Status</th><th>Acties</th></tr></thead>
                    <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="6">Geen resultaten.</td></tr>
                    <?php else: foreach ($rows as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['email']) ?></td>
                            <td><?= e($c['subject']) ?></td>
                            <td><?= e(date('d-m-Y H:i', strtotime((string)$c['created_at']))) ?></td>
                            <td><span class="pill <?= $c['status']==='open'?'pill--warn':($c['status']==='read'?'pill--ok':'') ?>"><?= e($c['status']) ?></span></td>
                            <td class="actions-col">
                                <a class="btn btn-ghost btn-sm" href="./contact-view.php?id=<?= (int)$c['id'] ?>">Bekijken</a>
                                <form action="./contact-toggle.php" method="post" style="display:inline">
                                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <input type="hidden" name="to" value="<?= $c['status']==='open' ? 'read' : 'open' ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit"><?= $c['status']==='open' ? 'Markeer als gelezen' : 'Terug naar open' ?></button>
                                </form>
                                <form action="./contact-delete.php" method="post" style="display:inline" onsubmit="return confirm('Verwijderen?');">
                                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages>1): ?>
                <div class="actions" style="justify-content:flex-end; padding:12px 16px">
                    <?php for ($p=1;$p<=$pages;$p++): $qs=http_build_query(['q'=>$q,'status'=>$status,'page'=>$p]); ?>
                        <a class="btn btn-sm<?= $p===$page?' btn-primary':'' ?>" href="?<?= $qs ?>"><?= $p ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script src="../javascript/admin.js"></script>
</body>
</html>
