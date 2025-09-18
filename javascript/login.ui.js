// ../javascript/login.ui.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const btn  = document.getElementById('loginBtn');
    const card = document.getElementById('authCard');
    const user = document.getElementById('user');
    const pass = document.getElementById('password');

    // focus op userveld
    if (user) user.focus();

    // Als server fout teruggaf (?err=...), kaart schudden en velden highlighten
    if (card && card.dataset.error === '1') {
        const highlight = () => {
            [user, pass].forEach(i => i && i.classList.add('input-error'));
            setTimeout(() => [user, pass].forEach(i => i && i.classList.remove('input-error')), 1200);
        };
        highlight();
    }

    // Alleen UI: spinner aan tijdens submit, daarna laat de browser gewoon posten
    form?.addEventListener('submit', () => {
        btn?.classList.add('btn--loading');
    });
});
// --- Perfect center for the login card (header + sub-hero + footer aware)
(function () {
    const root = document.documentElement;
    const header = document.querySelector('.site-header');
    const hero   = document.querySelector('.hero.hero--sub');
    const footer = document.querySelector('.site-footer');

    function setLoginOffset() {
        const h = header ? header.offsetHeight : 0;
        const he = hero ? hero.offsetHeight : 0;
        const f = footer ? footer.offsetHeight : 0;

        // totale ruimte die NIET voor de login-sectie beschikbaar is
        const offset = h + he + f;

        // voorkom te kleine of negatieve waarden
        const safeOffset = Math.max(160, Math.min(offset, 520));
        root.style.setProperty('--login-offset', safeOffset + 'px');

        // optioneel een paar pixels finetunen (bijv. micro-verticaal)
        root.style.setProperty('--login-y', '0px');
    }

    setLoginOffset();
    window.addEventListener('resize', setLoginOffset);
})();
