// javascript/login.js

// === Config ===
// Bewust geen plain-text geheimen. We vergelijken hashes met SALT.
// Credentials: user = "LucasA", password = "123".
const SALT = "LA-2025::";
const USER_HASH_HEX = "4e818388f2f7c31f911c0625d5cd781e5322bf635e2d870044319d6ad28fca6e"; // sha256(SALT + "LucasA")
const PASS_HASH_HEX = "98f4d4ebd0c479d4de82abe168b123ad625217732281fe85a381ea2520d6a0d3"; // sha256(SALT + "123")
const MAX_ATTEMPTS = 5;
const LOCK_MS = 2 * 60 * 1000; // 2 minuten

const ADMIN_URL = "../html/admin.html"; // waarheen na login

// === Helpers ===
async function sha256Hex(str) {
  const enc = new TextEncoder().encode(str);
  const buf = await crypto.subtle.digest("SHA-256", enc);
  const arr = Array.from(new Uint8Array(buf));
  return arr.map(b => b.toString(16).padStart(2, "0")).join("");
}

// Constant-time string compare
function timingSafeEqual(a, b) {
  if (a.length !== b.length) return false;
  let out = 0;
  for (let i = 0; i < a.length; i++) {
    out |= a.charCodeAt(i) ^ b.charCodeAt(i);
  }
  return out === 0;
}

function setStatus(msg, ok = false) {
  const el = document.getElementById("status");
  if (!el) return;
  el.textContent = msg;
  el.classList.remove("alert--ok", "alert--error");
  el.classList.add(ok ? "alert--ok" : "alert--error");
}

// Lockout state in localStorage
function getLockState() {
  const raw = localStorage.getItem("auth_lock");
  if (!raw) return { attempts: 0, until: 0 };
  try { return JSON.parse(raw); } catch { return { attempts: 0, until: 0 }; }
}
function setLockState(state) {
  localStorage.setItem("auth_lock", JSON.stringify(state));
}

function isLocked(now = Date.now()) {
  const { until } = getLockState();
  return until && now < until;
}

function registerFail() {
  const now = Date.now();
  const s = getLockState();
  const attempts = (s.attempts || 0) + 1;
  if (attempts >= MAX_ATTEMPTS) {
    setLockState({ attempts: 0, until: now + LOCK_MS });
  } else {
    setLockState({ attempts, until: 0 });
  }
}

function resetLock() {
  setLockState({ attempts: 0, until: 0 });
}

// Sessie-flag voor simpele guarding van de admin UI
function setSession() {
  // minimaal: random token
  const token = crypto.getRandomValues(new Uint8Array(16)).reduce((s, b) => s + b.toString(16).padStart(2, "0"), "");
  sessionStorage.setItem("auth", token);
}

// === Submit handler ===
document.getElementById("loginForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();

  if (isLocked()) {
    setStatus("Te veel pogingen. Probeer het later opnieuw.");
    return;
  }

  const user = document.getElementById("user")?.value?.trim() || "";
  const pass = document.getElementById("password")?.value || "";

  // Hash invoer met salt
  const userHash = await sha256Hex(SALT + user);
  const passHash = await sha256Hex(SALT + pass);

  const userOk = timingSafeEqual(userHash, USER_HASH_HEX);
  const passOk = timingSafeEqual(passHash, PASS_HASH_HEX);

  if (userOk && passOk) {
    resetLock();
    setSession();
    location.href = ADMIN_URL;
  } else {
    registerFail();
    setStatus("Onjuiste inloggegevens.");
  }
});

// Optioneel: client-side guard snippet voor admin-pagina's
// Zet dit (of iets vergelijkbaars) bovenin ../js/admin.js:
// if (!sessionStorage.getItem('auth')) { location.href = '../html/login.html'; }
