// /javascript/contact.js
document.addEventListener('DOMContentLoaded', () => {
    const url   = new URL(window.location.href);
    const sent  = url.searchParams.get('sent');
    const err   = url.searchParams.get('err');

    const modal = document.getElementById('contactSuccessModal');
    if (!modal) return;

    const closeBtn = modal.querySelector('[data-close-modal]');

    function openModal() {
        modal.classList.add('is-open');
        // focus op de primaire knop
        closeBtn?.focus();
        document.addEventListener('keydown', onEsc);
    }
    function closeModal() {
        modal.classList.remove('is-open');
        document.removeEventListener('keydown', onEsc);
    }
    function onEsc(e){ if (e.key === 'Escape') closeModal(); }

    // Open de modal bij ?sent=1
    if (sent === '1') {
        openModal();
        // Querystring opruimen zodat herladen geen popup meer geeft
        history.replaceState({}, '', window.location.pathname + window.location.hash);
    }

    // Sluiten met knop of klik buiten de kaart
    closeBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // (optioneel) toon een foutmelding in de alert-balk bij ?err=
    if (err) {
        // Je rended al server-side alerts; niets extra nodig hier.
        // Laat dit staan voor het geval je later client-side wilt tonen.
    }
});
