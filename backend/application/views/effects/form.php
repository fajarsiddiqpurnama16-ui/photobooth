<?php
$isEdit = !empty($fx);
$vName  = $posted ? $posted['name'] : ($isEdit ? $fx->name : '');
$vIcon  = $posted ? $posted['icon'] : ($isEdit ? $fx->icon : '✨');
$vOrder = $posted ? (int) $posted['sort_order'] : ($isEdit ? (int) $fx->sort_order : 0);
$vOn    = $posted ? !empty($posted['is_active']) : ($isEdit ? (bool) $fx->is_active : true);
?>
<style>
.fx-wrap{max-width:1100px;margin:0 auto;padding:16px}
.fx-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.fx-head h2{margin:0;font-size:22px}
.fx-btn{display:inline-block;padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-size:14px;font-weight:600;text-decoration:none;cursor:pointer}
.fx-btn:hover{background:#f1f5f9}
.fx-primary{background:#2563eb;border-color:#2563eb;color:#fff}
.fx-primary:hover{background:#1d4fd8}
.fx-error{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:8px;padding:10px 12px;margin-bottom:14px;font-size:14px;font-weight:600}
.fx-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px;align-items:start}
.fx-sticky{position:sticky;top:12px}
.fx-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:16px}
.fx-card h3{margin:0 0 10px;font-size:16px}
.fx-hint{margin:0 0 10px;color:#64748b;font-size:13px}
.fx-field{display:block;margin-bottom:10px;font-size:13px;font-weight:600}
.fx-field input[type=text],.fx-field input[type=number]{display:block;width:100%;margin-top:4px;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
.fx-two{display:grid;grid-template-columns:90px 1fr 110px;gap:10px}
.fx-layer{border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin-bottom:10px;background:#f8fafc}
.fx-layer-top{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.fx-layer-top img{width:44px;height:44px;object-fit:contain;background:repeating-conic-gradient(#e2e8f0 0 25%,#fff 0 50%) 50%/12px 12px;border-radius:6px}
.fx-layer-top strong{flex:1;font-size:14px}
.fx-tools button{width:28px;height:28px;margin-left:4px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;cursor:pointer}
.fx-row{display:grid;grid-template-columns:120px 1fr 44px;align-items:center;gap:8px;margin-bottom:6px;font-size:13px}
.fx-row select{grid-column:2 / 4;padding:6px;border:1px solid #cbd5e1;border-radius:6px}
.fx-row output{text-align:right;color:#475569;font-variant-numeric:tabular-nums}
.fx-checks{display:flex;gap:16px;flex-wrap:wrap;font-size:13px;margin-top:6px}
.fx-stage{position:relative;aspect-ratio:4/3;background:#0f172a;border-radius:12px;overflow:hidden;margin-bottom:10px}
.fx-stage video,.fx-stage canvas{position:absolute;inset:0;width:100%;height:100%}
.fx-stage video{transform:scaleX(-1);object-fit:fill}
.fx-msg{position:absolute;inset:0;display:grid;place-items:center;padding:20px;text-align:center;color:#cbd5e1;font-size:14px}
#upMsg{margin-left:10px;font-size:13px;color:#dc2626}
@media(max-width:900px){.fx-grid{grid-template-columns:1fr}.fx-sticky{position:static}}
</style>

<form id="fxForm" method="post" action="" class="fx-wrap">
  <div class="fx-head">
    <h2><?= $isEdit ? 'Edit Efek' : 'Efek Baru' ?></h2>
    <a class="fx-btn" href="<?= site_url('Admin_effects') ?>">&larr; Kembali</a>
  </div>

  <?php if (!empty($error)): ?><div class="fx-error" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <input type="hidden" id="csrf" name="<?= htmlspecialchars($csrf['name']) ?>" value="<?= htmlspecialchars($csrf['hash']) ?>">
  <input type="hidden" id="layersInput" name="layers" value="[]">

  <div class="fx-grid">
    <div>
      <div class="fx-card">
        <div class="fx-two">
          <label class="fx-field">Ikon<input type="text" name="icon" maxlength="4" value="<?= htmlspecialchars($vIcon) ?>"></label>
          <label class="fx-field">Nama efek<input type="text" name="name" maxlength="60" required value="<?= htmlspecialchars($vName) ?>" placeholder="Contoh: Kuping Anjing"></label>
          <label class="fx-field">Urutan<input type="number" name="sort_order" value="<?= $vOrder ?>"></label>
        </div>
        <label style="font-size:13px;font-weight:600"><input type="checkbox" name="is_active" value="1" <?= $vOn ? 'checked' : '' ?>> Tampilkan di photobooth</label>
      </div>

      <div class="fx-card">
        <h3>Lapisan gambar</h3>
        <p class="fx-hint">Gunakan PNG transparan. Lapisan paling atas di daftar ini digambar paling belakang. Untuk dua sisi (misal kuping kiri dan kanan) cukup unggah satu gambar, tambahkan dua kali, lalu balik salah satunya.</p>
        <div id="layerList"></div>
        <label class="fx-btn">+ Tambah lapisan<input type="file" id="fileInput" accept="image/png,image/webp,image/gif" hidden></label>
        <span id="upMsg"></span>
      </div>

      <button type="submit" class="fx-btn fx-primary">Simpan Efek</button>
    </div>

    <div class="fx-sticky">
      <div class="fx-card">
        <h3>Pratinjau di wajahmu</h3>
        <div class="fx-stage" id="stage">
          <video id="cam" muted playsinline></video>
          <canvas id="cv"></canvas>
          <div class="fx-msg" id="stageMsg">Nyalakan kamera untuk melihat efek menempel di wajahmu. Geser slider, hasilnya langsung terlihat.</div>
        </div>
        <button type="button" class="fx-btn" id="camBtn">Nyalakan kamera</button>
      </div>
    </div>
  </div>
</form>

<script type="module">
const MP = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.20';
const MODEL = 'https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task';
const UPLOAD_URL = <?= json_encode(site_url('Admin_effects/upload')) ?>;

/* ----- Titik tempel. SAMA dengan customEffects.ts di photobooth, jaga tetap sinkron. ----- */
const ANCHORS = {
  'kepala-atas':   { label: 'Atas kepala',            groups: [[10]] },
  'dahi':          { label: 'Dahi (tengah)',          groups: [[151]] },
  'dahi-kiri':     { label: 'Dahi kiri',              groups: [[54], [284]], side: 'kiri' },
  'dahi-kanan':    { label: 'Dahi kanan',             groups: [[54], [284]], side: 'kanan' },
  'antara-mata':   { label: 'Antara dua mata',        groups: [[168]] },
  'mata-kiri':     { label: 'Mata kiri',              groups: [[159, 145], [386, 374]], side: 'kiri' },
  'mata-kanan':    { label: 'Mata kanan',             groups: [[159, 145], [386, 374]], side: 'kanan' },
  'hidung':        { label: 'Ujung hidung',           groups: [[1]] },
  'mulut':         { label: 'Mulut',                  groups: [[13, 14]] },
  'dagu':          { label: 'Dagu',                   groups: [[152]] },
  'pipi-kiri':     { label: 'Pipi kiri',              groups: [[205], [425]], side: 'kiri' },
  'pipi-kanan':    { label: 'Pipi kanan',             groups: [[205], [425]], side: 'kanan' },
  'telinga-kiri':  { label: 'Samping kepala kiri',    groups: [[234], [454]], side: 'kiri' },
  'telinga-kanan': { label: 'Samping kepala kanan',   groups: [[234], [454]], side: 'kanan' },
};
// "kiri/kanan" = sisi kiri/kanan di LAYAR (video dicerminkan seperti cermin).

function anchorPoint(pt, a) {
  const g = a.groups.map((ids) => {
    let x = 0, y = 0;
    ids.forEach((i) => { const p = pt(i); x += p.x; y += p.y; });
    return { x: x / ids.length, y: y / ids.length };
  });
  if (g.length === 1) return g[0];
  g.sort((p, q) => p.x - q.x);
  return a.side === 'kiri' ? g[0] : g[1];
}

function measure(pt) {
  const L = pt(234), R = pt(454), a = pt(33), b = pt(263);
  let rx = b.x - a.x, ry = b.y - a.y;
  if (rx < 0) { rx = -rx; ry = -ry; }
  const len = Math.hypot(rx, ry) || 1;
  const right = { x: rx / len, y: ry / len };
  const up = { x: right.y, y: -right.x };
  return { w: Math.hypot(L.x - R.x, L.y - R.y), right, up, roll: Math.atan2(right.y, right.x) };
}

const imgs = {};
function getImg(url) {
  let i = imgs[url];
  if (!i) { i = new Image(); i.src = url; imgs[url] = i; }
  return i.complete && i.naturalWidth ? i : null;
}

function drawLayers(ctx, pt, list) {
  const m = measure(pt);
  if (!isFinite(m.w) || m.w < 10) return;
  for (const L of list) {
    const img = getImg(L.url);
    if (!img) continue;
    const base = anchorPoint(pt, ANCHORS[L.anchor] || ANCHORS['kepala-atas']);
    const tilt = L.tilt !== false;
    const r = tilt ? m.right : { x: 1, y: 0 };
    const u = tilt ? m.up : { x: 0, y: -1 };
    const cx = base.x + (r.x * L.dx + u.x * L.dy) * m.w;
    const cy = base.y + (r.y * L.dx + u.y * L.dy) * m.w;
    const w = L.size * m.w, h = (w * img.naturalHeight) / img.naturalWidth;
    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate((tilt ? m.roll : 0) + ((L.rot || 0) * Math.PI) / 180);
    if (L.flip) ctx.scale(-1, 1);
    ctx.globalAlpha = L.opacity == null ? 1 : L.opacity;
    ctx.drawImage(img, -w / 2, -h / 2, w, h);
    ctx.restore();
  }
}

/* ----- Daftar lapisan ----- */
let layers = <?= $layers_json ?>;
const list = document.getElementById('layerList');
const fmt = (n) => String(Math.round(n * 100) / 100);

function layerCard(L, i) {
  const opts = Object.entries(ANCHORS)
    .map(([k, a]) => `<option value="${k}" ${k === L.anchor ? 'selected' : ''}>${a.label}</option>`).join('');
  const slider = (key, label, min, max, step) =>
    `<label class="fx-row"><span>${label}</span><input type="range" data-k="${key}" min="${min}" max="${max}" step="${step}" value="${L[key]}"><output>${fmt(L[key])}</output></label>`;
  return `<div class="fx-layer" data-i="${i}">
    <div class="fx-layer-top">
      <img src="${L.url}" alt=""><strong>Lapisan ${i + 1}</strong>
      <span class="fx-tools">
        <button type="button" data-act="up" title="Pindah ke belakang">↑</button>
        <button type="button" data-act="down" title="Pindah ke depan">↓</button>
        <button type="button" data-act="del" title="Hapus lapisan">✕</button>
      </span>
    </div>
    <label class="fx-row"><span>Tempel di</span><select data-k="anchor">${opts}</select></label>
    ${slider('size', 'Ukuran', 0.05, 1.5, 0.01)}
    ${slider('dx', 'Geser kanan/kiri', -1, 1, 0.01)}
    ${slider('dy', 'Geser atas/bawah', -1, 1, 0.01)}
    ${slider('rot', 'Putar (°)', -180, 180, 1)}
    ${slider('opacity', 'Transparansi', 0.1, 1, 0.05)}
    <div class="fx-checks">
      <label><input type="checkbox" data-k="flip" ${L.flip ? 'checked' : ''}> Balik horizontal</label>
      <label><input type="checkbox" data-k="tilt" ${L.tilt !== false ? 'checked' : ''}> Ikut miring kepala</label>
    </div>
  </div>`;
}

function renderList() {
  list.innerHTML = layers.length ? layers.map(layerCard).join('') : '<p class="fx-hint">Belum ada lapisan. Klik "Tambah lapisan" dan pilih PNG transparan.</p>';
}

list.addEventListener('input', (e) => {
  const t = e.target, card = t.closest('.fx-layer');
  if (!card || !t.dataset.k) return;
  const L = layers[+card.dataset.i];
  if (t.type === 'checkbox') L[t.dataset.k] = t.checked;
  else if (t.type === 'range') { L[t.dataset.k] = parseFloat(t.value); t.nextElementSibling.textContent = fmt(L[t.dataset.k]); }
  else L[t.dataset.k] = t.value;
});

list.addEventListener('click', (e) => {
  const b = e.target.closest('button[data-act]');
  if (!b) return;
  const i = +b.closest('.fx-layer').dataset.i;
  if (b.dataset.act === 'del') layers.splice(i, 1);
  if (b.dataset.act === 'up' && i > 0) [layers[i - 1], layers[i]] = [layers[i], layers[i - 1]];
  if (b.dataset.act === 'down' && i < layers.length - 1) [layers[i + 1], layers[i]] = [layers[i], layers[i + 1]];
  renderList();
});

/* ----- Upload gambar ----- */
const fileInput = document.getElementById('fileInput');
const csrf = document.getElementById('csrf');
const upMsg = document.getElementById('upMsg');

fileInput.addEventListener('change', async () => {
  const f = fileInput.files[0];
  if (!f) return;
  const fd = new FormData();
  fd.append('image', f);
  fd.append(csrf.name, csrf.value);
  upMsg.textContent = 'Mengunggah...';
  try {
    const r = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
    const j = await r.json();
    if (j.csrf) csrf.value = j.csrf; // token CSRF berganti setiap request
    if (!j.ok) throw new Error(j.message || 'Upload gagal');
    layers.push({ image: j.file, url: j.url, anchor: 'kepala-atas', size: 0.4, dx: 0, dy: 0, rot: 0, flip: false, tilt: true, opacity: 1 });
    renderList();
    upMsg.textContent = '';
  } catch (err) {
    upMsg.textContent = err.message;
  }
  fileInput.value = '';
});

document.getElementById('fxForm').addEventListener('submit', () => {
  document.getElementById('layersInput').value = JSON.stringify(layers.map(({ url, ...rest }) => rest));
});

/* ----- Pratinjau kamera ----- */
const video = document.getElementById('cam');
const cv = document.getElementById('cv');
const ctx = cv.getContext('2d');
const stage = document.getElementById('stage');
const msg = document.getElementById('stageMsg');
const camBtn = document.getElementById('camBtn');
let landmarker = null, lm = null, lastT = -1, running = false;

function frame() {
  if (!running) return;
  if (video.readyState >= 2 && video.videoWidth) {
    if (cv.width !== video.videoWidth) {
      cv.width = video.videoWidth;
      cv.height = video.videoHeight;
      stage.style.aspectRatio = cv.width + ' / ' + cv.height;
    }
    ctx.clearRect(0, 0, cv.width, cv.height);
    if (landmarker && video.currentTime !== lastT) {
      lastT = video.currentTime;
      try {
        const r = landmarker.detectForVideo(video, performance.now());
        lm = r.faceLandmarks[0] || null;
      } catch (e) { /* abaikan satu frame */ }
    }
    if (lm && lm.length > 455) {
      drawLayers(ctx, (i) => ({ x: (1 - lm[i].x) * cv.width, y: lm[i].y * cv.height }), layers);
    }
  }
  requestAnimationFrame(frame);
}

camBtn.addEventListener('click', async () => {
  camBtn.disabled = true;
  msg.style.display = 'grid';
  msg.textContent = 'Menyalakan kamera dan memuat model wajah...';
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 960 }, height: { ideal: 720 } }, audio: false });
    video.srcObject = stream;
    await video.play();
    const { FaceLandmarker, FilesetResolver } = await import(MP + '/vision_bundle.mjs');
    const fileset = await FilesetResolver.forVisionTasks(MP + '/wasm');
    const make = (delegate) => FaceLandmarker.createFromOptions(fileset, {
      baseOptions: { modelAssetPath: MODEL, delegate }, runningMode: 'VIDEO', numFaces: 1,
    });
    try { landmarker = await make('GPU'); } catch (e) { landmarker = await make('CPU'); }
    msg.style.display = 'none';
    camBtn.textContent = 'Kamera aktif';
    running = true;
    frame();
  } catch (err) {
    console.error(err);
    msg.textContent = 'Gagal menyalakan kamera atau memuat model wajah. Izinkan akses kamera dan pastikan ada koneksi internet.';
    camBtn.disabled = false;
  }
});

renderList();
</script>