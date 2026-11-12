import type { FilterDef } from '@/types';

export const FILTERS: FilterDef[] = [
  { id: 'original', name: 'Original', css: 'none' },
  { id: 'bw', name: 'B&W', css: 'grayscale(100%) contrast(1.1)' },
  { id: 'sepia', name: 'Sepia', css: 'sepia(80%) contrast(1.05) brightness(1.05)' },
  { id: 'vintage', name: 'Vintage', css: 'sepia(40%) contrast(1.1) brightness(0.95) saturate(1.3) hue-rotate(-10deg)' },
  { id: 'warm', name: 'Warm', css: 'brightness(1.05) saturate(1.3) hue-rotate(-15deg) contrast(1.05)' },
  { id: 'cool', name: 'Cool', css: 'brightness(1.02) saturate(1.2) hue-rotate(15deg) contrast(1.05)' },
  { id: 'fade', name: 'Fade', css: 'contrast(0.85) brightness(1.1) saturate(0.7)' },
];
