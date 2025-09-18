<?php
declare(strict_types=1);

// Login verplicht
require_once __DIR__ . '/../auth/guard.php';

// DB + helpers
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';

$token = csrf_token();

// (optioneel) housekeeping
if (is_file(__DIR__ . '/../includes/housekeeping.php')) {
    require_once __DIR__ . '/../includes/housekeeping.php';
    try { hk_maybe($pdo); } catch (Throwable $e) { /* stil */ }
}

// Basispad, bv. "/portfolio"
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

// Stats projecten
$liveCount    = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status='live'")->fetchColumn();
$conceptCount = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status='concept'")->fetchColumn();

// Stats contact
$contactTotal = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$contactOpen  = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='open'")->fetchColumn();

// Laatste projecten
$projects = $pdo->query("
  SELECT id, title, status, updated_at, live_url
  FROM projects
  ORDER BY updated_at DESC
  LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);

// Laatste activiteiten (timeline) — nu óók contactacties
$logs = $pdo->query("
  SELECT created_at, username, action, entity, entity_id, meta
  FROM activity_log
  ORDER BY created_at DESC
  LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Laatste contactberichten (voor dashboard)
$contactLatest = $pdo->query("
  SELECT id, name, email, subject, status, created_at
  FROM contact_messages
  ORDER BY created_at DESC
  LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);

// Bezoekers analytics (tellers)
$views7 = (int)$pdo->query("
  SELECT COUNT(*) FROM page_views
  WHERE occurred_at >= (NOW() - INTERVAL 7 DAY)
")->fetchColumn();

$uniq7  = (int)$pdo->query("
  SELECT COUNT(DISTINCT session_id) FROM page_views
  WHERE occurred_at >= (NOW() - INTERVAL 7 DAY)
")->fetchColumn();

// Dagtotalen laatste 14 dagen (grafiek)
$daily = array_fill(0, 14, ['date' => null, 'count' => 0]);
for ($i = 13; $i >= 0; $i--) {
    $d = (new DateTime("-{$i} days"))->format('Y-m-d');
    $daily[13-$i]['date']  = $d;
}
$stmt = $pdo->query("
  SELECT DATE(occurred_at) d, COUNT(*) c
  FROM page_views
  WHERE occurred_at >= (CURDATE() - INTERVAL 13 DAY)
  GROUP BY d
");
$map = [];
foreach ($stmt as $row) { $map[$row['d']] = (int)$row['c']; }
$max = 1;
foreach ($daily as &$row) {
    $row['count'] = $map[$row['date']] ?? 0;
    if ($row['count'] > $max) $max = $row['count'];
}
unset($row);

// Som/avg + nette schaal voor y-as
$sum14 = 0;
foreach ($daily as $r) { $sum14 += (int)$r['count']; }

function nice_ceiling(int $n): int {
    if ($n <= 5) return 5;
    $pow  = (int)floor(log10(max(1,$n)));
    $base = 10 ** $pow;
    $m    = (int)ceil($n / $base);
    $nice = ($m <= 1) ? 1 : (($m <= 2) ? 2 : (($m <= 5) ? 5 : 10));
    return $nice * $base;
}
$yMax   = max(1, nice_ceiling($max));
$ticks  = 4;
$avg    = $sum14 / 14;
$chartH = 140;
$avgH   = (int)round(($avg / $yMax) * $chartH);
$today  = date('Y-m-d');

// Top pagina's (30 dagen)
$topPages = $pdo->query("
  SELECT path, COUNT(*) c
  FROM page_views
  WHERE occurred_at >= (NOW() - INTERVAL 30 DAY)
  GROUP BY path
  ORDER BY c DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Huidige gebruiker
$currentUser = $_SESSION['username'] ?? 'Gebruiker';

// Helpers
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES); }
function status_pill(string $status): string {
    $class = $status === 'live' ? 'pill pill--ok' : 'pill pill--warn';
    return '<span class="'.$class.'">'. htmlspecialchars(ucfirst($status), ENT_QUOTES) .'</span>';
}
?>
<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Portfolio Admin — Lucas Askamp</title>
    <link rel="stylesheet" href="../css/admin.css" />
</head>
<body>

<!-- Zijbalk -->
<aside class="sidebar" aria-label="Zijbalk navigatie">
    <div class="sidebar__brand">
        <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M12 2l7 4v12l-7 4-7-4V6l7-4z"/>
        </svg>
        <span>Portfolio Admin</span>
    </div>

    <nav class="sidebar__nav">
        <a href="#dashboard" class="nav-link is-active">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="#projects" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z"/></svg>
            <span>Projecten</span>
        </a>
        <a href="#media" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2z"/></svg>
            <span>Media</span>
        </a>
        <a href="#contact" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 8v10a2 2 0 0 1-2 2H5l-4 4V6a2 2 0 0 1 2-2h12"/></svg>
            <span>Contact</span>
        </a>

        <!-- Snelle links -->
        <a href="./admin.php" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M3 3h18v2H3V3zm0 6h12v2H3V9zm0 6h18v2H3v-2z"/></svg>
            <span>Open Projectbeheer</span>
        </a>
        <a href="./contacts.php" class="nav-link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M21 8v10a2 2 0 0 1-2 2H5l-4 4V6a2 2 0 0 1 2-2h12"/></svg>
            <span>Open Contact</span>
        </a>

        <a href="../auth/logout.php" class="nav-link nav-link--danger">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M10 17l1.41-1.41L8.83 13H21v-2H8.83l2.58-2.59L10 7l-5 5 5 5zM4 19h6v2H4a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2h6v2H4v14z"/></svg>
            <span>Uitloggen</span>
        </a>
    </nav>

    <div class="sidebar__footer">
        <small>v1.0 • © Lucas Askamp</small>
    </div>
</aside>

<!-- Bovenbalk -->
<header class="topbar">
    <button class="icon-btn" id="toggleSidebar" aria-label="Zijbalk openen/sluiten" aria-controls="sidebar">
        <span class="icon-bars"></span>
    </button>

    <h1 class="topbar__title">Portfolio Beheer</h1>

    <form class="topbar__search" role="search">
        <input type="search" placeholder="Zoek..." aria-label="Zoeken" />
    </form>

    <div class="topbar__user">
        <div class="avatar" aria-hidden="true">LA</div>
        <span class="topbar__user-name"><?= e($currentUser) ?></span>
    </div>
</header>

<!-- Inhoud -->
<main class="main">
    <!-- Dashboard -->
    <section id="dashboard" class="section">
        <h2>Dashboard</h2>

        <div class="grid stats">
            <article class="card stat">
                <div class="stat__label">Projecten live</div>
                <div class="stat__value"><?= (int)$liveCount ?></div>
                <div class="stat__trend <?= $liveCount > 0 ? 'up':'' ?>">Actief gepubliceerd</div>
            </article>
            <article class="card stat">
                <div class="stat__label">In concept</div>
                <div class="stat__value"><?= (int)$conceptCount ?></div>
                <div class="stat__trend">Nog te publiceren</div>
            </article>
            <article class="card stat">
                <div class="stat__label">Paginaweergaven (7d)</div>
                <div class="stat__value"><?= (int)$views7 ?></div>
                <div class="stat__trend">Uniek: <?= (int)$uniq7 ?></div>
            </article>
            <article class="card stat">
                <div class="stat__label">Contactberichten</div>
                <div class="stat__value"><?= (int)$contactTotal ?></div>
                <div class="stat__trend"><?= (int)$contactOpen ?> open</div>
            </article>
        </div>

        <div class="grid two">
            <article class="card">
                <header class="card__header">
                    <h3>Snel acties</h3>
                </header>
                <div class="card__body actions">
                    <a class="btn btn-primary" href="./project-new.php">Nieuw project</a>
                    <a class="btn btn-ghost" href="./admin.php">Open Projectbeheer</a>
                    <a class="btn btn-ghost" href="./contacts.php">Contactberichten</a>
                    <a class="btn btn-ghost" href="<?= $base ?>/uploads/projects/" target="_blank" rel="noopener">Map uploads</a>
                </div>
            </article>

            <article class="card">
                <header class="card__header">
                    <h3>Laatste activiteiten</h3>
                </header>
                <ul class="timeline">
                    <?php if (!$logs): ?>
                        <li><span>—</span> Nog geen activiteiten</li>
                    <?php else: foreach ($logs as $r):
                        $t   = date('H:i', strtotime((string)$r['created_at']));
                        $act = (string)$r['action'];
                        $ent = (string)$r['entity'];
                        $eid = (int)$r['entity_id'];
                        $meta = json_decode((string)($r['meta'] ?? ''), true) ?: [];
                        $who = $r['username'] ? 'door ' . e((string)$r['username']) : '';

                        if ($ent === 'project') {
                            $title = isset($meta['title']) ? e((string)$meta['title']) : ('#' . $eid);
                            if     ($act === 'create') { $msg = "Project <strong>{$title}</strong> aangemaakt {$who}"; }
                            elseif ($act === 'update') { $msg = "Project <strong>{$title}</strong> bijgewerkt {$who}"; }
                            elseif ($act === 'delete') { $msg = "Project <strong>{$title}</strong> verwijderd {$who}"; }
                            elseif ($act === 'toggle') {
                                $to = isset($meta['to']) ? e((string)$meta['to']) : '';
                                $msg = "Project <strong>{$title}</strong> status → {$to} {$who}";
                            } else { $msg = ucfirst($act) . " project {$who}"; }

                        } elseif ($ent === 'contact') {
                            if ($act === 'create') {
                                $nm  = isset($meta['name']) ? e((string)$meta['name']) : ('#'.$eid);
                                $sub = isset($meta['subject']) ? ' — '.e((string)$meta['subject']) : '';
                                $msg = "Nieuw contactbericht van <strong>{$nm}</strong>{$sub}";
                            } elseif ($act === 'toggle') {
                                $to = isset($meta['to']) ? e((string)$meta['to']) : '';
                                $msg = "Contactbericht #{$eid} gemarkeerd als <strong>{$to}</strong> {$who}";
                            } elseif ($act === 'view') {
                                $msg = "Contactbericht #{$eid} bekeken {$who}";
                            } elseif ($act === 'delete') {
                                $msg = "Contactbericht #{$eid} verwijderd {$who}";
                            } else {
                                $msg = ucfirst($act) . " contact {$who}";
                            }

                        } elseif ($ent === 'user' && ($act === 'login' || $act === 'logout')) {
                            $msg = "Gebruiker {$who} " . ($act === 'login' ? 'ingelogd' : 'uitgelogd');
                        } else {
                            $msg = ucfirst($act) . " {$ent} {$who}";
                        }
                        ?>
                        <li><span><?= e($t) ?></span> <?= $msg ?></li>
                    <?php endforeach; endif; ?>
                </ul>
            </article>
        </div>

        <!-- Bezoekers-grafiek + Top pagina's -->
        <div class="grid two">
            <article class="card">
                <header class="card__header"><h3>Bezoekers laatste 14 dagen</h3></header>
                <div class="card__body chart chart--pretty">
                    <div class="kpis">
                        <span class="badge badge--info">Totaal 7d: <?= (int)$views7 ?></span>
                        <span class="badge badge--info">Uniek 7d: <?= (int)$uniq7 ?></span>
                        <span class="badge badge--info">Gem./dag: <?= str_replace('.', ',', number_format($avg, 1)) ?></span>
                    </div>

                    <div class="chart__wrap">
                        <div class="chart__ylabels">
                            <?php for ($i = $ticks; $i >= 0; $i--): ?>
                                <span><?= (int)round($yMax * $i / $ticks) ?></span>
                            <?php endfor; ?>
                        </div>

                        <div class="chart__bars" style="--chart-height:<?= $chartH ?>px">
                            <?php foreach ($daily as $d):
                                $h = (int)round((($d['count'] / $yMax) * $chartH));
                                if ($d['count'] > 0 && $h < 4) $h = 4;
                                $lbl = date('d-m', strtotime($d['date'])) . ' • ' . $d['count'];
                                $cls = ($d['date'] === $today) ? ' chart__bar--today' : '';
                                ?>
                                <div class="chart__bar<?= $cls ?>" style="--h:<?= $h ?>" data-tip="<?= e($lbl) ?>"></div>
                            <?php endforeach; ?>

                            <div class="chart__avg-line" style="top: calc(100% - <?= $avgH ?>px);">
                                <span class="chart__avg-badge">gem. <?= str_replace('.', ',', number_format($avg, 1)) ?></span>
                            </div>
                        </div>

                        <div class="chart__x">
                            <?php foreach ($daily as $i => $d): ?>
                                <span><?= $i % 2 === 0 ? e(date('d', strtotime($d['date']))) : '' ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>

            <article class="card">
                <header class="card__header"><h3>Top pagina’s (30d)</h3></header>
                <div class="card__body">
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Pad</th><th>Weergaven</th></tr></thead>
                            <tbody>
                            <?php if (!$topPages): ?>
                                <tr><td colspan="2">Nog geen data.</td></tr>
                            <?php else: foreach ($topPages as $tp): ?>
                                <tr>
                                    <td><?= e($tp['path']) ?></td>
                                    <td><?= (int)$tp['c'] ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- Projecten -->
    <section id="projects" class="section">
        <h2>Projecten</h2>

        <div class="card">
            <div class="table-toolbar">
                <div class="input-wrap">
                    <input type="search" id="projectSearch" placeholder="Zoek project..." />
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-primary" href="./project-new.php">Project toevoegen</a>
                    <a class="btn btn-ghost" href="./admin.php">Open volledige lijst</a>
                </div>
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
                    <tbody id="projectTableBody">
                    <?php if (!$projects): ?>
                        <tr><td colspan="4">Nog geen projecten.</td></tr>
                    <?php else: foreach ($projects as $p): ?>
                        <tr>
                            <td><?= e($p['title']) ?></td>
                            <td><?= status_pill((string)$p['status']) ?></td>
                            <td><?= e(date('d-m-Y', strtotime((string)$p['updated_at']))) ?></td>
                            <td class="actions-col">
                                <a class="btn btn-ghost btn-sm" href="./project-edit.php?id=<?= (int)$p['id'] ?>">Bewerken</a>
                                <?php if (!empty($p['live_url'])): ?>
                                    <a class="btn btn-ghost btn-sm" href="<?= e($p['live_url']) ?>" target="_blank" rel="noopener">Bekijken</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Media -->
    <section id="media" class="section">
        <h2>Media</h2>

        <div class="card">
            <div class="table-toolbar">
                <div class="input-wrap">
                    <input type="search" id="mediaSearch" placeholder="Zoek media..." />
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-primary" href="./project-new.php">Upload via project</a>
                    <a class="btn btn-ghost" href="<?= $base ?>/uploads/projects/" target="_blank" rel="noopener">Open uploads</a>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Bestand</th>
                        <th>Type</th>
                        <th>Grootte</th>
                        <th>Geüpload</th>
                        <th>Acties</th>
                    </tr>
                    </thead>
                    <tbody id="mediaTableBody">
                    <tr>
                        <td colspan="5">Koppel later aan je uploadtabel of map.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="section">
        <h2>Contact</h2>

        <div class="card">
            <div class="table-toolbar">
                <div class="input-wrap">
                    <input type="search" id="contactSearch" placeholder="Zoek bericht..." disabled />
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-primary" href="./contacts.php">Bekijk alle berichten</a>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Onderwerp</th>
                        <th>Ontvangen</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                    </thead>
                    <tbody id="contactTableBody">
                    <?php if (!$contactLatest): ?>
                        <tr><td colspan="6">Nog geen berichten.</td></tr>
                    <?php else: foreach ($contactLatest as $c): ?>
                        <tr>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['email']) ?></td>
                            <td><?= e($c['subject']) ?></td>
                            <td><?= e(date('d-m-Y H:i', strtotime((string)$c['created_at']))) ?></td>
                            <td>
                <span class="pill <?= $c['status']==='open' ? 'pill--warn' : 'pill--ok' ?>">
                  <?= e($c['status']) ?>
                </span>
                            </td>
                            <td class="actions-col">
                                <a class="btn btn-ghost btn-sm" href="./contact-view.php?id=<?= (int)$c['id'] ?>">Bekijken</a>
                                <form action="./contact-toggle.php" method="post" style="display:inline">
                                    <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <input type="hidden" name="to" value="<?= $c['status']==='open' ? 'read' : 'open' ?>">
                                    <button class="btn btn-ghost btn-sm" type="submit">
                                        <?= $c['status']==='open' ? 'Markeer als gelezen' : 'Terug naar open' ?>
                                    </button>
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
