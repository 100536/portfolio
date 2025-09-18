<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/config.php';

$token   = csrf_token();
$ok      = isset($_GET['sent']) && $_GET['sent'] === '1';
$qErr    = $_GET['err'] ?? '';
$old     = $_SESSION['contact_old'] ?? [];
$errKey  = $_SESSION['contact_err'] ?? '';
$errFld  = $_SESSION['contact_err_field'] ?? '';
unset($_SESSION['contact_old'], $_SESSION['contact_err'], $_SESSION['contact_err_field']);

function field_error(string $name, string $errFld, string $errKey): ?string {
    $map = [
            'input_name'    => 'Vul een geldige naam in (minimaal 2 tekens).',
            'input_email'   => 'Vul een geldig e-mailadres in.',
            'input_subject' => 'Onderwerp is te kort.',
            'input_message' => 'Bericht is te kort (minimaal 10 tekens).',
            'csrf'          => 'Beveiligingsfout. Probeer opnieuw.',
            'rate'          => 'Je hebt kort geleden al een bericht gestuurd. Probeer later nog eens.',
            'input'         => 'Controleer je invoer.',
    ];
    if ($errFld === $name && isset($map[$errKey])) return $map[$errKey];
    if ($name === 'global' && $errKey && isset($map[$errKey])) return $map[$errKey];
    return null;
}
?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact — Lucas Askamp</title>
    <link rel="stylesheet" href="../css/site.css" />
    <style>
        /* kleine, geïsoleerde overlay laag – tast je bestaande stijl niet aan */
        .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:1000}
        .modal-backdrop.is-open{display:flex}
        .modal{max-width:520px;width:90vw}
    </style>
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
            <a href="project.php" class="nav__link">Projecten</a>
            <a href="#" class="nav__link is-active">Contact</a>
            <a href="login.php" class="nav__link">Login</a>
            <span class="nav__indicator" aria-hidden="true"></span>
        </nav>
    </div>
</header>

<!-- Sub-hero -->
<section class="hero hero--sub">
    <div class="container hero-inner">
        <div class="hero-copy">
            <h1>Contact</h1>
            <p>Vertel kort wat je zoekt. Ik reageer meestal dezelfde dag.</p>
        </div>
    </div>
</section>

<!-- Inhoud -->
<main class="section">
    <div class="container contact-grid">
        <!-- Formulier -->
        <article class="card">
            <header class="card__header">
                <h2>Stuur een bericht</h2>
            </header>
            <div class="card__body">
                <form action="../php/send_mail.php" method="post" class="contact-form" novalidate>
                    <input type="hidden" name="csrf" value="<?= e($token) ?>">

                    <!-- Globale foutmelding -->
                    <?php if (!$ok && ($g = field_error('global', $errFld, $errKey))): ?>
                        <p class="alert alert--error" role="status"><?= e($g) ?></p>
                    <?php elseif ($ok): ?>
                        <p class="alert alert--ok" role="status">Bedankt, je bericht is verstuurd.</p>
                    <?php endif; ?>

                    <!-- Honeypot -->
                    <div class="hp" aria-hidden="true">
                        <label for="website">Laat leeg</label>
                        <input type="text" id="website" name="website" autocomplete="off" tabindex="-1" />
                    </div>

                    <div class="form-grid">
                        <div class="field">
                            <label class="label" for="name">Naam</label>
                            <input class="input" type="text" id="name" name="name"
                                   value="<?= e($old['name'] ?? '') ?>" placeholder="Je naam" required />
                            <?php if ($m = field_error('name', $errFld, $errKey)): ?>
                                <small class="hint" style="color:#e66"><?= e($m) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label class="label" for="email">E-mail</label>
                            <input class="input" type="email" id="email" name="email"
                                   value="<?= e($old['email'] ?? '') ?>" placeholder="jij@voorbeeld.nl" required />
                            <?php if ($m = field_error('email', $errFld, $errKey)): ?>
                                <small class="hint" style="color:#e66"><?= e($m) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="subject">Onderwerp</label>
                        <input class="input" type="text" id="subject" name="subject"
                               value="<?= e($old['subject'] ?? '') ?>" placeholder="Waar gaat het over?" required />
                        <?php if ($m = field_error('subject', $errFld, $errKey)): ?>
                            <small class="hint" style="color:#e66"><?= e($m) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="field">
                        <label class="label" for="message">Bericht</label>
                        <textarea class="textarea" id="message" name="message" rows="6"
                                  placeholder="Schrijf hier je bericht..." required><?= e($old['message'] ?? '') ?></textarea>
                        <p class="hint">Ik laat snel iets weten.</p>
                        <?php if ($m = field_error('message', $errFld, $errKey)): ?>
                            <small class="hint" style="color:#e66"><?= e($m) ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Verstuur</button>
                        <button type="reset" class="btn">Leegmaken</button>
                    </div>
                </form>
            </div>
        </article>

        <!-- Contactinfo -->
        <aside class="card">
            <header class="card__header">
                <h2>Direct contact</h2>
            </header>
            <div class="card__body">
                <ul class="inline-list">
                    <li>
                        <div class="kicker">E-mail</div>
                        <a href="mailto:lucasa.werk@gmail.com">lucasa.werk@gmail.com</a>
                    </li>
                    <li>
                        <div class="kicker">LinkedIn</div>
                        <a href="https://www.linkedin.com/in/lucas-askamp-87031a2b7/" target="_blank" rel="noopener">Mijn LinkedIn profiel</a>
                    </li>
                    <li>
                        <div class="kicker">GitHub</div>
                        <a href="https://github.com/100536" target="_blank" rel="noopener">Mijn Github</a>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</main>

<!-- SUCCES-MODAL -->
<div class="modal-backdrop" id="contactSuccessModal" aria-hidden="true">
    <div class="card modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalDesc" tabindex="-1">
        <header class="card__header">
            <h3 id="modalTitle">Bericht verstuurd</h3>
        </header>
        <div class="card__body" id="modalDesc">
            <p>Bedankt. Je bericht is ontvangen. Ik neem snel contact op.</p>
        </div>
        <footer class="card__footer actions">
            <button class="btn btn-primary" type="button" data-close-modal>Oké</button>
        </footer>
    </div>
</div>

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
<script src="../javascript/contact.js" defer></script>
</body>
</html>
