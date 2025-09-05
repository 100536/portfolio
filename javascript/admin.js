// Sidebar togglen op mobiel
const toggleBtn = document.getElementById('toggleSidebar');
toggleBtn?.addEventListener('click', () => {
  document.body.classList.toggle('sidebar-open');
});

// Active link styling bij hash-wijziging of klik
const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
function setActiveFromHash() {
  const hash = window.location.hash || '#dashboard';
  navLinks.forEach(a => a.classList.toggle('is-active', a.getAttribute('href') === hash));
}
navLinks.forEach(a => a.addEventListener('click', () => {
  setTimeout(setActiveFromHash, 0);
  document.body.classList.remove('sidebar-open'); // sluit sidebar op mobiel
}));
window.addEventListener('hashchange', setActiveFromHash);
setActiveFromHash();

// Generieke tabel-zoekfunctie
function bindTableSearch(inputId, tbodySelector) {
  const input = document.getElementById(inputId);
  const rows = Array.from(document.querySelectorAll(`${tbodySelector} tr`));
  input?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      tr.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

// Koppel zoekvelden
bindTableSearch('projectSearch', '#projectTableBody');
bindTableSearch('mediaSearch', '#mediaTableBody');
bindTableSearch('contactSearch', '#contactTableBody');
