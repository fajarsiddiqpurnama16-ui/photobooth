import type { Template } from '@/types';

/** Rasio lebar/tinggi slot ke-index (pakai photoSlots milik Template). */
export function slotAspect(t: Template, index: number): number {
  const s = t.photoSlots[index] ?? t.photoSlots[0];
  return s.width / s.height;
}

/**
 * Kotak panduan di atas video (video memakai object-contain).
 * Taruh di wrapper yang sama dengan <video> (wrapper harus `relative`).
 * videoAspect = videoWidth / videoHeight dari kamera.
 */
export function SlotGuide({ aspect, videoAspect = 16 / 9 }: { aspect: number; videoAspect?: number }) {
  // Bagian dari video yang dipakai slot: penuh kalau slot lebih lebar dari video, selebihnya menyusut
  const k = Math.min(1, aspect / videoAspect);
  return (
    <div className="pointer-events-none absolute inset-0" style={{ containerType: 'size' }}>
      <div
        className="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rounded-lg border-2 border-white/90"
        style={{
          aspectRatio: aspect,
          width: `calc(min(100cqw, 100cqh * ${videoAspect}) * ${k})`,
          boxShadow: '0 0 0 9999px rgba(0,0,0,.45)',
        }}
      />
    </div>
  );
}
