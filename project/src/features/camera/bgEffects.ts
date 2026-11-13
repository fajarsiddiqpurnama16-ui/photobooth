/**
 * Definisi efek background (body segmentation).
 * Background diganti dengan warna/gambar/blur.
 */

export type BgEffectId =
  | 'bg-none'
  | 'bg-blur'
  | 'bg-white'
  | 'bg-black'
  | 'bg-sky'
  | 'bg-milksky'
  | 'bg-sunset'
  | 'bg-neon'
  | 'bg-aesthetic';

export interface BgEffectDef {
  id: BgEffectId;
  name: string;
  emoji: string;
  /** Kalau ada, gambar ini dimuat sebagai background */
  imageUrl?: string;
  /** CSS gradient / warna solid sebagai fallback */
  gradient?: string;
  /** Blur background asli (px) alih-alih ganti warna */
  blur?: number;
}

export const BG_EFFECTS: BgEffectDef[] = [
  {
    id: 'bg-none',
    name: 'Background Asli',
    emoji: '🚫',
  },
  {
    id: 'bg-blur',
    name: 'Aesthetic Blur',
    emoji: '🌫️',
    blur: 18,
  },
  {
    id: 'bg-white',
    name: 'Pure White',
    emoji: '⬜',
    gradient: '#ffffff',
  },
  {
    id: 'bg-black',
    name: 'Pure Black',
    emoji: '⬛',
    gradient: '#000000',
  },
  {
    id: 'bg-sky',
    name: 'Blue Sky',
    emoji: '🌤️',
    gradient: 'linear-gradient(180deg, #87CEEB 0%, #B0E0FF 50%, #E8F4FD 100%)',
  },
  {
    id: 'bg-milksky',
    name: 'Milk Sky',
    emoji: '🥛',
    gradient: 'linear-gradient(180deg, #d4e8f0 0%, #e8f4f8 40%, #f5f0e8 100%)',
  },
  {
    id: 'bg-sunset',
    name: 'Sunset',
    emoji: '🌅',
    gradient: 'linear-gradient(180deg, #ff6b6b 0%, #ffa07a 30%, #ffd700 70%, #87CEEB 100%)',
  },
  {
    id: 'bg-neon',
    name: 'Neon Night',
    emoji: '🌃',
    gradient: 'linear-gradient(135deg, #0a0a2e 0%, #1a0a3e 30%, #0d1b4b 60%, #001233 100%)',
  },
  {
    id: 'bg-aesthetic',
    name: 'Aesthetic',
    emoji: '🌸',
    gradient: 'linear-gradient(135deg, #fce4ec 0%, #f8bbd9 25%, #e1bee7 50%, #bbdefb 75%, #e0f2fe 100%)',
  },
];

export function isBgEffect(id: string): id is BgEffectId {
  return id.startsWith('bg-');
}

/** Render gradient/warna ke canvas (helper) */
export function drawGradientBg(
  ctx: CanvasRenderingContext2D,
  w: number,
  h: number,
  gradient: string
) {
  if (gradient.startsWith('linear-gradient')) {
    // Parse linear-gradient sederhana
    const match = gradient.match(/linear-gradient\((.+)\)/);
    if (match) {
      const parts = match[1].split(/,(?![^(]*\))/);
      const anglePart = parts[0].trim();
      const stops = parts.slice(1);

      let x0 = 0, y0 = 0, x1 = 0, y1 = h;
      if (anglePart.includes('180deg')) { x0 = 0; y0 = 0; x1 = 0; y1 = h; }
      else if (anglePart.includes('135deg')) { x0 = 0; y0 = 0; x1 = w; y1 = h; }
      else if (anglePart.includes('90deg')) { x0 = 0; y0 = h / 2; x1 = w; y1 = h / 2; }

      const grad = ctx.createLinearGradient(x0, y0, x1, y1);
      stops.forEach((stop) => {
        const m = stop.trim().match(/^(#[0-9a-fA-F]+|rgba?\([^)]+\)|[a-z]+)\s+([\d.]+%)/);
        if (m) grad.addColorStop(parseFloat(m[2]) / 100, m[1]);
      });
      ctx.fillStyle = grad;
    }
  } else {
    ctx.fillStyle = gradient;
  }
  ctx.fillRect(0, 0, w, h);
}

