<?php $unlocked = !empty($error); ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk | Photobooth Studio Admin</title>
<link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/camera-icon.svg') ?>">
<link rel="icon" type="image/png" href="<?= base_url('assets/images/camera-icon.png') ?>">
<link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--line:#e6e8ec;--text:#0f172a;--muted:#64748b;--blue:#2563eb;--blue-2:#1d4fd8}
*{box-sizing:border-box;margin:0;padding:0}
body{
  min-height:100vh;display:grid;place-items:center;padding:24px;overflow-x:hidden;
  background:#07090d;color:var(--text);font-family:'Inter',system-ui,sans-serif;font-size:14px;line-height:1.5;
}
.glow{position:fixed;inset:0;pointer-events:none;opacity:0;transition:opacity 1.2s .1s;
  background:radial-gradient(650px 460px at 28% 52%, rgba(125,160,255,.22), transparent 70%),
             radial-gradient(500px 400px at 85% 90%, rgba(13,148,136,.10), transparent 70%)}
.unlocked .glow{opacity:1}
.flash{position:fixed;inset:0;background:#fff;opacity:0;pointer-events:none;z-index:10}
.shot .flash{animation:flash .9s ease-out}
@keyframes flash{0%{opacity:0}7%{opacity:1}100%{opacity:0}}

.layout{position:relative;z-index:1;width:100%;max-width:960px;display:grid;grid-template-columns:1fr 400px;gap:40px;align-items:center}

/* ---------- Kamera ---------- */
.scene{display:flex;flex-direction:column;align-items:center;gap:4px}
#cam{width:min(380px,100%);height:auto;overflow:visible;display:block}
#knob{cursor:grab;touch-action:none}
#knob.grab{cursor:grabbing}
#knob:focus{outline:none}
#knob:focus-visible .kcirc{stroke:#60a5fa;stroke-width:3}
.unlocked #knob{cursor:default;pointer-events:none}
.hint{color:#8a93a6;font-size:13px;transition:opacity .4s}
.unlocked .hint{opacity:0}
.flashlamp{fill:#6b7690}
.shot .flashlamp{animation:lamp .9s ease-out forwards}
@keyframes lamp{0%{fill:#6b7690}10%{fill:#fff;filter:drop-shadow(0 0 14px #fff)}100%{fill:#9aa6bd;filter:none}}
.shot .rec{animation:rec .9s steps(2,end) 2}
@keyframes rec{50%{opacity:.2}}

/* ---------- Kartu login ---------- */
.card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:30px 28px;box-shadow:0 24px 60px rgba(0,0,0,.45)}
.locked .formwrap{visibility:hidden}
.unlocked .card{animation:develop .85s .3s cubic-bezier(.2,.8,.2,1) both}
@keyframes develop{
  from{opacity:0;transform:translateX(-90px) rotate(-5deg) scale(.96);filter:blur(8px) brightness(1.8)}
  to{opacity:1;transform:none;filter:none}
}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:22px}
.brand-mark{width:40px;height:40px;border-radius:10px;background:var(--blue);display:grid;place-items:center;color:#fff;flex-shrink:0}
.brand-mark svg{width:20px;height:20px}
.brand b{display:block;font-size:16px;line-height:1.2}
.brand small{color:var(--muted);font-size:12px}
h1{font-size:22px;font-weight:700;letter-spacing:-.02em}
.lead{color:var(--muted);margin:4px 0 22px}
.error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 12px;margin-bottom:18px;font-size:13px;font-weight:500}
.field{margin-bottom:16px}
label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}
input[type=text],input[type=password]{width:100%;padding:10px 12px;background:#fff;border:1px solid var(--line);border-radius:8px;color:var(--text);font:inherit;font-size:14px;outline:none}
input::placeholder{color:#94a3b8}
input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,.15)}
button{width:100%;margin-top:6px;padding:11px;border:0;border-radius:8px;background:var(--blue);color:#fff;font:inherit;font-size:14px;font-weight:600;cursor:pointer}
button:hover{background:var(--blue-2)}
button:focus-visible{outline:3px solid rgba(37,99,235,.35);outline-offset:2px}
.foot{text-align:center;color:#6b7690;font-size:12px;margin-top:16px}

@media(max-width:860px){
  .layout{grid-template-columns:1fr;max-width:400px;gap:24px}
  .locked .formwrap{display:none}
  #cam{width:min(280px,100%)}
}
@media(prefers-reduced-motion:reduce){
  .unlocked .card{animation-duration:.01s;animation-delay:0s}
  .shot .flash{animation-duration:.3s}
}
</style>
<noscript><style>.locked .formwrap{visibility:visible!important;display:block!important}.hint{display:none}</style></noscript>
</head>
<body class="<?= $unlocked ? 'unlocked' : 'locked' ?>">
  <div class="glow" aria-hidden="true"></div>
  <div class="flash" aria-hidden="true"></div>

  <div class="layout">
    <div class="scene">
      <svg id="cam" viewBox="0 0 380 420" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <radialGradient id="glass" cx=".35" cy=".3" r=".9">
            <stop offset="0" stop-color="#5b7bd6"/><stop offset=".45" stop-color="#1b2a5e"/><stop offset="1" stop-color="#070b1c"/>
          </radialGradient>
          <linearGradient id="metal" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#e3e8f1"/><stop offset="1" stop-color="#7d869a"/>
          </linearGradient>
        </defs>
        <ellipse cx="175" cy="408" rx="120" ry="9" fill="#000" opacity=".5"/>
        <g stroke="#2a3245" stroke-width="7" stroke-linecap="round">
          <line x1="130" y1="300" x2="100" y2="404"/><line x1="220" y1="300" x2="250" y2="404"/><line x1="175" y1="300" x2="175" y2="406"/>
        </g>
        <rect x="50" y="140" width="250" height="44" rx="8" fill="#2d364b"/>
        <rect x="70" y="148" width="46" height="20" rx="4" fill="#0e121b" stroke="#3a4560"/>
        <rect x="130" y="118" width="64" height="26" rx="4" fill="#3a4560"/>
        <rect class="flashlamp" x="136" y="124" width="52" height="14" rx="2"/>
        <rect x="246" y="130" width="26" height="12" rx="3" fill="#c7ceda"/>
        <rect x="50" y="170" width="250" height="140" rx="12" fill="#1a1f2c"/>
        <rect x="62" y="182" width="226" height="116" rx="8" fill="#212839"/>
        <circle class="rec" cx="268" cy="198" r="4" fill="#ef4444"/>
        <circle cx="175" cy="240" r="58" fill="#0b0e14" stroke="#3a4560" stroke-width="3"/>
        <circle cx="175" cy="240" r="50" fill="none" stroke="#8a93a6" stroke-width="1"/>
        <circle cx="175" cy="240" r="40" fill="url(#glass)"/>
        <circle cx="161" cy="226" r="7" fill="#fff" opacity=".35"/>
        <!-- kabel shutter -->
        <path id="cord" d="M259 136 Q 352 140 330 290" fill="none" stroke="#b4bccb" stroke-width="3" stroke-linecap="round"/>
        <g id="knob" transform="translate(330 300)" role="button" aria-label="Kabel rana. Tarik ke bawah, atau tekan Enter, untuk membuka form masuk" <?= $unlocked ? 'tabindex="-1" aria-hidden="true"' : 'tabindex="0"' ?>>
          <circle r="30" fill="transparent"/>
          <rect x="-3" y="-14" width="6" height="8" rx="1" fill="#7d869a"/>
          <circle class="kcirc" r="10" cy="2" fill="url(#metal)" stroke="#4a5368" stroke-width="1.5"/>
        </g>
      </svg>
      <p class="hint">Tarik kabel untuk masuk</p>
    </div>

    <div class="formwrap">
      <div class="card" id="card" <?= $unlocked ? '' : 'inert aria-hidden="true"' ?>>
        <div class="brand">
          <span class="brand-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
          </span>
          <div><b>Photobooth</b><small>Studio Admin</small></div>
        </div>

        <h1>Selamat datang kembali</h1>
        <p class="lead">Masuk untuk mengelola template, order, dan pembayaran studio.</p>

        <?php if (!empty($error)): ?>
          <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
          <div class="field">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" placeholder="Contoh: admin" autocomplete="username" required>
          </div>
          <div class="field">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Masukkan password" autocomplete="current-password" required>
          </div>
          <button type="submit">Masuk ke dashboard</button>
        </form>
      </div>
      <div class="foot">Khusus admin Photobooth Studio</div>
    </div>
  </div>

<script>
(function () {
  var body = document.body, svg = document.getElementById('cam'), knob = document.getElementById('knob'),
      cord = document.getElementById('cord'), card = document.getElementById('card'), user = document.getElementById('username');
  var RX = 330, RY = 300, AX = 259, AY = 136, THRESH = 70;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var x = RX, y = RY, vx = 0, vy = 0, drag = false, running = false, sp = null;

  function unlocked() { return body.classList.contains('unlocked'); }
  function draw() {
    cord.setAttribute('d', 'M' + AX + ' ' + AY + ' Q ' + (Math.max(x, 300) + 22) + ' ' + (AY + 4) + ' ' + x.toFixed(1) + ' ' + (y - 12).toFixed(1));
    knob.setAttribute('transform', 'translate(' + x.toFixed(1) + ' ' + y.toFixed(1) + ')');
  }
  function settled() { return Math.abs(x - RX) < .05 && Math.abs(y - RY) < .05 && Math.abs(vx) + Math.abs(vy) < .02; }
  function loop(ts) {
    if (!running) return;
    if (!drag) {
      var sway = (reduce || unlocked()) ? 0 : Math.sin(ts / 700) * 1.2;
      vx += (RX + sway - x) * .14; vy += (RY - y) * .14;
      vx *= .8; vy *= .8; x += vx; y += vy;
    }
    draw();
    if (drag || !settled() || (!reduce && !unlocked())) requestAnimationFrame(loop); else running = false;
  }
  function start() { if (!running) { running = true; requestAnimationFrame(loop); } }

  function sound() {
    function click(g) {
      try {
        var C = window.AudioContext || window.webkitAudioContext, a = new C(), n = Math.floor(a.sampleRate * .08),
            b = a.createBuffer(1, n, a.sampleRate), d = b.getChannelData(0);
        for (var i = 0; i < n; i++) d[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / n, 3);
        var s = a.createBufferSource(), gn = a.createGain();
        s.buffer = b; gn.gain.value = g; s.connect(gn); gn.connect(a.destination); s.start();
        setTimeout(function () { a.close(); }, 400);
      } catch (e) {}
    }
    click(.28); setTimeout(function () { click(.18); }, 110);
  }

  function shoot() {
    if (unlocked()) return;
    drag = false;
    body.classList.add('shot');
    body.classList.remove('locked');
    body.classList.add('unlocked');
    card.removeAttribute('inert'); card.removeAttribute('aria-hidden');
    knob.setAttribute('tabindex', '-1');
    sound();
    try { if (navigator.vibrate) navigator.vibrate(25); } catch (e) {}
    setTimeout(function () { user.focus({ preventScroll: true }); }, 700);
    start();
  }

  function pt(e) {
    var p = svg.createSVGPoint(); p.x = e.clientX; p.y = e.clientY;
    return p.matrixTransform(svg.getScreenCTM().inverse());
  }
  knob.addEventListener('pointerdown', function (e) {
    if (unlocked()) return;
    knob.setPointerCapture(e.pointerId);
    sp = pt(e); drag = true; knob.classList.add('grab'); start(); e.preventDefault();
  });
  knob.addEventListener('pointermove', function (e) {
    if (!drag) return;
    var p = pt(e), dx = p.x - sp.x, dy = Math.max(p.y - sp.y, -8);
    x = RX + dx * .3;
    y = RY + (dy > 0 ? 140 * (1 - Math.exp(-dy / 80)) : dy);
    draw();
    if (dy >= THRESH) shoot();
  });
  function release() { drag = false; knob.classList.remove('grab'); }
  knob.addEventListener('pointerup', release);
  knob.addEventListener('pointercancel', release);
  knob.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    e.preventDefault();
    if (unlocked()) return;
    x = RX + 6; y = RY + 80; draw(); shoot();
  });

  draw();
  if (!unlocked()) start();
})();
</script>
</body>
</html>