/**
 * Efek wajah (AR) yang digambar di atas kamera dan ikut tercetak di foto.
 * Sebagian stiker digambar pakai canvas, sebagian (kacamata, kumis) memakai gambar PNG
 * dari folder public/effects/.
 *
 * Semua efek ditempel ke wajah dan digambar sekali per wajah yang terdeteksi.
 *
 * Cara tambah efek baru:
 *  1. tambah id di EffectId dan satu baris di EFFECTS
 *  2. tulis fungsi gambarnya
 *  3. daftarkan di `faceDrawers`
 */
import { drawCustomEffect, isCustomEffect } from './customEffects';

type BuiltinEffectId = 'none' | 'hearts' | 'birds' | 'butterflies' | 'blush' | 'glasses' | 'mustache' | 'disguise';
   export type EffectId = BuiltinEffectId | `custom-${number}`;

export interface EffectDef {
  id: EffectId;
  name: string;
  emoji: string;
}

export const EFFECTS: EffectDef[] = [
  { id: 'none', name: 'Tanpa Efek', emoji: '🚫' },
  { id: 'hearts', name: 'Mahkota Hati', emoji: '💖' },
  { id: 'birds', name: 'Burung', emoji: '🐦' },
  { id: 'butterflies', name: 'Kupu-kupu', emoji: '🦋' },
  { id: 'blush', name: 'Rona Pipi', emoji: '🌸' },
  { id: 'glasses', name: 'Kacamata', emoji: '🕶️' },
  { id: 'mustache', name: 'Kumis', emoji: '🥸' },
  { id: 'disguise', name: 'Kacamata + Kumis', emoji: '😎' },
];

/** Apakah efek ini butuh pelacakan wajah? */
export function needsFaces(id: EffectId): boolean {
  return id !== 'none';
}

export interface Point {
  x: number;
  y: number;
}

/** Mengubah nomor titik wajah MediaPipe jadi koordinat piksel di kanvas tujuan. */
export type PointFn = (index: number) => Point;

/** Satu wajah yang terlacak: id tetap selama wajahnya masih di layar. */
export interface FaceInput {
  id: number;
  pt: PointFn;
}

interface Face {
  pt: PointFn;
  top: Point;
  w: number; // lebar wajah (piksel)
  h: number; // tinggi wajah (piksel)
  right: Point; // arah "kanan" wajah (ikut miring kepala)
  up: Point; // arah "atas" wajah
  roll: number; // sudut miring kepala (radian)
  cheekA: Point;
  cheekB: Point;
}

const dist = (a: Point, b: Point) => Math.hypot(a.x - b.x, a.y - b.y);

function measure(pt: PointFn): Face {
  const top = pt(10); // dahi atas
  const chin = pt(152); // dagu
  const left = pt(234); // sisi wajah kiri
  const rightSide = pt(454); // sisi wajah kanan
  const eyeA = pt(33);
  const eyeB = pt(263);

  let rx = eyeB.x - eyeA.x;
  let ry = eyeB.y - eyeA.y;
  if (rx < 0) {
    rx = -rx;
    ry = -ry;
  }
  const len = Math.hypot(rx, ry) || 1;
  const right = { x: rx / len, y: ry / len };
  const up = { x: right.y, y: -right.x };

  return {
    pt,
    top,
    w: dist(left, rightSide),
    h: dist(top, chin),
    right,
    up,
    roll: Math.atan2(right.y, right.x),
    cheekA: pt(205),
    cheekB: pt(425),
  };
}

/** Geser titik `base` sejauh rightAmt ke kanan wajah dan upAmt ke atas wajah. */
function at(f: Face, base: Point, rightAmt: number, upAmt: number): Point {
  return {
    x: base.x + f.right.x * rightAmt + f.up.x * upAmt,
    y: base.y + f.right.y * rightAmt + f.up.y * upAmt,
  };
}

/** Angka acak tetap (0..1) per indeks, supaya posisi stiker nggak loncat tiap frame. */
function rnd(i: number, salt = 0): number {
  const x = Math.sin(i * 127.1 + salt * 311.7) * 43758.5453;
  return x - Math.floor(x);
}

/* ---------------- Mahkota hati ---------------- */

function heart(ctx: CanvasRenderingContext2D, s: number, c1: string, c2: string) {
  const g = ctx.createLinearGradient(-s, -s, s, s);
  g.addColorStop(0, c1);
  g.addColorStop(1, c2);
  ctx.globalAlpha = 0.93;
  ctx.fillStyle = g;
  ctx.beginPath();
  ctx.moveTo(0, s * 0.9);
  ctx.bezierCurveTo(-s * 1.3, -s * 0.1, -s * 0.6, -s * 1.0, 0, -s * 0.3);
  ctx.bezierCurveTo(s * 0.6, -s * 1.0, s * 1.3, -s * 0.1, 0, s * 0.9);
  ctx.closePath();
  ctx.fill();
  // kilau kecil
  ctx.globalAlpha = 0.55;
  ctx.fillStyle = '#ffffff';
  ctx.beginPath();
  ctx.ellipse(-s * 0.42, -s * 0.3, s * 0.16, s * 0.1, -0.6, 0, Math.PI * 2);
  ctx.fill();
}

function drawHearts(ctx: CanvasRenderingContext2D, f: Face, t: number) {
  const n = 11;
  for (let i = 0; i < n; i++) {
    const u = -1 + (2 * i) / (n - 1); // -1 (kiri) .. 1 (kanan)
    const rightAmt = (u * 0.56 + (rnd(i) - 0.5) * 0.06) * f.w;
    const upAmt =
      f.h * (0.2 * (1 - u * u) - 0.04 + (rnd(i, 1) - 0.5) * 0.05) +
      Math.sin(t * 2 + i) * f.h * 0.008;
    const p = at(f, f.top, rightAmt, upAmt);
    const size = f.w * (0.035 + rnd(i, 2) * 0.03) * (1 + 0.1 * Math.sin(t * 3 + i * 1.3));

    ctx.save();
    ctx.translate(p.x, p.y);
    ctx.rotate(f.roll + (rnd(i, 3) - 0.5) * 0.7);
    if (i % 3 === 0) heart(ctx, size, '#f9a8d4', '#ec4899');
    else heart(ctx, size, '#f472b6', '#db2777');
    ctx.restore();
  }
}

/* ---------------- Burung ---------------- */

function bird(ctx: CanvasRenderingContext2D, s: number, flap: number, dir: number) {
  ctx.save();
  ctx.scale(dir, 1);

  const wing = (alpha: number, color: string, angle: number) => {
    ctx.save();
    ctx.translate(-s * 0.05, -s * 0.05);
    ctx.rotate(angle);
    ctx.globalAlpha = alpha;
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.ellipse(0, -s * 0.5, s * 0.2, s * 0.55, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  };

  wing(0.75, '#0284c7', -0.2 + flap * 0.9); // sayap belakang

  // ekor
  ctx.fillStyle = '#0ea5e9';
  ctx.beginPath();
  ctx.moveTo(-s * 0.45, 0);
  ctx.lineTo(-s * 0.95, -s * 0.18);
  ctx.lineTo(-s * 0.9, s * 0.12);
  ctx.closePath();
  ctx.fill();

  // badan
  ctx.fillStyle = '#38bdf8';
  ctx.beginPath();
  ctx.ellipse(0, 0, s * 0.55, s * 0.32, 0, 0, Math.PI * 2);
  ctx.fill();

  // kepala
  ctx.beginPath();
  ctx.arc(s * 0.5, -s * 0.12, s * 0.22, 0, Math.PI * 2);
  ctx.fill();

  // paruh
  ctx.fillStyle = '#fbbf24';
  ctx.beginPath();
  ctx.moveTo(s * 0.68, -s * 0.17);
  ctx.lineTo(s * 0.93, -s * 0.09);
  ctx.lineTo(s * 0.68, -s * 0.04);
  ctx.closePath();
  ctx.fill();

  // mata
  ctx.fillStyle = '#0f172a';
  ctx.beginPath();
  ctx.arc(s * 0.57, -s * 0.16, s * 0.04, 0, Math.PI * 2);
  ctx.fill();

  wing(1, '#0ea5e9', 0.1 - flap * 0.9); // sayap depan
  ctx.restore();
}

function drawBirds(ctx: CanvasRenderingContext2D, f: Face, t: number) {
  const n = 5;
  for (let i = 0; i < n; i++) {
    const u = -0.85 + (1.7 * i) / (n - 1);
    const rightAmt = u * 0.55 * f.w + Math.sin(t * 1.3 + i * 2) * f.w * 0.04;
    const upAmt = f.h * (0.2 * (1 - u * u) + 0.1 + 0.03 * Math.sin(t * 1.7 + i * 1.9));
    const p = at(f, f.top, rightAmt, upAmt);
    const size = f.w * (0.085 + rnd(i, 4) * 0.03);
    const flap = Math.sin(t * 14 + i * 1.7);

    ctx.save();
    ctx.translate(p.x, p.y);
    ctx.rotate(f.roll * 0.6 + Math.sin(t + i) * 0.12);
    bird(ctx, size, flap, i % 2 === 0 ? 1 : -1);
    ctx.restore();
  }
}

/* ---------------- Kupu-kupu di pipi ---------------- */

function butterfly(ctx: CanvasRenderingContext2D, s: number, flap: number) {
  // sayap (diskalakan horizontal supaya terlihat mengepak)
  ctx.save();
  ctx.scale(Math.max(0.25, Math.abs(flap)), 1);
  const g = ctx.createRadialGradient(0, 0, s * 0.05, 0, 0, s * 0.9);
  g.addColorStop(0, '#fbcfe8');
  g.addColorStop(1, '#ec4899');
  ctx.fillStyle = g;
  ctx.strokeStyle = 'rgba(255,255,255,0.55)';
  ctx.lineWidth = Math.max(1, s * 0.03);
  ctx.globalAlpha = 0.92;
  for (const sx of [-1, 1]) {
    ctx.beginPath();
    ctx.ellipse(sx * s * 0.5, -s * 0.3, s * 0.55, s * 0.5, sx * -0.5, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();
    ctx.beginPath();
    ctx.ellipse(sx * s * 0.4, s * 0.35, s * 0.38, s * 0.34, sx * 0.4, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();
  }
  ctx.restore();

  // badan dan sungut
  ctx.globalAlpha = 1;
  ctx.fillStyle = '#7c2d4a';
  ctx.beginPath();
  ctx.ellipse(0, 0, s * 0.07, s * 0.45, 0, 0, Math.PI * 2);
  ctx.fill();
  ctx.strokeStyle = '#7c2d4a';
  ctx.lineWidth = Math.max(1, s * 0.03);
  ctx.beginPath();
  ctx.moveTo(0, -s * 0.4);
  ctx.quadraticCurveTo(-s * 0.15, -s * 0.7, -s * 0.28, -s * 0.72);
  ctx.moveTo(0, -s * 0.4);
  ctx.quadraticCurveTo(s * 0.15, -s * 0.7, s * 0.28, -s * 0.72);
  ctx.stroke();
}

function drawButterflies(ctx: CanvasRenderingContext2D, f: Face, t: number) {
  const items = [
    { p: f.cheekA, rot: -0.35, phase: 0 },
    { p: f.cheekB, rot: 0.35, phase: 1.7 },
  ];
  for (const it of items) {
    const size = f.w * 0.17 * (1 + 0.04 * Math.sin(t * 2 + it.phase));
    ctx.save();
    ctx.translate(it.p.x, it.p.y);
    ctx.rotate(f.roll + it.rot);
    butterfly(ctx, size, Math.sin(t * 6 + it.phase));
    ctx.restore();
  }
}

/* ---------------- Rona pipi ---------------- */

function drawBlush(ctx: CanvasRenderingContext2D, f: Face) {
  for (const p of [f.cheekA, f.cheekB]) {
    const r = f.w * 0.2;
    const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r);
    g.addColorStop(0, 'rgba(255, 92, 128, 0.42)');
    g.addColorStop(1, 'rgba(255, 92, 128, 0)');
    ctx.fillStyle = g;
    ctx.beginPath();
    ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
    ctx.fill();
  }
}

/* ---------------- Kacamata dan kumis (gambar PNG) ---------------- */

/**
 * File ada di public/effects/. BASE_URL ikut '/photobooth/', jadi tidak pakai '/' di depan.
 * Gambar dimuat sekali saat file ini pertama dipakai, lalu dipakai ulang tiap frame.
 */
function muat(src: string): HTMLImageElement {
  const img = new Image();
  img.onerror = () => console.error('[Efek] gagal memuat gambar:', src);
  img.src = import.meta.env.BASE_URL + src;
  return img;
}

const IMG_KACAMATA = muat('effects/kacamata.png');
const IMG_KUMIS = muat('effects/kumis.png');

/** Menggambar `img` dengan titik tengah di `c`, selebar `lebar`, diputar `sudut` (radian). */
function tempel(ctx: CanvasRenderingContext2D, img: HTMLImageElement, c: Point, lebar: number, sudut: number) {
  if (!img.complete || img.naturalWidth === 0) return; // belum selesai dimuat
  const tinggi = (lebar * img.naturalHeight) / img.naturalWidth;
  ctx.translate(c.x, c.y);
  ctx.rotate(sudut);
  ctx.drawImage(img, -lebar / 2, -tinggi / 2, lebar, tinggi);
}

function drawGlasses(ctx: CanvasRenderingContext2D, f: Face) {
  const a = f.pt(33);
  const b = f.pt(263);
  const jarakMata = dist(a, b);
  const tengah = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
  // sedikit turun dari garis mata (turun = kebalikan "atas" wajah)
  const c = at(f, tengah, 0, -jarakMata * 0.03);

  ctx.save();
  tempel(ctx, IMG_KACAMATA, c, jarakMata * 2.0, f.roll);
  ctx.restore();
}

function drawMustache(ctx: CanvasRenderingContext2D, f: Face) {
  const a = f.pt(33);
  const b = f.pt(263);
  const jarakMata = dist(a, b);
  const hidung = f.pt(164); // dasar hidung
  const bibir = f.pt(0); // bibir atas
  const c = {
    x: hidung.x + (bibir.x - hidung.x) * 0.4,
    y: hidung.y + (bibir.y - hidung.y) * 0.4,
  };

  ctx.save();
  tempel(ctx, IMG_KUMIS, c, jarakMata * 1.0, f.roll);
  ctx.restore();
}

/* ---------------- Pintu masuk ---------------- */

const faceDrawers: Record<Exclude<EffectId, 'none'>, (ctx: CanvasRenderingContext2D, f: Face, t: number) => void> = {
  hearts: drawHearts,
  birds: drawBirds,
  butterflies: drawButterflies,
  blush: (ctx, f) => drawBlush(ctx, f),
  glasses: (ctx, f) => drawGlasses(ctx, f),
  mustache: (ctx, f) => drawMustache(ctx, f),
  disguise: (ctx, f) => {
    drawGlasses(ctx, f);
    drawMustache(ctx, f);
  },
};

/**
 * Gambar efek ke `ctx`.
 * @param faces semua wajah yang terlacak (titik sudah dicerminkan/diskalakan oleh pemanggil)
 * @param time  detik berjalan, dipakai untuk animasi
 */
export function drawEffects(ctx: CanvasRenderingContext2D, effect: EffectId, faces: FaceInput[], time: number) {
  if (effect === 'none') return;

   if (isCustomEffect(effect)) {
     drawCustomEffect(ctx, effect, faces);
     return;
   }

  const draw = faceDrawers[effect];
  for (const fi of faces) {
    const face = measure(fi.pt);
    if (!isFinite(face.w) || face.w < 10) continue;
    ctx.save();
    draw(ctx, face, time + fi.id * 1.7); // geser waktu per wajah supaya gerakannya tidak kembar
    ctx.restore();
  }
}