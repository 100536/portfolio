// ../javascript/login.bg.js
(() => {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cvs = document.getElementById('loginFx');
    const stage = document.querySelector('.login-stage');
    const card  = document.getElementById('authCard');

    // --- Particle achtergrond ---
    if (cvs && !prefersReduced) {
        const ctx = cvs.getContext('2d', { alpha: true });
        let w = 0, h = 0, dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
        const dots = [];
        const DOTS = 48; // aantal deeltjes
        const SPD  = 0.08; // snelheid

        function resize() {
            const rect = cvs.getBoundingClientRect();
            w = Math.floor(rect.width);
            h = Math.floor(rect.height);
            cvs.width  = Math.floor(w * dpr);
            cvs.height = Math.floor(h * dpr);
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function seed() {
            dots.length = 0;
            for (let i = 0; i < DOTS; i++) {
                dots.push({
                    x: Math.random() * w,
                    y: Math.random() * h,
                    vx: (Math.random() - .5) * SPD,
                    vy: (Math.random() - .5) * SPD,
                    r: 1 + Math.random() * 2,
                    a: .35 + Math.random() * .35
                });
            }
        }

        function step() {
            ctx.clearRect(0, 0, w, h);
            ctx.globalCompositeOperation = 'lighter';
            for (const d of dots) {
                d.x += d.vx; d.y += d.vy;
                if (d.x < -10) d.x = w + 10;
                if (d.x > w + 10) d.x = -10;
                if (d.y < -10) d.y = h + 10;
                if (d.y > h + 10) d.y = -10;

                ctx.beginPath();
                ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(168, 189, 255, ${d.a})`;
                ctx.shadowColor = 'rgba(80,120,255,.35)';
                ctx.shadowBlur = 12;
                ctx.fill();
            }
            requestAnimationFrame(step);
        }

        const ro = new ResizeObserver(() => { resize(); seed(); });
        ro.observe(cvs);
        resize(); seed(); step();
    }

    // --- Mini tilt van de card (geen effect op mobiel) ---
    if (stage && card && !prefersReduced) {
        let raf = 0;
        const maxTilt = 6; // graden

        function onMove(e) {
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(() => {
                const r = stage.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width;  // 0..1
                const y = (e.clientY - r.top)  / r.height; // 0..1
                const tx = (y - 0.5) * -maxTilt;
                const ty = (x - 0.5) *  maxTilt;
                card.style.transform = `rotateX(${tx}deg) rotateY(${ty}deg) translateZ(0)`;
            });
        }
        function reset() { card.style.transform = 'translateZ(0)'; }

        stage.addEventListener('mousemove', onMove);
        stage.addEventListener('mouseleave', reset);
        window.addEventListener('blur', reset);
    }
})();
