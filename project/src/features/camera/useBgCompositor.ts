import { useEffect, useRef, useCallback, type RefObject } from 'react';
import { type BgEffectId, BG_EFFECTS, drawGradientBg } from '@/features/camera/bgEffects';

/**
 * Hook yang mengompositkan video kamera + background baru ke dalam sebuah canvas.
 * Canvas ini yang ditampilkan ke user (menggantikan <video> langsung).
 *
 * Kalau bgEffect = 'bg-none', canvas hanya mirror video tanpa modifikasi.
 * Kalau bgEffect lain, canvas mengganti background dengan warna/gambar/blur.
 *
 * Returns `composeFrame` — fungsi untuk render satu frame ke canvas capture.
 */
export function useBgCompositor(
  videoRef: RefObject<HTMLVideoElement>,
  canvasRef: RefObject<HTMLCanvasElement>,
  maskRef: RefObject<ImageData | null>,
  bgEffect: BgEffectId,
  filterCss: string
) {
  const rafRef = useRef(0);
  // Canvas blur background (offscreen, dibuat ulang kalau ukuran berubah)
  const blurCanvasRef = useRef<HTMLCanvasElement | null>(null);
  const maskCanvasRef = useRef<HTMLCanvasElement | null>(null);

  // Loop render utama
  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let lastW = 0, lastH = 0;

    const draw = () => {
      const video = videoRef.current;
      if (!video || video.readyState < 2) {
        rafRef.current = requestAnimationFrame(draw);
        return;
      }

      const vw = video.videoWidth;
      const vh = video.videoHeight;

      // Resize canvas kalau perlu
      if (canvas.width !== vw || canvas.height !== vh) {
        canvas.width = vw;
        canvas.height = vh;
      }

      ctx.save();
      // Mirror (selfie)
      ctx.translate(vw, 0);
      ctx.scale(-1, 1);

      if (bgEffect === 'bg-none') {
        // Langsung gambar video + filter CSS
        ctx.filter = filterCss !== 'none' ? filterCss : 'none';
        ctx.drawImage(video, 0, 0, vw, vh);
      } else {
        const def = BG_EFFECTS.find(e => e.id === bgEffect);
        const mask = maskRef.current;

        if (!mask) {
          // Mask belum siap, tampilkan video biasa dulu
          ctx.filter = filterCss !== 'none' ? filterCss : 'none';
          ctx.drawImage(video, 0, 0, vw, vh);
        } else {
          // ---- Compose: background + masked person ----
          ctx.filter = 'none';

          if (def?.blur) {
            // Blur: gambar video blur sebagai BG, lalu tempel orang asli
            ctx.filter = `blur(${def.blur}px)`;
            ctx.drawImage(video, -def.blur * 2, -def.blur * 2, vw + def.blur * 4, vh + def.blur * 4);
            ctx.filter = 'none';
          } else if (def?.gradient) {
            // Solid color / gradient
            drawGradientBg(ctx, vw, vh, def.gradient);
          }

          // Tempel orang di atas background menggunakan mask
          applyPersonMask(ctx, video, mask, vw, vh);

          // Apply color filter di atas semua
          if (filterCss !== 'none') {
            ctx.filter = filterCss;
            ctx.globalCompositeOperation = 'source-atop';
            ctx.drawImage(canvas, 0, 0);
            ctx.globalCompositeOperation = 'source-over';
            ctx.filter = 'none';
          }
        }
      }

      ctx.restore();
      rafRef.current = requestAnimationFrame(draw);
    };

    rafRef.current = requestAnimationFrame(draw);
    return () => cancelAnimationFrame(rafRef.current);
  }, [videoRef, canvasRef, maskRef, bgEffect, filterCss]);

  /**
   * Render satu frame untuk capture foto.
   * Harus dipanggil setelah flash (video masih hidup).
   */
  const composeFrame = useCallback((): HTMLCanvasElement | null => {
    const video = videoRef.current;
    if (!video || !video.videoWidth) return null;

    const out = document.createElement('canvas');
    out.width = video.videoWidth;
    out.height = video.videoHeight;
    const ctx = out.getContext('2d');
    if (!ctx) return null;

    const vw = out.width;
    const vh = out.height;

    ctx.save();
    ctx.translate(vw, 0);
    ctx.scale(-1, 1);

    if (bgEffect === 'bg-none') {
      ctx.filter = filterCss !== 'none' ? filterCss : 'none';
      ctx.drawImage(video, 0, 0, vw, vh);
    } else {
      const def = BG_EFFECTS.find(e => e.id === bgEffect);
      const mask = maskRef.current;
      ctx.filter = 'none';

      if (!mask) {
        ctx.filter = filterCss !== 'none' ? filterCss : 'none';
        ctx.drawImage(video, 0, 0, vw, vh);
      } else {
        if (def?.blur) {
          ctx.filter = `blur(${def.blur}px)`;
          ctx.drawImage(video, -def.blur * 2, -def.blur * 2, vw + def.blur * 4, vh + def.blur * 4);
          ctx.filter = 'none';
        } else if (def?.gradient) {
          drawGradientBg(ctx, vw, vh, def.gradient);
        }
        applyPersonMask(ctx, video, mask, vw, vh);
      }
    }

    ctx.restore();
    return out;
  }, [videoRef, maskRef, bgEffect, filterCss]);

  return { composeFrame };
}

/**
 * Tempel piksel "orang" dari video ke ctx menggunakan mask segmentasi.
 * Mask diupscale ke dimensi video dengan createImageBitmap.
 */
function applyPersonMask(
  ctx: CanvasRenderingContext2D,
  video: HTMLVideoElement,
  mask: ImageData,
  vw: number,
  vh: number
) {
  // Buat canvas sementara untuk video frame
  const videoCanvas = document.createElement('canvas');
  videoCanvas.width = vw;
  videoCanvas.height = vh;
  const vCtx = videoCanvas.getContext('2d')!;
  vCtx.drawImage(video, 0, 0, vw, vh);
  const videoFrame = vCtx.getImageData(0, 0, vw, vh);

  // Buat canvas mask yang di-upscale ke ukuran video
  const maskCanvas = document.createElement('canvas');
  maskCanvas.width = mask.width;
  maskCanvas.height = mask.height;
  const mCtx = maskCanvas.getContext('2d')!;
  mCtx.putImageData(mask, 0, 0);

  // Gambar mask (upscaled) ke canvas temp
  const compositeCanvas = document.createElement('canvas');
  compositeCanvas.width = vw;
  compositeCanvas.height = vh;
  const cCtx = compositeCanvas.getContext('2d')!;

  // Gambar video dulu
  cCtx.drawImage(videoCanvas, 0, 0);
  // Clip dengan mask: destination-in = hanya piksel yg ada di mask yang tersisa
  cCtx.globalCompositeOperation = 'destination-in';
  cCtx.drawImage(maskCanvas, 0, 0, vw, vh);
  cCtx.globalCompositeOperation = 'source-over';

  // Tempel hasilnya di atas background
  ctx.drawImage(compositeCanvas, 0, 0);
}

