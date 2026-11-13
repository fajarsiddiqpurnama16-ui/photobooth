import { useEffect, useRef, useState, type RefObject } from 'react';

/**
 * Throttle segmentasi: hanya proses tiap N ms.
 * 80ms ≈ 12fps — cukup halus untuk background replacement.
 */
const SEGMENT_INTERVAL_MS = 80;

const WASM_URL = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm';
const MODEL_URL =
  'https://storage.googleapis.com/mediapipe-models/image_segmenter/selfie_segmenter/float16/latest/selfie_segmenter.tflite';

export type SegmenterStatus = 'idle' | 'loading' | 'ready' | 'error';

let segmenterPromise: Promise<any> | null = null;

function loadSegmenter(): Promise<any> {
  if (!segmenterPromise) {
    segmenterPromise = (async () => {
      const { ImageSegmenter, FilesetResolver } = await import('@mediapipe/tasks-vision');
      const fileset = await FilesetResolver.forVisionTasks(WASM_URL);
      const make = (delegate: 'GPU' | 'CPU') =>
        ImageSegmenter.createFromOptions(fileset, {
          baseOptions: { modelAssetPath: MODEL_URL, delegate },
          runningMode: 'VIDEO',
          outputCategoryMask: true,
          outputConfidenceMasks: false,
        });
      try {
        return await make('GPU');
      } catch {
        return await make('CPU');
      }
    })();
    segmenterPromise.catch(() => {
      segmenterPromise = null;
    });
  }
  return segmenterPromise;
}

/**
 * Mengembalikan mask segmentasi tubuh (orang vs background).
 * `maskRef.current` = ImageData atau null kalau belum ada.
 */
export function useBodySegmenter(
  videoRef: RefObject<HTMLVideoElement>,
  enabled: boolean
) {
  const maskRef = useRef<ImageData | null>(null);
  const [status, setStatus] = useState<SegmenterStatus>('idle');

  useEffect(() => {
    if (!enabled) {
      maskRef.current = null;
      setStatus('idle');
      return;
    }

    let cancelled = false;
    let raf = 0;
    let lastTime = -1;
    let lastSegmentAt = 0;
    setStatus('loading');

    // Canvas kecil untuk baca mask (480x270 sudah cukup akurat, lebih cepat dari resolusi penuh)
    const tmpCanvas = document.createElement('canvas');
    tmpCanvas.width = 480;
    tmpCanvas.height = 270;
    const tmpCtx = tmpCanvas.getContext('2d')!;

    loadSegmenter()
      .then((segmenter) => {
        if (cancelled) return;
        setStatus('ready');

        const loop = (now: number) => {
          const video = videoRef.current;
          if (
            video &&
            video.readyState >= 2 &&
            video.currentTime !== lastTime &&
            now - lastSegmentAt >= SEGMENT_INTERVAL_MS
          ) {
            lastTime = video.currentTime;
            lastSegmentAt = now;
            try {
              // Gambar frame kecil untuk segmentasi
              tmpCtx.drawImage(video, 0, 0, 480, 270);
              const result = segmenter.segmentForVideo(tmpCanvas, now);
              if (result?.categoryMask) {
                // Konversi mask ke ImageData agar bisa dibaca di compositing
                const w = result.categoryMask.width;
                const h = result.categoryMask.height;
                const maskData = result.categoryMask.getAsFloat32Array();
                const imgData = new ImageData(w, h);
                for (let i = 0; i < maskData.length; i++) {
                  // 0 = background, 1 = orang
                  const alpha = maskData[i] > 0.5 ? 255 : 0;
                  imgData.data[i * 4]     = 255;
                  imgData.data[i * 4 + 1] = 255;
                  imgData.data[i * 4 + 2] = 255;
                  imgData.data[i * 4 + 3] = alpha;
                }
                maskRef.current = imgData;
                result.categoryMask.close();
              }
            } catch {
              // abaikan error per-frame
            }
          }
          raf = requestAnimationFrame(loop);
        };
        raf = requestAnimationFrame(loop);
      })
      .catch((err) => {
        console.error('[BodySegmenter] gagal memuat:', err);
        if (!cancelled) setStatus('error');
      });

    return () => {
      cancelled = true;
      cancelAnimationFrame(raf);
      maskRef.current = null;
    };
  }, [enabled, videoRef]);

  return { maskRef, status };
}

