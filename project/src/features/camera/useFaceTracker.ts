import { useEffect, useRef, useState, type RefObject } from 'react';
import type { FaceLandmarker, NormalizedLandmark } from '@mediapipe/tasks-vision';

/**
 * Jumlah wajah maksimal yang dilacak sekaligus.
 * 5 = semua orang dalam frame kena efek.
 */
const MAX_FACES = 5;

/**
 * Throttle deteksi wajah: hanya proses tiap N milidetik.
 * 100ms ≈ 10fps deteksi — cukup halus untuk stiker wajah,
 * jauh lebih ringan dari 60fps. Naikkan ke 150 kalau masih berat.
 */
const DETECT_INTERVAL_MS = 100;

/**
 * Lokasi file MediaPipe. Default: diambil dari internet (CDN) saat efek pertama dipakai.
 *
 * Kalau photobooth harus bisa jalan TANPA internet:
 *  1. salin folder  node_modules/@mediapipe/tasks-vision/wasm  ->  public/mediapipe/wasm
 *  2. unduh model dari MODEL_URL di bawah, simpan sebagai  public/mediapipe/face_landmarker.task
 *  3. ganti dua konstanta ini jadi '/mediapipe/wasm' dan '/mediapipe/face_landmarker.task'
 */
const WASM_URL = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm';
const MODEL_URL =
  'https://storage.googleapis.com/mediapipe-models/face_landmarker/face_landmarker/float16/1/face_landmarker.task';

export type TrackerStatus = 'idle' | 'loading' | 'ready' | 'error';

/** Satu wajah yang terlacak. `id` tetap selama wajah itu masih di layar. */
export interface TrackedFace {
  id: number;
  points: NormalizedLandmark[];
}

let landmarkerPromise: Promise<FaceLandmarker> | null = null;

/** Dimuat sekali saja dan dipakai ulang. Library baru diunduh saat efek pertama dipilih. */
function loadFaceLandmarker(): Promise<FaceLandmarker> {
  if (!landmarkerPromise) {
    landmarkerPromise = (async () => {
      const { FaceLandmarker, FilesetResolver } = await import('@mediapipe/tasks-vision');
      const fileset = await FilesetResolver.forVisionTasks(WASM_URL);
      const make = (delegate: 'GPU' | 'CPU') =>
        FaceLandmarker.createFromOptions(fileset, {
          baseOptions: { modelAssetPath: MODEL_URL, delegate },
          runningMode: 'VIDEO',
          numFaces: MAX_FACES,
          // Hanya landmark yang dipakai — tidak perlu blendshapes / transformations
          outputFaceBlendshapes: false,
          outputFacialTransformationMatrixes: false,
        });
      try {
        return await make('GPU');
      } catch {
        return await make('CPU'); // cadangan kalau GPU tidak tersedia
      }
    })();
    landmarkerPromise.catch(() => {
      landmarkerPromise = null; // boleh dicoba lagi
    });
  }
  return landmarkerPromise;
}

/* =====================================================================
 *  EFEK KACAMATA + KUMIS (gambar PNG ditempel mengikuti titik wajah)
 * ===================================================================== */

/**
 * Memuat gambar dari folder public/. Contoh: muatGambar('effects/kacamata.png').
 * BASE_URL ikut '/photobooth/', jadi jangan pakai '/' di depan src.
 */
export const muatGambar = (src: string) =>
  new Promise<HTMLImageElement>((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error('Gagal memuat gambar: ' + src));
    img.src = import.meta.env.BASE_URL + src;
  });

/** Menggambar `img` dengan titik tengah di (cx, cy), selebar `lebar`, diputar `sudut` (radian). */
export function tempel(
  ctx: CanvasRenderingContext2D,
  img: HTMLImageElement,
  cx: number,
  cy: number,
  lebar: number,
  sudut: number
) {
  const tinggi = (lebar * img.naturalHeight) / img.naturalWidth;
  ctx.save();
  ctx.translate(cx, cy);
  ctx.rotate(sudut);
  ctx.drawImage(img, -lebar / 2, -tinggi / 2, lebar, tinggi);
  ctx.restore();
}

/**
 * Menempel kacamata dan kumis di satu wajah.
 * `toScreen` mengubah titik wajah (0..1) jadi koordinat piksel di canvas
 * (sudah termasuk crop object-cover dan mirror kalau ada).
 */
export function gambarKacamataKumis(
  ctx: CanvasRenderingContext2D,
  face: { points: { x: number; y: number }[] },
  toScreen: (p: { x: number; y: number }) => { x: number; y: number },
  kacamata: HTMLImageElement,
  kumis: HTMLImageElement
) {
  const P = (i: number) => toScreen(face.points[i]);

  // urutkan kiri-kanan di layar, supaya aman walau video di-mirror
  const a = P(33);
  const b = P(263);
  const [kiri, kanan] = a.x < b.x ? [a, b] : [b, a];
  const jarakMata = Math.hypot(kanan.x - kiri.x, kanan.y - kiri.y);
  const sudut = Math.atan2(kanan.y - kiri.y, kanan.x - kiri.x);

  // kacamata: di tengah kedua mata
  tempel(
    ctx,
    kacamata,
    (kiri.x + kanan.x) / 2,
    (kiri.y + kanan.y) / 2 + jarakMata * 0.03,
    jarakMata * 2.0,
    sudut
  );

  // kumis: di antara dasar hidung dan bibir atas
  const hidung = P(164);
  const bibir = P(0);
  tempel(
    ctx,
    kumis,
    (hidung.x + bibir.x) / 2,
    hidung.y + (bibir.y - hidung.y) * 0.4,
    jarakMata * 1.0,
    sudut
  );
}

/* =====================================================================
 *  PELACAK WAJAH
 * ===================================================================== */

/**
 * Melacak semua wajah dari <video> selama `enabled` = true.
 * Hasilnya (titik wajah ternormalisasi 0..1, tidak dicerminkan) ada di `facesRef.current`,
 * berupa array kosong kalau tidak ada wajah.
 */
export function useFaceTracker(videoRef: RefObject<HTMLVideoElement>, enabled: boolean) {
  const facesRef = useRef<TrackedFace[]>([]);
  const [status, setStatus] = useState<TrackerStatus>('idle');

  useEffect(() => {
    if (!enabled) {
      facesRef.current = [];
      setStatus('idle');
      return;
    }

    let cancelled = false;
    let raf = 0;
    let lastTime = -1;
    let lastDetectAt = 0;   // timestamp deteksi terakhir (throttle)
    let warned = false;
    let nextId = 1;
    let tracks: TrackedFace[] = [];
    setStatus('loading');

    loadFaceLandmarker()
      .then((landmarker) => {
        if (cancelled) return;
        setStatus('ready');

        const loop = (now: number) => {
          const video = videoRef.current;
          // Throttle: skip frame jika belum waktunya
          if (
            video &&
            video.readyState >= 2 &&
            video.currentTime !== lastTime &&
            now - lastDetectAt >= DETECT_INTERVAL_MS
          ) {
            lastTime = video.currentTime;
            lastDetectAt = now;
            try {
              const res = landmarker.detectForVideo(video, now);
              const detected = res.faceLandmarks.filter((f) => f.length > 455);

              // Cocokkan tiap wajah baru dengan wajah frame sebelumnya (paling dekat berdasarkan
              // ujung hidung), supaya id-nya tetap dan gerakannya bisa dihaluskan.
              const used = new Set<number>();
              const next: TrackedFace[] = [];
              for (const cur of detected) {
                let best = -1;
                let bestD = 0.12;
                tracks.forEach((prev, idx) => {
                  if (used.has(idx)) return;
                  const d = Math.hypot(prev.points[1].x - cur[1].x, prev.points[1].y - cur[1].y);
                  if (d < bestD) {
                    bestD = d;
                    best = idx;
                  }
                });
                if (best >= 0) {
                  used.add(best);
                  const prev = tracks[best];
                  next.push({
                    id: prev.id,
                    // Smooth lerp lebih kuat (0.5) agar stiker tidak terlalu gemetar
                    points: cur.map((p, i) => ({
                      ...p,
                      x: prev.points[i].x + (p.x - prev.points[i].x) * 0.5,
                      y: prev.points[i].y + (p.y - prev.points[i].y) * 0.5,
                    })),
                  });
                } else {
                  next.push({ id: nextId++, points: cur });
                }
              }
              tracks = next;
              facesRef.current = next;
            } catch (err) {
              if (!warned) {
                warned = true;
                console.error('[FaceTracker] gagal mendeteksi wajah:', err);
              }
            }
          }
          raf = requestAnimationFrame(loop);
        };
        raf = requestAnimationFrame(loop);
      })
      .catch((err) => {
        console.error('[FaceTracker] gagal memuat model/library:', err);
        if (!cancelled) setStatus('error');
      });

    return () => {
      cancelled = true;
      cancelAnimationFrame(raf);
      facesRef.current = [];
    };
  }, [enabled, videoRef]);

  return { facesRef, status };
}