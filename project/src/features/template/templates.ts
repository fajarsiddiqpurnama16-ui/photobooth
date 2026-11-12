import type { Template } from '@/types';

// ---- Strip Templates ----

function stripTemplate(id: string, name: string, count: number, accent: string, bg: string, label: string): Template {
  const slotW = 360;
  const slotH = 240;
  const padding = 24;
  const topPad = 36;
  const footerH = 64;
  const gap = 14;
  const W = slotW + padding * 2;
  const H = topPad + slotH * count + gap * (count - 1) + footerH + padding;
  const slots = Array.from({ length: count }, (_, i) => ({
    x: padding,
    y: topPad + i * (slotH + gap),
    width: slotW,
    height: slotH,
    borderRadius: 4,
  }));
  return {
    id,
    name,
    category: 'strip',
    width: W,
    height: H,
    photoCount: count,
    background: { type: 'solid', color: bg },
    photoSlots: slots,
    photoBorder: { color: bg, width: 1, padding: 0 },
    accent,
    text: [
      { text: label, x: W / 2, y: H - footerH / 2 - 4, fontSize: 15, color: accent, fontFamily: 'sans-serif', fontWeight: '600', align: 'center', letterSpacing: 4 },
    ],
  };
}

export const classic3Strip = stripTemplate('classic3', 'Classic 3 Strip', 3, '#1a1a1a', '#ffffff', 'PHOTOBOOTH');
export const classic4Strip = stripTemplate('classic4', 'Classic 4 Strip', 4, '#1a1a1a', '#ffffff', 'PHOTOBOOTH');

export const vintage3Strip: Template = {
  id: 'vintage3',
  name: 'Vintage',
  category: 'strip',
  width: 420,
  height: 980,
  photoCount: 3,
  background: { type: 'gradient', colors: ['#e8dcc8', '#d4c5a9'], direction: 'vertical' },
  photoSlots: [
    { x: 36, y: 48, width: 348, height: 234, borderRadius: 2 },
    { x: 36, y: 308, width: 348, height: 234, borderRadius: 2 },
    { x: 36, y: 568, width: 348, height: 234, borderRadius: 2 },
  ],
  photoBorder: { color: '#3a3028', width: 1, padding: 6 },
  decorations: [
    { type: 'border-inset', color: '#3a3028', strokeWidth: 2, width: 12, layer: 'back' },
    { type: 'corner', x: 18, y: 18, width: 22, color: '#3a3028', strokeWidth: 2.5, layer: 'front' },
    { type: 'corner', x: 402, y: 18, width: 22, color: '#3a3028', strokeWidth: 2.5, layer: 'front' },
    { type: 'line', x: 36, y: 840, x2: 384, y2: 840, color: '#3a3028', strokeWidth: 1.5, layer: 'front' },
    { type: 'heart', x: 170, y: 850, width: 36, height: 36, color: '#b04a3a', layer: 'front' },
  ],
  text: [
    { text: 'SWEET MEMORIES', x: 210, y: 920, fontSize: 14, color: '#3a3028', fontWeight: '600', letterSpacing: 3, align: 'center' },
    { text: '★  EST. 2026  ★', x: 210, y: 948, fontSize: 11, color: '#7a6a55', letterSpacing: 2, align: 'center' },
  ],
  accent: '#3a3028',
};

export const retro3Strip: Template = {
  id: 'retro3',
  name: 'Retro',
  category: 'strip',
  width: 420,
  height: 980,
  photoCount: 3,
  background: { type: 'gradient', colors: ['#1a1a2e', '#16213e'], direction: 'vertical' },
  photoSlots: [
    { x: 36, y: 48, width: 348, height: 234, borderRadius: 8 },
    { x: 36, y: 308, width: 348, height: 234, borderRadius: 8 },
    { x: 36, y: 568, width: 348, height: 234, borderRadius: 8 },
  ],
  photoBorder: { color: '#e94560', width: 1, padding: 6 },
  decorations: [
    { type: 'line', x: 0, y: 830, x2: 420, y2: 830, color: '#e94560', strokeWidth: 2, layer: 'front' },
    { type: 'dot', x: 60, y: 860, radius: 4, color: '#e94560', layer: 'front' },
    { type: 'dot', x: 210, y: 860, radius: 4, color: '#0f3460', layer: 'front' },
    { type: 'dot', x: 360, y: 860, radius: 4, color: '#e94560', layer: 'front' },
    { type: 'star', x: 180, y: 880, width: 24, height: 24, color: '#e94560', layer: 'front' },
  ],
  text: [
    { text: 'NEON NIGHTS', x: 210, y: 920, fontSize: 18, color: '#e94560', fontWeight: '700', letterSpacing: 6, align: 'center' },
  ],
  accent: '#e94560',
};

export const cute3Strip: Template = {
  id: 'cute3',
  name: 'Cute',
  category: 'strip',
  width: 420,
  height: 980,
  photoCount: 3,
  background: { type: 'gradient', colors: ['#fff0f6', '#ffe0ec'], direction: 'vertical' },
  photoSlots: [
    { x: 36, y: 56, width: 348, height: 234, borderRadius: 24 },
    { x: 36, y: 312, width: 348, height: 234, borderRadius: 24 },
    { x: 36, y: 568, width: 348, height: 234, borderRadius: 24 },
  ],
  photoBorder: { color: '#ffffff', width: 1, padding: 8 },
  decorations: [
    { type: 'heart', x: 30, y: 40, width: 28, height: 28, color: '#ff8fab', layer: 'back' },
    { type: 'heart', x: 370, y: 820, width: 28, height: 28, color: '#ff8fab', layer: 'back' },
    { type: 'star', x: 350, y: 30, width: 20, height: 20, color: '#ffc2d1', layer: 'back' },
    { type: 'dot', x: 50, y: 850, radius: 6, color: '#ffc2d1', layer: 'back' },
    { type: 'dot', x: 380, y: 50, radius: 5, color: '#ffc2d1', layer: 'back' },
    { type: 'line', x: 36, y: 842, x2: 384, y2: 842, color: '#ff8fab', strokeWidth: 2, layer: 'front' },
  ],
  text: [
    { text: 'love you ♡', x: 210, y: 905, fontSize: 16, color: '#d6336c', fontWeight: '500', letterSpacing: 1, align: 'center' },
    { text: '— photobooth —', x: 210, y: 940, fontSize: 11, color: '#ff8fab', letterSpacing: 2, align: 'center' },
  ],
  accent: '#d6336c',
};

// ---- Single Templates ----

export const minimalSingle: Template = {
  id: 'minimal',
  name: 'Minimal',
  category: 'single',
  width: 540,
  height: 720,
  photoCount: 1,
  background: { type: 'solid', color: '#f5f5f4' },
  photoSlots: [{ x: 60, y: 60, width: 420, height: 560, borderRadius: 2 }],
  photoBorder: { color: '#ffffff', width: 1, padding: 0 },
  decorations: [
    { type: 'line', x: 60, y: 660, x2: 480, y2: 660, color: '#d6d3d1', strokeWidth: 1, layer: 'front' },
  ],
  text: [
    { text: 'PHOTOBOOTH', x: 270, y: 690, fontSize: 11, color: '#a8a29e', fontWeight: '500', letterSpacing: 6, align: 'center' },
  ],
  accent: '#1c1917',
};

export const polaroidSingle: Template = {
  id: 'polaroid',
  name: 'Polaroid',
  category: 'single',
  width: 540,
  height: 680,
  photoCount: 1,
  background: { type: 'solid', color: '#fafaf9' },
  photoSlots: [{ x: 54, y: 54, width: 432, height: 432, borderRadius: 2 }],
  photoBorder: { color: '#fafaf9', width: 1, padding: 0 },
  decorations: [
    { type: 'tape', x: 200, y: 28, width: 140, height: 28, color: 'rgba(180,170,150,0.45)', layer: 'back' },
  ],
  text: [
    { text: 'a moment worth keeping', x: 270, y: 560, fontSize: 16, color: '#78716c', fontWeight: '400', letterSpacing: 1, align: 'center' },
    { text: '— 2026 —', x: 270, y: 620, fontSize: 12, color: '#a8a29e', letterSpacing: 4, align: 'center' },
  ],
  accent: '#44403c',
};

// ---- Collage Templates ----

export const collage4: Template = {
  id: 'collage',
  name: 'Collage',
  category: 'grid',
  width: 720,
  height: 720,
  photoCount: 4,
  background: { type: 'solid', color: '#1a1a1a' },
  photoSlots: [
    { x: 30, y: 30, width: 320, height: 320, borderRadius: 4 },
    { x: 370, y: 30, width: 320, height: 320, borderRadius: 4 },
    { x: 30, y: 370, width: 320, height: 320, borderRadius: 4 },
    { x: 370, y: 370, width: 320, height: 320, borderRadius: 4 },
  ],
  photoBorder: { color: '#1a1a1a', width: 1, padding: 0 },
  decorations: [],
  text: [],
  accent: '#ffffff',
};

export const allTemplates: Template[] = [
  classic3Strip,
  classic4Strip,
  minimalSingle,
  polaroidSingle,
  vintage3Strip,
  retro3Strip,
  cute3Strip,
  collage4,
];

export const categories = [
  { id: 'all', name: 'All' },
  { id: 'strip', name: 'Strips' },
  { id: 'single', name: 'Single' },
  { id: 'grid', name: 'Grid' },
  { id: 'creative', name: 'Creative' },
] as const;
