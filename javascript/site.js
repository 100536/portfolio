// ========= Nav-indicator =========
const nav = document.querySelector('.nav');
const indicator = document.querySelector('.nav__indicator');
const links = document.querySelectorAll('.nav__link');

function positionIndicator(el) {
  if (!el || !indicator || !nav) return;
  const { left, width } = el.getBoundingClientRect();
  const { left: parentLeft } = nav.getBoundingClientRect();
  indicator.style.left = `${left - parentLeft}px`;
  indicator.style.width = `${width}px`;
}
links.forEach(link => {
  link.addEventListener('mouseenter', () => positionIndicator(link));
  link.addEventListener('focus', () => positionIndicator(link));
  link.addEventListener('click', () => {
    links.forEach(l => l.classList.remove('is-active'));
    link.classList.add('is-active');
    positionIndicator(link);
  });
});
nav?.addEventListener('mouseleave', () => {
  const active = document.querySelector('.nav__link.is-active') || links[0];
  positionIndicator(active);
});
window.addEventListener('load', () => {
  const active = document.querySelector('.nav__link.is-active') || links[0];
  positionIndicator(active);
});

// ========= Mobiele nav open/close via brand (optioneel) =========
document.querySelector('.brand')?.addEventListener('click', () => {
  if (window.innerWidth <= 860) document.body.classList.toggle('nav-open');
});

// ========= Thema switch =========
const themeSwitch = document.getElementById('themeSwitch');
const htmlEl = document.documentElement;
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'light' || savedTheme === 'dark') {
  htmlEl.setAttribute('data-theme', savedTheme);
}
themeSwitch?.addEventListener('click', () => {
  const current = htmlEl.getAttribute('data-theme') || 'dark';
  const next = current === 'dark' ? 'light' : 'dark';
  htmlEl.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
});

// ========= Jaar in footer =========
document.getElementById('year')?.append(new Date().getFullYear());

// ========= Background particles in hero =========
(function () {
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const canvas = document.getElementById('fxCanvas');
  const host = document.getElementById('bgFx');
  if (!canvas || !host || prefersReduced) return;

  const ctx = canvas.getContext('2d', { alpha: true });
  let dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
  let w = 0, h = 0, running = true, particles = [];

  // CSS kleur --blue
  const cssBlue = getComputedStyle(document.documentElement).getPropertyValue('--blue').trim() || '#2563eb';
  const blueRGB = hexToRgb(cssBlue) || { r: 37, g: 99, b: 235 };

  function hexToRgb(hex) {
    const m = hex.replace('#','').match(/^([a-f\d]{3}|[a-f\d]{6})$/i);
    if (!m) return null;
    let c = m[0];
    if (c.length === 3) c = c.split('').map(x => x + x).join('');
    const num = parseInt(c, 16);
    return { r: (num >> 16) & 255, g: (num >> 8) & 255, b: num & 255 };
  }

  function resize() {
    const rect = host.getBoundingClientRect();
    w = Math.max(1, rect.width);
    h = Math.max(1, rect.height);
    canvas.width = Math.floor(w * dpr);
    canvas.height = Math.floor(h * dpr);
    canvas.style.width = w + 'px';
    canvas.style.height = h + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    // Aantal particles o.b.v. oppervlak (cap voor performance)
    const target = Math.min(120, Math.round((w * h) / 10000));
    if (particles.length > target) particles.length = target;
    while (particles.length < target) particles.push(makeParticle());
  }

  function makeParticle() {
    const speed = 0.15 + Math.random() * 0.35; // px per frame
    const angle = Math.random() * Math.PI * 2;
    return {
      x: Math.random() * w,
      y: Math.random() * h,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      r: 1 + Math.random() * 1.6
    };
  }

  function step() {
    if (!running) return;
    ctx.clearRect(0, 0, w, h);

    // Beweeg en teken puntjes
    for (let p of particles) {
      p.x += p.vx; p.y += p.vy;

      // zachte bounce
      if (p.x < -10 || p.x > w + 10) p.vx *= -1;
      if (p.y < -10 || p.y > h + 10) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${blueRGB.r}, ${blueRGB.g}, ${blueRGB.b}, 0.35)`;
      ctx.fill();
    }

    // Lijntjes tussen dichte puntjes
    const maxDist = 120;
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const a = particles[i], b = particles[j];
        const dx = a.x - b.x, dy = a.y - b.y;
        const d2 = dx * dx + dy * dy;
        if (d2 < maxDist * maxDist) {
          const t = 1 - Math.sqrt(d2) / maxDist; // 0..1
          ctx.beginPath();
          ctx.moveTo(a.x, a.y);
          ctx.lineTo(b.x, b.y);
          ctx.strokeStyle = `rgba(${blueRGB.r}, ${blueRGB.g}, ${blueRGB.b}, ${0.25 * t})`;
          ctx.lineWidth = 1;
          ctx.stroke();
        }
      }
    }

    requestAnimationFrame(step);
  }

  const ro = new ResizeObserver(resize);
  ro.observe(host);

  document.addEventListener('visibilitychange', () => {
    running = document.visibilityState !== 'hidden';
    if (running) requestAnimationFrame(step);
  });

  resize();
  requestAnimationFrame(step);
})();
