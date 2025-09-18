<!doctype html>
<html lang="nl" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Portfolio — Lucas Askamp</title>
  <link rel="stylesheet" href="css/site.css" />
</head>
<body>

  <!-- Header / Navigatie -->
  <header class="site-header">
    <div class="container header-inner">
      <a href="#home" class="brand" aria-label="Ga naar home">
        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l7 4v12l-7 4-7-4V6l7-4zm0 2.3L7 6.8v10.4l5 2.5 5-2.5V6.8l-5-2.5z"/></svg>
        <span>Lucas Askamp</span>
      </a>

      <nav class="nav" aria-label="Hoofd">
        <a href="#home" class="nav__link is-active">Home</a>
        <a href="main/about.php" class="nav__link">Over mij</a>
        <a href="main/project.php" class="nav__link">Projecten</a>
        <a href="main/contact.php" class="nav__link">Contact</a>
        <a href="main/login.php" class="nav__link">Login</a>
        <span class="nav__indicator" aria-hidden="true"></span>
      </nav>
    </div>
  </header>

 <!-- Hero -->
  <section id="home" class="hero">
  <!-- Achtergrond-animatie: nu full-width -->
  <div class="bg-fx" id="bgFx" aria-hidden="true">
    <canvas id="fxCanvas"></canvas>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>
  </div>

  <!-- Content blijft in de container -->
  <div class="container hero-inner">
    <div class="hero-copy">
      <h1>Portfolio van Lucas Askamp</h1>
      <p>Webdeveloper. Focus op websites met strakke code en een duidelijke UX.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="main/project.php">Bekijk projecten</a>
        <a class="btn" href="main/contact.php">Neem contact op</a>
      </div>
    </div>
  </div>
</section>

  <!-- Korte intro -->
  <section class="section">
    <div class="container grid two">
      <article class="card">
        <h3>Wie ik ben</h3>
        <p>
          Tweedejaars student Software Development aan het Grafisch Lyceum Rotterdam (MBO-4).
          Ik ben al vroeg met tech en code bezig en zoek een stageplek om mijn skills verder
          te laten groeien.
        </p>
        <div class="actions" style="margin-top:10px;">
          <a class="btn btn-primary" href="/portfolio/Cv-Lucas_(Carel)_Askamp(2025).pdf" target="_blank" rel="noopener">Download CV (PDF)</a>
          <a class="btn" href="main/contact.php">Plan een kennismaking</a>
        </div>
      </article>

      <article class="card">
        <h3>Wat ik doe</h3>
        <p>
          Ik bouw dynamische, responsieve websites met HTML, CSS, JavaScript en PHP.
          Daarnaast verdiep ik me in C# en game-development (Unity/Unreal).
        </p>
      </article>
    </div>
  </section>

  <!-- Highlights -->
  <section class="section">
    <div class="container grid three">
      <article class="card">
        <h3>Frontend</h3>
        <p>Schone mark-up, moderne CSS en duidelijke interacties.</p>
      </article>
      <article class="card">
        <h3>Performance</h3>
        <p>Lichte pagina’s, snelle laadtijden en heldere code.</p>
      </article>
      <article class="card">
        <h3>Beheer</h3>
        <p>Admin hub in dezelfde stijl voor eenvoudig onderhoud.</p>
      </article>
    </div>
  </section>

  <!-- Skills -->
  <section class="section">
    <div class="container grid two">
      <article class="card">
        <h3>Codeertalen</h3>
        <ul class="chip-list">
          <li class="chip">HTML</li>
          <li class="chip">CSS</li>
          <li class="chip">JavaScript</li>
          <li class="chip">PHP</li>
          <li class="chip">C# (basis)</li>
          <li class="chip">Database</li>
        </ul>
      </article>
      <article class="card">
        <h3>Software & tools</h3>
        <ul class="chip-list">
          <li class="chip">PHPStorm</li>
          <li class="chip">phpMyAdmin</li>
          <li class="chip">Adobe Creative Cloud</li>
          <li class="chip">Unity</li>
          <li class="chip">Unreal Engine</li>
        </ul>
      </article>
    </div>
  </section>

  <!-- Ervaring -->
  <section class="section">
    <div class="container grid two">
      <article class="card">
        <h3>McDonald’s — Host/Supervisor</h3>
        <p class="muted">Feb 2024 – heden</p>
        <p>Binnen acht maanden “Crew van de Maand”, daarna doorgegroeid naar supervisor. Verantwoordelijk voor de eetruimte en aansturing van het team.</p>
      </article>
      <article class="card">
        <h3>Albert Heijn — Vakkenvuller</h3>
        <p class="muted">Aug 2022 – Feb 2023</p>
        <p>Vakken vullen en winkelondersteuning in de avonduren, meerdere dagen per week.</p>
      </article>
    </div>
  </section>

  <!-- Opleiding & Talen -->
  <section class="section">
    <div class="container grid two">
      <article class="card">
        <h3>Opleiding</h3>
        <p><strong>Grafisch Lyceum Rotterdam</strong> — ICT & Media, MBO-4 (3-jarig).</p>
      </article>
      <article class="card">
        <h3>Talen</h3>
        <ul class="chip-list">
          <li class="chip">Nederlands</li>
          <li class="chip">Engels</li>
        </ul>
      </article>
    </div>
  </section>

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
        <li><a href="main/contact.php">Contact</a></li>
      </ul>
    </div>
  </footer>

  <script src="/portfolio/javascript/analytics.js" defer></script>
  <script src="javascript/site.js"></script>
</body>
</html>
