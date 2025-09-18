<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/csrf.php';
$token = csrf_token();

/** Eventuele fout via querystring (bv. ?err=1 of ?err=bad) */
$errKey = $_GET['err'] ?? ($_GET['error'] ?? '');
$errMsg = '';
if ($errKey) {
    // Toon 1 generieke boodschap; backend logt details
    $errMsg = 'Onjuiste inloggegevens.';
}
?>
<!doctype html>
<html lang="nl" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login — Lucas Askamp</title>
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
            <a href="../index.php" class="nav__link">Home</a>
            <a href="about.php" class="nav__link">Over mij</a>
            <a href="project.php" class="nav__link">Projecten</a>
            <a href="contact.php" class="nav__link">Contact</a>
            <a href="#" class="nav__link is-active">Login</a>
            <span class="nav__indicator" aria-hidden="true"></span>
        </nav>
    </div>
</header>

<!-- Sub-hero -->
<section class="hero hero--sub">
    <div class="container hero-inner">
        <div class="hero-copy">
            <h1>Inloggen</h1>
            <p>Hier is de admin omgeving van deze portfolio.</p>
        </div>
    </div>
</section>

<!-- Login kaart -->
<main class="section section--login">
    <!-- VOLLEDIGE ACHTERGROND, onder de hero / titel -->
    <div class="login-backdrop" aria-hidden="true">
        <canvas id="loginFx"></canvas>
        <span class="glow glow-1"></span>
        <span class="glow glow-2"></span>
    </div>

    <div class="container auth-wrap">
        <div class="login-stage"><!-- tilt-stage voor de kaart -->

            <!-- JE BESTAANDE CARD (functionaliteit ongewijzigd) -->
            <article
                    class="card auth-card<?= $errMsg ? ' is-shake' : '' ?>"
                    id="authCard"
                    <?= $errMsg ? 'data-error="1"' : '' ?>
            >
                <header class="card__header">
                    <h2 class="auth-title">Portfolio Admin</h2>
                    <p class="muted">Meld je aan met je gebruikersnaam en wachtwoord.</p>
                </header>

                <div class="card__body">
                    <form id="loginForm" class="auth-form" action="../auth/login.php" method="post" novalidate>
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>">

                        <div class="field">
                            <label class="label" for="user">Gebruikersnaam</label>
                            <input class="input" type="text" id="user" name="user"
                                   placeholder="Gebruikersnaam" required autocomplete="username">
                        </div>

                        <div class="field">
                            <label class="label" for="password">Wachtwoord</label>
                            <input class="input" type="password" id="password" name="password"
                                   placeholder="•••••••" required autocomplete="current-password">
                        </div>

                        <div class="actions">
                            <button id="loginBtn" type="submit" class="btn btn-primary">Inloggen</button>
                            <button type="reset" class="btn">Leegmaken</button>
                        </div>

                        <p id="status" class="alert <?= $errMsg ? 'alert--error' : '' ?>" role="status">
                            <?= $errMsg ? htmlspecialchars($errMsg, ENT_QUOTES) : '' ?>
                        </p>
                    </form>
                </div>
            </article>
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
<script src="../javascript/login.ui.js" defer></script>
<script src="../javascript/login.bg.js" defer></script>
</body>
</html>
