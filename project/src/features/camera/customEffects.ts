import { useEffect, useState } from 'react';
import type { EffectId, FaceInput, PointFn } from './effects';

/**
 * Efek buatan admin (dari panel Admin_effects). Berisi satu atau beberapa lapisan gambar PNG
 * yang ditempel ke titik wajah. Efek bawaan (effects.ts) tetap dipakai seperti biasa.
 */

const API_BASE = 'http://localhost/photobooth/backend/index.php';

export interface EffectLayer {
  image_url: string;
  anchor: string;
  size: number;    // lebar gambar, dalam satuan lebar wajah
  dx: number;      // geser kanan(+)/kiri(-), satuan lebar wajah
  dy: number;      // geser atas(+)/bawah(-), satuan lebar wajah
  rot: number;     // putar tambahan, derajat
  flip: boolean;
  tilt: boolean;   // ikut miring kepala
  opacity: number;
}

export interface CustomEffect {
  id: number;
  name: string;
  icon: string;
  layers: EffectLayer[];
}

/* Titik tempel. SAMA dengan editor di admin (views/effects/form.php), jaga tetap sinkron. */
interface AnchorDef {
  groups: number[][];       // tiap grup = indeks titik wajah yang dirata-rata
  side?: 'kiri' | 'kanan';  // untuk pasangan: sisi kiri/kanan di LAYAR
}

const ANCHORS: Record<string, AnchorDef> = {
  'kepala-atas': { groups: [[10]] },
  dahi: { groups: [[151]] },
  'dahi-kiri': { groups: [[54], [284]], side: 'kiri' },
  'dahi-kanan': { groups: [[54], [284]], side: 'kanan' },
  'antara-mata': { groups: [[168]] },
  'mata-kiri': { groups: [[159, 145], [386, 374]], side: 'kiri' },
  'mata-kanan': { groups: [[159, 145], [386, 374]], side: 'kanan' },
  hidung: { groups: [[1]] },
  mulut: { groups: [[13, 14]] },
  dagu: { groups: [[152]] },
  'pipi-kiri': { groups: [[205], [425]], side: 'kiri' },
  'pipi-kanan': { groups: [[205], [425]], side: 'kanan' },
  'telinga-kiri': { groups: [[234], [454]], side: 'kiri' },
  'telinga-kanan': { groups: [[234], [454]], side: 'kanan' },
};

const registry = new Map<number, CustomEffect>();
const images = new Map<string, HTMLImageElement>();
let loading: Promise<CustomEffect[]> | null = null;

/** Ambil daftar efek dari backend (sekali; pakai force untuk memuat ulang). */
export function loadCustomEffects(force = false): Promise<CustomEffect[]> {
  if (!loading || force) {
    loading = fetch(`${API_BASE}/Effects`, { cache: 'no-store' })
      .then((r) => r.json())
      .then((json) => {
        const list: CustomEffect[] = Array.isArray(json.effects) ? json.effects : [];
        registry.clear();
        for (const fx of list) {
          registry.set(fx.id, fx);
          fx.layers.forEach((l) => getImage(l.image_url)); // muat gambar lebih awal
        }
        return list;
      })
      .catch((err) => {
        console.error('[Efek] gagal memuat efek buatan admin:', err);
        loading = null; // boleh dicoba lagi
        return [];
      });
  }
  return loading;
}

/** Gambar dimuat dengan crossOrigin supaya foto tetap bisa disimpan (canvas tidak "tercemar"). */
function getImage(url: string): HTMLImageElement | null {
  let img = images.get(url);
  if (!img) {
    img = new Image();
    img.crossOrigin = 'anonymous';
    img.onerror = () => console.error('[Efek] gagal memuat gambar:', url);
    img.src = url;
    images.set(url, img);
  }
  return img.complete && img.naturalWidth > 0 ? img : null;
}

export function isCustomEffect(id: string): id is `custom-${number}` {
  return id.startsWith('custom-');
}

function anchorPoint(pt: PointFn, a: AnchorDef) {
  const g = a.groups.map((ids) => {
    let x = 0;
    let y = 0;
    for (const i of ids) {
      const p = pt(i);
      x += p.x;
      y += p.y;
    }
    return { x: x / ids.length, y: y / ids.length };
  });
  if (g.length === 1) return g[0];
  g.sort((p, q) => p.x - q.x);
  return a.side === 'kiri' ? g[0] : g[1];
}

function measure(pt: PointFn) {
  const L = pt(234);
  const R = pt(454);
  const a = pt(33);
  const b = pt(263);
  let rx = b.x - a.x;
  let ry = b.y - a.y;
  if (rx < 0) {
    rx = -rx;
    ry = -ry;
  }
  const len = Math.hypot(rx, ry) || 1;
  const right = { x: rx / len, y: ry / len };
  const up = { x: right.y, y: -right.x };
  return { w: Math.hypot(L.x - R.x, L.y - R.y), right, up, roll: Math.atan2(right.y, right.x) };
}

/** Dipanggil dari drawEffects() di effects.ts untuk id seperti "custom-12". */
export function drawCustomEffect(ctx: CanvasRenderingContext2D, effect: `custom-${number}`, faces: FaceInput[]) {
  const fx = registry.get(Number(effect.slice('custom-'.length)));
  if (!fx) return;

  for (const face of faces) {
    const m = measure(face.pt);
    if (!isFinite(m.w) || m.w < 10) continue;

    for (const L of fx.layers) {
      const img = getImage(L.image_url);
      if (!img) continue; // gambar belum siap atau gagal dimuat: lewati, jangan bikin error

      const base = anchorPoint(face.pt, ANCHORS[L.anchor] ?? ANCHORS['kepala-atas']);
      const r = L.tilt ? m.right : { x: 1, y: 0 };
      const u = L.tilt ? m.up : { x: 0, y: -1 };
      const cx = base.x + (r.x * L.dx + u.x * L.dy) * m.w;
      const cy = base.y + (r.y * L.dx + u.y * L.dy) * m.w;
      const w = L.size * m.w;
      const h = (w * img.naturalHeight) / img.naturalWidth;

      ctx.save();
      ctx.translate(cx, cy);
      ctx.rotate((L.tilt ? m.roll : 0) + (L.rot * Math.PI) / 180);
      if (L.flip) ctx.scale(-1, 1);
      ctx.globalAlpha = L.opacity;
      ctx.drawImage(img, -w / 2, -h / 2, w, h);
      ctx.restore();
    }
  }
}

/** Untuk EffectStrip: daftar efek buatan admin dalam bentuk yang sama dengan EFFECTS. */
export function useCustomEffectDefs(): { id: EffectId; name: string; emoji: string }[] {
  const [list, setList] = useState<CustomEffect[]>(() => Array.from(registry.values()));

  useEffect(() => {
    let alive = true;
    void loadCustomEffects(true).then((l) => { // selalu ambil ulang, supaya efek baru dari admin langsung muncul
      if (alive) setList(l);
    });
    return () => {
      alive = false;
    };
  }, []);

  return list.map((c) => ({ id: `custom-${c.id}` as EffectId, name: c.name, emoji: c.icon }));
}