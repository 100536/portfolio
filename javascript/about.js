// Reveal + langzamere typewriter in de terminal
(function () {
    const REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // === Instellingen voor snelheid (ms per teken) ===
    const SPEED_CMD  = 34;   // was ~18 → nu langzamer
    const SPEED_TEXT = 24;   // was ~12 → nu langzamer
    const PAUSE_BETWEEN = 420; // pauze tussen regels (was ~280)

    // ---- Reveal on scroll
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && !REDUCED) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const delay = e.target.getAttribute('data-reveal-delay');
                    if (delay) e.target.style.setProperty('--reveal-delay', delay);
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    // ---- Typewriter in terminal
    const term = document.querySelector('.typewriter');
    if (!term) return;

    const lines = Array.from(term.querySelectorAll('.term-line'));

    function buildPromptLine(cmdText) {
        const frag = document.createDocumentFragment();
        const prompt = document.createElement('span');
        prompt.className = 'term-prompt';
        prompt.textContent = 'lucas@portfolio';
        const tilde = document.createTextNode(':');
        const cmdSpan = document.createElement('span');
        cmdSpan.className = 'term-cmd';
        cmdSpan.textContent = '~$';
        const space = document.createTextNode(' ');
        const typed = document.createElement('span');
        typed.className = 'typed';

        frag.appendChild(prompt);
        frag.appendChild(tilde);
        frag.appendChild(cmdSpan);
        frag.appendChild(space);
        frag.appendChild(typed);
        return { frag, typed, text: cmdText || '' };
    }

    async function typeInto(el, text, speed) {
        // knipperende cursor
        const caret = document.createElement('span');
        caret.className = 'type-caret';
        el.appendChild(caret);
        for (let i = 0; i <= text.length; i++) {
            el.textContent = text.slice(0, i);
            el.appendChild(caret);
            await new Promise(r => setTimeout(r, speed));
        }
        caret.remove();
    }

    async function run() {
        if (REDUCED) {
            // zonder animaties direct vullen
            lines.forEach(l => {
                const cmd = l.getAttribute('data-cmd');
                const txt = l.getAttribute('data-text');
                if (cmd) {
                    const { frag, typed, text } = buildPromptLine(cmd);
                    l.innerHTML = '';
                    l.appendChild(frag);
                    typed.textContent = text;
                } else if (txt) {
                    l.textContent = txt;
                }
            });
            return;
        }

        for (const l of lines) {
            const cmd = l.getAttribute('data-cmd');
            const txt = l.getAttribute('data-text');

            if (cmd) {
                const { frag, typed, text } = buildPromptLine(cmd);
                l.innerHTML = '';
                l.appendChild(frag);
                await typeInto(typed, text, SPEED_CMD);
            } else if (txt) {
                l.textContent = '';
                await typeInto(l, txt, SPEED_TEXT);
            }
            await new Promise(r => setTimeout(r, PAUSE_BETWEEN));
        }
    }

    run().catch(() => {});
})();
