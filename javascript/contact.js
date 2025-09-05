// Toont statusmelding op basis van ?status=ok|error in de URL
(function () {
  const el = document.getElementById('status');
  if (!el) return;
  const s = new URLSearchParams(location.search).get('status');
  if (!s) return;
  const ok = s === 'ok';
  el.textContent = ok ? 'Bericht verzonden.' : 'Versturen mislukt. Probeer het later opnieuw.';
  el.classList.add(ok ? 'alert--ok' : 'alert--error');
})();
