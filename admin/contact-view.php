<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/activity.php';

function e($s){ return htmlspecialchars((string)$s,ENT_QUOTES); }

$token = csrf_token();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); exit('Bad id'); }

$st = $pdo->prepare("SELECT id,name,email,subject,message,status,created_at,ip,user_agent FROM contact_messages WHERE id=:id");
$st->execute([':id'=>$id]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); exit('Not found'); }

try { log_event($pdo, $_SESSION['user_id']??null, $_SESSION['username']??null, 'view', 'contact', $id, []); } catch(Throwable $e){}

$ip = '';
if (!empty($row['ip'])) $ip = @inet_ntop($row['ip']) ?: '';
$ua = (string)($row['user_agent'] ?? '');
$currentUser = $_SESSION['username'] ?? 'Gebruiker';
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact bekijken — Admin</title>
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
    <h1 class="topbar__title">Contactbericht #<?= (int)$row['id'] ?></h1>
    <div class="topbar__user"><div class="avatar">LA</div><span class="topbar__user-name"><?= e($currentUser) ?></span></div>
</header>

<main class="main">
    <section class="section">
        <div class="grid two">
            <article class="card">
                <header class="card__header"><h3>Details</h3></header>
                <div class="card__body">
                    <div class="list">
                        <div><strong>Naam:</strong> <?= e($row['name']) ?></div>
                        <div><strong>E-mail:</strong> <a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a></div>
                        <div><strong>Onderwerp:</strong> <?= e($row['subject']) ?></div>
                        <div><strong>Ontvangen:</strong> <?= e(date('d-m-Y H:i', strtotime((string)$row['created_at']))) ?></div>
                        <div><strong>Status:</strong> <span class="pill <?= $row['status']==='open'?'pill--warn':($row['status']==='read'?'pill--ok':'') ?>"><?= e($row['status']) ?></span></div>
                        <?php if ($ip): ?><div><strong>IP:</strong> <?= e($ip) ?></div><?php endif; ?>
                        <?php if ($ua): ?><div><strong>User-Agent:</strong> <span class="muted"><?= e($ua) ?></span></div><?php endif; ?>
                    </div>
                </div>
            </article>

            <article class="card">
                <header class="card__header"><h3>Acties</h3></header>
                <div class="card__body actions">
                    <form action="./contact-toggle.php" method="post">
                        <input type="hidden" name="csrf" value="<?= e($token) ?>">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <input type="hidden" name="to" value="<?= $row['status']==='open' ? 'read' : 'open' ?>">
                        <button class="btn btn-primary" type="submit"><?= $row['status']==='open' ? 'Markeer als gelezen' : 'Terug naar open' ?></button>
                    </form>

                    <?php if ($row['status']!=='archived'): ?>
                        <form action="./contact-toggle.php" method="post">
                            <input type="hidden" name="csrf" value="<?= e($token) ?>">
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <input type="hidden" name="to" value="archived">
                            <button class="btn" type="submit">Archiveer</button>
                        </form>
                    <?php endif; ?>

                    <form action="./contact-delete.php" method="post" onsubmit="return confirm('Verwijderen?');">
                        <input type="hidden" name="csrf" value="<?= e($token) ?>">
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button class="btn btn-ghost" type="submit">Verwijderen</button>
                    </form>
                </div>
            </article>
        </div>

        <article class="card">
            <header class="card__header"><h3>Bericht</h3></header>
            <div class="card__body"><pre style="white-space:pre-wrap; margin:0; font:inherit; color:#cfdbff;"><?= e($row['message']) ?></pre></div>
        </article>
    </section>
</main>

<script src="../javascript/admin.js"></script>
</body>
</html>
