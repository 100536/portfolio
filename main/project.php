<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php'; // bevat $pdo en helper e()

// Bepaal je site-basispad dynamisch (bijv. "/portfolio")
$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

// Haal live projecten op en kijk of er een image-record bestaat
$stmt = $pdo->query("
  SELECT p.*, pi.id AS img_id
  FROM projects p
  LEFT JOIN project_images pi ON pi.project_id = p.id
  WHERE p.status = 'live'
  ORDER BY p.updated_at DESC
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

/** Fallback helper als je ooit nog een pad in hero_image gebruikt */
function heroSrc(?string $path, string $base): ?string {
    if (!$path) return null;
    if (preg_match('~^https?://~i', $path) || $path[0] === '/') return $path;
    return $base . '/' . ltrim($path, '/'); // → /portfolio/uploads/projects/...
}

/** Chips van "HTML,CSS,JS" */
function chips(?string $csv): array {
    return array_values(array_filter(array_map('trim', explode(',', $csv ?? ''))));
}
?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Projecten — Lucas Askamp</title>
    <link rel="stylesheet" href="../css/site.css" />
</head>
<body>

<!-- Header / Navigatie -->
<header class="site-header">
    <div class="container header-inner">
        <a href="../index.php" class="brand" aria-label="Ga naar home">
            <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l7 4v12l-7 4-7-4V6l7-4zm0 2.3L7 6.8v10.4l5 2.5 5-2.5V6.8l-5-2.5z"/></svg>
            <span>Lucas Askamp</span>
        </a>

        <nav class="nav" aria-label="Hoofd">
            <a href="../index.php#home" class="nav__link">Home</a>
            <a href="about.php" class="nav__link">Over mij</a>
            <a href="#" class="nav__link is-active">Projecten</a>
            <a href="contact.php" class="nav__link">Contact</a>
            <a href="login.php" class="nav__link">Login</a>
            <span class="nav__indicator" aria-hidden="true"></span>
        </nav>

    </div>
</header>

<!-- Sub-hero -->
<section class="hero hero--sub">
    <div class="container hero-inner">
        <div class="hero-copy">
            <h1>Projecten</h1>
            <p>Een selectie van recente werken en experimenten.</p>
        </div>
    </div>
</section>

<!-- Projecten -->
<main class="section">
    <div class="container">
        <div class="project-grid">
            <?php if (empty($projects)): ?>
                <p class="muted">Nog geen projecten gepubliceerd.</p>
            <?php else: ?>
                <?php foreach ($projects as $p): ?>
                    <?php
                    $techs = chips($p['tech'] ?? '');
                    // BLOB uit DB heeft voorrang; anders fallback naar hero_image pad
                    $imgUrl = !empty($p['img_id'])
                            ? ($base . '/image.php?id=' . (int)$p['id'])
                            : heroSrc($p['hero_image'] ?? null, $base);
                    ?>
                    <article class="card project">
                        <?php if ($imgUrl): ?>
                            <a class="project__thumb" href="<?= e($p['live_url'] ?? '#') ?>" target="_blank" rel="noopener">
                                <img src="<?= e($imgUrl) ?>" alt="<?= e($p['title'] ?? 'Project') ?>" loading="lazy" />
                            </a>
                        <?php endif; ?>
                        <div class="project__body">
                            <h3 class="project__title"><?= e($p['title'] ?? 'Zonder titel') ?></h3>
                            <p class="project__desc"><?= e($p['excerpt'] ?? '') ?></p>
                            <?php if ($techs): ?>
                                <div class="chip-list">
                                    <?php foreach ($techs as $t): ?>
                                        <span class="chip"><?= e($t) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['live_url'])): ?>
                                <div class="project__actions">
                                    <a class="btn btn-primary" href="<?= e($p['live_url']) ?>" target="_blank" rel="noopener">Bekijk live</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <span class="avatar">LA</span>
            <div>
                <strong>Lucas Askamp</strong>
                <div class="muted">© <span id="year"></span> Alle rechten voorbehouden</div>
            </div>
        </div>

        <ul class="footer-menu">
            <li><a href="https://github.com/100536" target="_blank" rel="noopener">GitHub</a></li>
            <li><a href="https://www.linkedin.com/in/lucas-askamp-87031a2b7/" target="_blank" rel="noopener">LinkedIn</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </div>
</footer>

<script src="../javascript/analytics.js" defer></script>
<script src="../javascript/site.js"></script>
</body>
</html>
