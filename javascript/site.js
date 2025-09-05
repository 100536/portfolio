// Actieve nav-underline (werkt op hover en bij de actieve link)
const nav = document.querySelector('.nav');
const indicator = document.querySelector('.nav__indicator');
const links = document.querySelectorAll('.nav__link');

function positionIndicator(el) {
  if (!el || !indicator) return;
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
  });
});
nav?.addEventListener('mouseleave', () => {
  const active = document.querySelector('.nav__link.is-active');
  positionIndicator(active);
});
window.addEventListener('load', () => {
  const active = document.querySelector('.nav__link.is-active') || links[0];
  positionIndicator(active);
});

// Mobiel: nav togglen via brand of aparte knop (optioneel kun je een hamburger toevoegen)
document.querySelector('.brand')?.addEventListener('click', () => {
  if (window.innerWidth <= 860) document.body.classList.toggle('nav-open');
});

// Thema switch (dark/light), default = dark
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

// Jaar in footer
document.getElementById('year').textContent = new Date().getFullYear();
