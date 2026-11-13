import { useCallback, useEffect, useRef, useState } from 'react';
import { Camera, Timer, Aperture, Images, RotateCcw, X, Volume2, VolumeX, CheckCircle2, Focus, Zap, Image as ImageIcon } from 'lucide-react';
import type { Template } from '@/types';
import { FILTERS } from '@/features/camera/filters';
import { sound } from '@/features/common/sound';
import { FilterStrip } from '@/features/camera/FilterStrip';
import { EffectStrip } from '@/features/camera/EffectStrip';
import { drawEffects, needsFaces, type EffectId, type FaceInput } from '@/features/camera/effects';
import { useFaceTracker } from '@/features/camera/useFaceTracker';
import { TimerSelector, CountdownOverlay, ShutterFlash } from '@/features/camera/TimerSelector';
import { TemplatePreview } from '@/features/template/TemplatePreview';
import { SlotGuide, slotAspect } from '@/features/camera/slotTools';

interface Props {
  template: Template;
  initialPhotos?: string[];
  initialRetakeIndex?: number | null;
  onCaptureComplete: (photos: string[]) => void;
  onChangeTemplate: () => void;
  onExit: () => void;
}

const NAVY = '#0b4a9e';

/** Ikon kamera digital yang melayang pelan di latar belakang. */
const FLOATERS = [
  { icon: Camera, x: '5%', y: '16%', size: 44, anim: 'pb-float', dur: 7 },
  { icon: Aperture, x: '90%', y: '14%', size: 52, anim: 'pb-spin', dur: 18 },
  { icon: Focus, x: '4%', y: '50%', size: 40, anim: 'pb-pulse', dur: 3 },
  { icon: Zap, x: '93%', y: '48%', size: 36, anim: 'pb-flash', dur: 2.6 },
  { icon: Aperture, x: '6%', y: '78%', size: 56, anim: 'pb-spin', dur: 24 },
  { icon: ImageIcon, x: '91%', y: '76%', size: 42, anim: 'pb-float', dur: 8 },
  { icon: Focus, x: '28%', y: '92%', size: 30, anim: 'pb-pulse', dur: 3.6 },
  { icon: Camera, x: '72%', y: '93%', size: 32, anim: 'pb-float', dur: 6 },
];

const PB_KEYFRAMES = `
@keyframes pb-float { 0%,100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-14px) rotate(4deg); } }
@keyframes pb-spin { to { transform: rotate(360deg); } }
@keyframes pb-pulse { 0%,100% { transform: scale(1); opacity: .25; } 50% { transform: scale(1.25); opacity: .6; } }
@keyframes pb-flash { 0%,60%,100% { opacity: .15; } 70%,80% { opacity: .75; } }
@keyframes pb-blink { 0%,100% { opacity: 1; } 50% { opacity: .15; } }
@keyframes pb-scan { 0% { top: 0%; } 100% { top: 100%; } }
`;

const ROUND_BTN =
  'flex h-14 w-14 items-center justify-center rounded-full bg-white text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5 hover:scale-105 active:translate-y-1 active:shadow-none sm:h-16 sm:w-16';
const LABEL = 'text-[11px] font-semibold tracking-wide text-white drop-shadow';

function generateSamplePhotos(count: number): string[] {
  const result: string[] = [];
  const palettes = [
    ['#3a7bd5', '#3a6073'],
    ['#e96443', '#904e95'],
    ['#00b09b', '#96c93d'],
    ['#8e2de2', '#4a00e0']
  ];
  for (let i = 0; i < count; i++) {
    const c = document.createElement('canvas');
    c.width = 640;
    c.height = 480;
    const ctx = c.getContext('2d')!;
    const pal = palettes[i % palettes.length];
    const grad = ctx.createLinearGradient(0, 0, 640, 480);
    grad.addColorStop(0, pal[0]);
    grad.addColorStop(1, pal[1]);
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, 640, 480);

    ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
    ctx.beginPath();
    ctx.arc(320, 200, 75, 0, Math.PI * 2);
    ctx.fill();

    ctx.beginPath();
    ctx.arc(320, 390, 140, Math.PI, Math.PI * 2);
    ctx.fill();

    ctx.fillStyle = '#222222';
    ctx.beginPath();
    ctx.arc(295, 185, 8, 0, Math.PI * 2);
    ctx.arc(345, 185, 8, 0, Math.PI * 2);
    ctx.fill();

    ctx.strokeStyle = '#222222';
    ctx.lineWidth = 5;
    ctx.beginPath();
    ctx.arc(320, 210, 25, 0.2 * Math.PI, 0.8 * Math.PI);
    ctx.stroke();

    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 24px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('Sample Photo #' + (i + 1), 320, 60);

    result.push(c.toDataURL('image/jpeg', 0.92));
  }
  return result;
}

export function CameraStudio({
  template,
  initialPhotos = [],
  initialRetakeIndex = null,
  onCaptureComplete,
  onChangeTemplate,
  onExit,
}: Props) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const overlayRef = useRef<HTMLCanvasElement>(null);
  const streamRef = useRef<MediaStream | null>(null);
  const [filter, setFilter] = useState('original');
  const [effect, setEffect] = useState<EffectId>('none');
  const [timer, setTimer] = useState(0);

  // Photos array
  const targetCount = template.photoCount || 3;
  const [photos, setPhotos] = useState<string[]>(() => {
    if (initialPhotos && initialPhotos.length > 0) {
      return [...initialPhotos];
    }
    return [];
  });

  // Current slot being targeted (e.g. 0 for photo 1, 1 for photo 2, etc.)
  const [targetSlot, setTargetSlot] = useState<number>(() => {
    if (initialRetakeIndex !== null && initialRetakeIndex !== undefined && initialRetakeIndex >= 0 && initialRetakeIndex < targetCount) {
      return initialRetakeIndex;
    }
    return initialPhotos.length < targetCount ? initialPhotos.length : 0;
  });

  const [countdown, setCountdown] = useState(0);
  const [flash, setFlash] = useState(false);
  const [showTimerUI, setShowTimerUI] = useState(false);
  // showFilters: di mobile = popup overlay; di lg+ = selalu tampil di sidebar kiri
  const [showFilters, setShowFilters] = useState(false);
  const [showGallery, setShowGallery] = useState(false);
  const [muted, setMuted] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [ready, setReady] = useState(false);
  const [liveStream, setLiveStream] = useState<MediaStream | null>(null);
  const [videoAspect, setVideoAspect] = useState(16 / 9);
  const capturingRef = useRef(false);

  // Pelacakan wajah: hanya aktif kalau ada efek yang dipilih dan kamera sudah siap
  const { facesRef, status: trackerStatus } = useFaceTracker(videoRef, needsFaces(effect) && ready);

  const filterCss = FILTERS.find((f) => f.id === filter)?.css ?? 'none';

  // Penanda "pemanggilan terbaru"
  const startRunRef = useRef(0);

  const startCamera = useCallback(async () => {
    const run = ++startRunRef.current;
    streamRef.current?.getTracks().forEach((t) => t.stop());
    streamRef.current = null;
    try {
      const stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
        audio: false,
      });
      if (run !== startRunRef.current) {
        stream.getTracks().forEach((t) => t.stop());
        return;
      }
      streamRef.current = stream;
      setLiveStream(stream);
      if (videoRef.current) {
        videoRef.current.srcObject = stream;
        try {
          await videoRef.current.play();
        } catch {
          /* play() bisa dibatalkan kalau stream diganti, abaikan */
        }
      }
      setReady(true);
      setError(null);
    } catch (err) {
      console.error('[Kamera] gagal dibuka:', err);
      if (run === startRunRef.current) {
        setError('Camera access denied. Please allow camera permissions and reload.');
      }
    }
  }, []);

  useEffect(() => {
    void startCamera();
    return () => {
      startRunRef.current++;
      streamRef.current?.getTracks().forEach((t) => t.stop());
      streamRef.current = null;
    };
  }, [startCamera]);

  // Gambar efek wajah di atas tampilan kamera (preview)
  useEffect(() => {
    let raf = 0;

    const clear = () => {
      const canvas = overlayRef.current;
      const ctx = canvas?.getContext('2d');
      if (canvas && ctx) {
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
      }
    };

    if (effect === 'none') {
      clear();
      return;
    }

    const draw = () => {
      const canvas = overlayRef.current;
      const video = videoRef.current;
      if (canvas && video && video.videoWidth && canvas.parentElement) {
        const cw = canvas.parentElement.clientWidth;
        const ch = canvas.parentElement.clientHeight;
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        if (canvas.width !== Math.round(cw * dpr) || canvas.height !== Math.round(ch * dpr)) {
          canvas.width = Math.round(cw * dpr);
          canvas.height = Math.round(ch * dpr);
        }
        const ctx = canvas.getContext('2d');
        if (ctx) {
          ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
          ctx.clearRect(0, 0, cw, ch);
          const vw = video.videoWidth;
          const vh = video.videoHeight;
          const s = Math.min(cw / vw, ch / vh);
          const ox = (cw - vw * s) / 2;
          const oy = (ch - vh * s) / 2;
          const faces: FaceInput[] = facesRef.current.map((tf) => ({
            id: tf.id,
            pt: (i) => ({ x: cw - (ox + tf.points[i].x * vw * s), y: oy + tf.points[i].y * vh * s }),
          }));
          drawEffects(ctx, effect, faces, performance.now() / 1000);
        }
      }
      raf = requestAnimationFrame(draw);
    };
    draw();

    return () => {
      cancelAnimationFrame(raf);
      clear();
    };
  }, [effect, facesRef]);

  const capturePhoto = useCallback(() => {
    const video = videoRef.current;
    if (!video || !video.videoWidth) return null;
    const vw = video.videoWidth;
    const vh = video.videoHeight;

    const baseCanvas = document.createElement('canvas');
    baseCanvas.width = vw;
    baseCanvas.height = vh;
    const ctx = baseCanvas.getContext('2d')!;
    ctx.translate(vw, 0);
    ctx.scale(-1, 1);
    ctx.filter = filterCss === 'none' ? 'none' : filterCss;
    ctx.drawImage(video, 0, 0, vw, vh);

    // Bakar efek wajah ke dalam foto
    if (effect !== 'none') {
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.filter = 'none';
      const faces: FaceInput[] = facesRef.current.map((tf) => ({
        id: tf.id,
        pt: (i) => ({ x: vw - tf.points[i].x * vw, y: tf.points[i].y * vh }),
      }));
      drawEffects(ctx, effect, faces, performance.now() / 1000);
    }

    return baseCanvas.toDataURL('image/jpeg', 0.92);
  }, [filterCss, effect, facesRef]);

  const doCountdownAndCapture = useCallback(async () => {
    if (capturingRef.current) return;
    capturingRef.current = true;

    if (timer > 0) {
      for (let c = timer; c > 0; c--) {
        setCountdown(c);
        sound.beep(c === 1);
        await sleep(1000);
      }
      setCountdown(0);
    }

    sound.shutter();
    setFlash(true);
    await sleep(80);
    const photo = capturePhoto();
    setFlash(false);

    if (photo) {
      setPhotos((prev) => {
        const next = [...prev];
        next[targetSlot] = photo;

        const allFilled = next.length >= targetCount && next.slice(0, targetCount).every(Boolean);

        if (initialRetakeIndex !== null) {
          setTimeout(() => onCaptureComplete(next.slice(0, targetCount)), 500);
        } else if (allFilled) {
          setTimeout(() => onCaptureComplete(next.slice(0, targetCount)), 600);
        } else {
          const nextEmpty = next.findIndex((p, idx) => idx < targetCount && !p);
          if (nextEmpty !== -1) {
            setTargetSlot(nextEmpty);
          } else if (targetSlot < targetCount - 1) {
            setTargetSlot(targetSlot + 1);
          }
        }

        return next;
      });
    }
    capturingRef.current = false;
  }, [timer, capturePhoto, targetCount, targetSlot, initialRetakeIndex, onCaptureComplete]);

  const handleCapture = useCallback(() => {
    void doCountdownAndCapture();
  }, [doCountdownAndCapture]);

  const handleRetakeAll = useCallback(() => {
    setPhotos([]);
    setTargetSlot(0);
    sound.click();
  }, []);

  const handleSelectSlotForRetake = useCallback((slotIdx: number) => {
    sound.click();
    setTargetSlot(slotIdx);
  }, []);

  // Keyboard shortcuts
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.code === 'Space' && !capturingRef.current && ready && !error) {
        e.preventDefault();
        handleCapture();
      } else if (e.code === 'KeyR') {
        handleRetakeAll();
      } else if (e.code === 'Escape') {
        onExit();
      }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [handleCapture, handleRetakeAll, onExit, ready, error]);

  const toggleMute = () => {
    const nextMuted = !muted;
    setMuted(nextMuted);
    sound.setMuted(nextMuted);
    if (!nextMuted) {
      sound.click();
    }
  };

  const isCurrentSlotFilled = Boolean(photos[targetSlot]);
  const allSlotsFilled = photos.length >= targetCount && photos.slice(0, targetCount).every(Boolean);

  /** Konten panel filter — dipakai di sidebar kiri (md+) maupun popup (mobile) */
  const FilterPanelContent = (
    <div className="flex flex-col gap-5 p-4">
      <div>
        <p className="mb-3 text-[11px] font-semibold uppercase tracking-widest text-neutral-400">🎨 Filter Warna</p>
        <FilterStrip active={filter} onSelect={setFilter} />
      </div>

      <div>
        <p className="mb-3 text-[11px] font-semibold uppercase tracking-widest text-neutral-400">😄 Efek Wajah</p>
        <EffectStrip active={effect} onSelect={setEffect} status={trackerStatus} />
      </div>
    </div>
  );

  return (
    <div className="relative flex h-[100dvh] flex-col overflow-hidden bg-gradient-to-br from-[#082f6b] via-[#0b4a9e] to-[#1a73d9]">
      {/* Latar: ikon kamera digital yang bergerak pelan */}
      <style>{PB_KEYFRAMES}</style>
      <div className="pointer-events-none absolute inset-0 overflow-hidden">
        <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -right-24 bottom-1/4 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl" />
        {FLOATERS.map((f, i) => {
          const Icon = f.icon;
          return (
            <span
              key={i}
              className="absolute text-white/30"
              style={{ left: f.x, top: f.y, animation: `${f.anim} ${f.dur}s ease-in-out infinite`, animationDelay: `${i * 0.4}s` }}
            >
              <Icon size={f.size} strokeWidth={1.5} />
            </span>
          );
        })}
      </div>

      {/* Judul */}
      <div className="relative z-10 flex shrink-0 justify-center px-4 pt-3">
        <div className="flex items-center gap-3 rounded-full bg-white px-6 py-1.5 shadow-[0_5px_0_rgba(0,20,60,0.35)]">
          <Camera size={22} strokeWidth={2.5} style={{ color: NAVY }} />
          <h1
            className="text-xl font-extrabold uppercase sm:text-2xl"
            style={{ letterSpacing: '0.32em', fontFamily: '"Segoe UI", "Helvetica Neue", Arial, sans-serif' }}
          >
            <span style={{ color: NAVY }}>Photo</span>
            <span className="text-sky-500">booth</span>
          </h1>
        </div>
      </div>

      {/* Top bar */}
      <div className="relative z-10 flex items-center justify-between px-4 py-3 sm:px-8">
        <button
          onClick={() => {
            sound.click();
            onExit();
          }}
          className="flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-xs font-bold text-[#0b4a9e] shadow-[0_4px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0.5 active:shadow-none"
        >
          <X size={14} strokeWidth={3} /> Exit
        </button>

        {/* Slot selector pills for per-slot retake */}
        <div className="flex items-center gap-2">
          {Array.from({ length: targetCount }).map((_, idx) => {
            const hasPhoto = Boolean(photos[idx]);
            const isSelected = targetSlot === idx;
            return (
              <button
                key={idx}
                onClick={() => handleSelectSlotForRetake(idx)}
                className={`flex items-center gap-1.5 rounded-full px-4 py-2 text-xs font-bold shadow-[0_4px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5 ${
                  isSelected
                    ? 'scale-110 bg-white text-[#0b4a9e]'
                    : hasPhoto
                      ? 'bg-sky-400 text-white'
                      : 'bg-white/15 text-white'
                }`}
                title={`Foto ${idx + 1} - ${hasPhoto ? 'Sudah Diambil (Klik untuk Retake)' : 'Belum Diambil'}`}
              >
                {hasPhoto && <CheckCircle2 size={13} />}
                <span>Foto {idx + 1}</span>
                {hasPhoto && <RotateCcw size={10} className="opacity-70" />}
              </button>
            );
          })}
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => {
              sound.click();
              onChangeTemplate();
            }}
            className="rounded-full bg-white px-4 py-2 text-xs font-bold text-[#0b4a9e] shadow-[0_4px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0.5 active:shadow-none"
          >
            {template.name}
          </button>
          <button
            onClick={toggleMute}
            className="rounded-full bg-white p-2.5 text-[#0b4a9e] shadow-[0_4px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0.5 active:shadow-none"
          >
            {muted ? <VolumeX size={16} /> : <Volume2 size={16} />}
          </button>
        </div>
      </div>

      {/* Progress bar */}
      <div className="relative z-10 mx-4 h-3 overflow-hidden rounded-full bg-white/30 sm:mx-8">
        <div
          className="h-full rounded-full bg-gradient-to-r from-sky-300 via-white to-sky-300 transition-all duration-300"
          style={{ width: `${(photos.filter(Boolean).length / targetCount) * 100}%` }}
        />
      </div>

      {/* Status */}
      <div className="relative z-10 flex items-center justify-center gap-2 px-4 py-2 text-center text-xs font-semibold text-white drop-shadow">
        <span className="inline-block h-2.5 w-2.5 animate-pulse rounded-full bg-sky-300" />
        <span>
          {isCurrentSlotFilled
            ? `Sedang memilih Foto ${targetSlot + 1} (Klik Capture untuk Retake Foto Ini)`
            : `Mengambil Foto ${targetSlot + 1} dari ${targetCount}`}
        </span>
        {allSlotsFilled && (
          <button
            onClick={() => onCaptureComplete(photos.slice(0, targetCount))}
            className="ml-3 rounded-full bg-white px-4 py-1 text-[11px] font-black text-[#0b4a9e] shadow-[0_3px_0_rgba(0,20,60,0.35)] transition-all hover:-translate-y-0.5"
          >
            Lanjut ke Hasil &rarr;
          </button>
        )}
      </div>

      {/* ===== MAIN CONTENT AREA ===== */}
      <div className="relative z-10 flex min-h-0 flex-1 items-stretch px-3 pb-2 gap-3">

        {/* KIRI: Filter panel — sidebar permanen di md+, tersembunyi di mobile kecil */}
        <div className="hidden md:flex md:w-64 lg:w-72 xl:w-80 shrink-0 flex-col self-stretch py-1">
          <div className="flex flex-col h-full rounded-2xl bg-neutral-900/90 shadow-2xl ring-1 ring-white/20 backdrop-blur overflow-hidden">
            <div className="px-4 pt-4 pb-2 border-b border-white/10 shrink-0">
              <p className="text-[13px] font-bold uppercase tracking-widest text-white/80">🎨 Filter &amp; Efek</p>
            </div>
            <div className="flex-1 overflow-y-auto">
              {FilterPanelContent}
            </div>
          </div>
        </div>

        {/* TENGAH: Kamera */}
        <div className="flex flex-1 items-center justify-center min-w-0">
          <div
            className="relative h-full max-w-full overflow-hidden rounded-[2rem] border-[6px] border-white bg-neutral-900 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]"
            style={{ aspectRatio: '16 / 9' }}
          >
            {error ? (
              <div className="flex h-full flex-col items-center justify-center gap-4 px-6 text-center">
                <Camera size={48} className="text-white/60" />
                <p className="max-w-sm text-sm text-white/80">{error}</p>
                <div className="flex items-center gap-3">
                  <button
                    onClick={() => void startCamera()}
                    className="rounded-full bg-white px-6 py-2 text-sm font-bold text-[#0b4a9e]"
                  >
                    Retry
                  </button>
                  <button
                    onClick={() => {
                      const samples = generateSamplePhotos(targetCount);
                      onCaptureComplete(samples);
                    }}
                    className="rounded-full border border-white/30 bg-white/15 px-6 py-2 text-sm font-bold text-white hover:bg-white/25"
                  >
                    Use Sample Photos
                  </button>
                </div>
              </div>
            ) : (
              <>
                {/* Video */}
                <video
                  onLoadedMetadata={(e) => {
                    const v = e.currentTarget;
                    if (v.videoWidth && v.videoHeight) setVideoAspect(v.videoWidth / v.videoHeight);
                  }}
                  ref={videoRef}
                  autoPlay
                  playsInline
                  muted
                  className="absolute inset-0 h-full w-full object-contain"
                  style={{
                    filter: filterCss,
                    transform: 'scaleX(-1)',
                  }}
                />
                {/* Efek wajah (stiker) di atas video */}
                <canvas ref={overlayRef} className="pointer-events-none absolute inset-0 h-full w-full" />
                <SlotGuide aspect={slotAspect(template, targetSlot)} videoAspect={videoAspect} />

                <CountdownOverlay count={countdown} />
                <ShutterFlash show={flash} />

                {/* Viewfinder kamera digital */}
                <div className="pointer-events-none absolute inset-0">
                  {['left-3 top-3 border-l-4 border-t-4 rounded-tl-xl', 'right-3 top-3 border-r-4 border-t-4 rounded-tr-xl', 'bottom-3 left-3 border-b-4 border-l-4 rounded-bl-xl', 'bottom-3 right-3 border-b-4 border-r-4 rounded-br-xl'].map((c) => (
                    <span key={c} className={`absolute h-8 w-8 border-white/80 ${c}`} />
                  ))}
                  <div className="absolute left-5 top-5 flex items-center gap-1.5 rounded-md bg-black/35 px-2 py-0.5 text-[10px] font-bold tracking-widest text-white">
                    <span className="h-2 w-2 rounded-full bg-red-500" style={{ animation: 'pb-blink 1.2s infinite' }} />
                    REC
                  </div>
                  <div className="absolute inset-x-0 h-px bg-white/25" style={{ animation: 'pb-scan 4s linear infinite' }} />
                </div>

                {!ready && (
                  <div className="absolute inset-0 flex items-center justify-center bg-[#0b4a9e]">
                    <div className="h-10 w-10 animate-spin rounded-full border-4 border-white/30 border-t-white" />
                  </div>
                )}

                {/* Filter panel popup — HANYA di mobile kecil (< md) */}
                {showFilters && (
                  <div className="absolute left-1/2 top-4 z-20 w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 rounded-2xl bg-neutral-900/95 shadow-2xl ring-2 ring-white/40 backdrop-blur md:hidden">
                    <div className="flex items-center justify-between px-3.5 pt-3 pb-1 border-b border-white/10">
                      <p className="text-[10px] font-semibold uppercase tracking-widest text-neutral-400">Filter &amp; Efek</p>
                      <button onClick={() => setShowFilters(false)} className="text-xs text-neutral-400 hover:text-white">✕ Tutup</button>
                    </div>
                    {FilterPanelContent}
                  </div>
                )}

                {/* Gallery panel */}
                {showGallery && photos.length > 0 && (
                  <div className="absolute left-1/2 top-4 z-20 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 rounded-2xl bg-neutral-900/95 p-3 shadow-2xl ring-2 ring-white/40 backdrop-blur">
                    <div className="mb-2 flex items-center justify-between">
                      <p className="text-[10px] font-semibold uppercase tracking-widest text-neutral-400">Foto yang Sudah Diambil</p>
                      <button onClick={() => setShowGallery(false)} className="text-xs text-neutral-400 hover:text-white">✕</button>
                    </div>
                    <div className="grid grid-cols-3 gap-2">
                      {Array.from({ length: targetCount }).map((_, i) => (
                        <div
                          key={i}
                          onClick={() => {
                            handleSelectSlotForRetake(i);
                            setShowGallery(false);
                          }}
                          className={`relative cursor-pointer overflow-hidden rounded-lg border transition-all ${
                            targetSlot === i ? 'border-sky-300 ring-2 ring-sky-300' : 'border-white/10 hover:border-white/30'
                          }`}
                        >
                          {photos[i] ? (
                            <img src={photos[i]} alt={`Slot ${i + 1}`} className="h-20 w-full object-cover" />
                          ) : (
                            <div className="flex h-20 w-full items-center justify-center bg-neutral-800 text-xs text-neutral-500">
                              Kosong
                            </div>
                          )}
                          <div className="absolute inset-x-0 bottom-0 flex items-center justify-between bg-black/70 px-1 py-0.5 text-[9px] text-white">
                            <span>Foto {i + 1}</span>
                            {photos[i] && (
                              <span title="Klik untuk retake">
                                <RotateCcw size={9} />
                              </span>
                            )}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        </div>

        {/* KANAN: pratinjau hasil template (layar lebar) */}
        <div className="pointer-events-none hidden h-full md:w-56 lg:w-64 xl:w-72 shrink-0 md:flex flex-col items-center justify-center gap-2">
          <div className="flex min-h-0 w-full flex-1 items-center justify-center" style={{ containerType: 'size' }}>
            <TemplatePreview
              fit
              template={template}
              live={{
                activeIndex: targetSlot,
                photos: Array.from({ length: targetCount }, (_, i) => (i === targetSlot ? null : photos[i] ?? null)),
                stream: liveStream,
                mirrored: true,
                filterCss,
              }}
            />
          </div>
          <span className="rounded-full bg-white/90 px-3 py-1 text-[11px] font-bold text-[#0b4a9e] shadow">
            Foto {targetSlot + 1} dari {targetCount}
          </span>
        </div>

      </div>

      {/* Bottom control bar */}
      <div className="relative z-10 flex items-end justify-between px-6 pb-3 pt-1 sm:px-16">
        {/* Timer */}
        <div className="flex flex-col items-center gap-1">
          <button
            onClick={() => {
              sound.click();
              setShowTimerUI((v) => !v);
              setShowFilters(false);
              setShowGallery(false);
            }}
            className={`${ROUND_BTN} ${showTimerUI || timer > 0 ? 'ring-4 ring-sky-300' : ''}`}
          >
            <Timer size={24} strokeWidth={2.5} />
          </button>
          <span className={LABEL}>{timer > 0 ? `${timer}s` : 'Timer'}</span>
          {showTimerUI && (
            <div className="absolute bottom-24 left-4 z-30 sm:left-16">
              <TimerSelector value={timer} onChange={(v) => { setTimer(v); setShowTimerUI(false); sound.click(); }} />
            </div>
          )}
        </div>

        {/* Filter button — hanya di mobile kecil, di md+ panel sudah selalu tampil */}
        <div className="flex flex-col items-center gap-1 md:hidden">
          <button
            onClick={() => {
              sound.click();
              setShowFilters((v) => !v);
              setShowTimerUI(false);
              setShowGallery(false);
            }}
            className={`${ROUND_BTN} ${showFilters ? 'ring-4 ring-sky-300' : ''}`}
            title="Pilih Filter & Efek"
          >
            <Aperture size={24} strokeWidth={2.5} />
          </button>
          <span className={LABEL}>Filter</span>
        </div>

        {/* Capture button */}
        <div className="flex flex-col items-center gap-1">
          <button
            onClick={handleCapture}
            disabled={!ready || !!error}
            className="flex h-20 w-20 items-center justify-center rounded-full bg-white p-1.5 shadow-[0_6px_0_rgba(0,20,60,0.35),0_10px_25px_rgba(0,20,60,0.35)] transition-all hover:scale-105 active:translate-y-1 active:scale-95 disabled:opacity-40 sm:h-24 sm:w-24"
          >
            <div
              className="flex h-full w-full items-center justify-center rounded-full"
              style={{ background: 'linear-gradient(135deg, #0b4a9e, #1a73d9)' }}
            >
              <Camera size={30} strokeWidth={2.5} className="text-white drop-shadow" />
            </div>
          </button>
          <span className={LABEL}>
            {isCurrentSlotFilled ? `Retake Foto ${targetSlot + 1}` : `Ambil Foto ${targetSlot + 1}`}
          </span>
        </div>

        {/* Gallery */}
        <div className="flex flex-col items-center gap-1">
          <button
            onClick={() => {
              sound.click();
              setShowGallery((v) => !v);
              setShowTimerUI(false);
              setShowFilters(false);
            }}
            className={`${ROUND_BTN} relative ${showGallery ? 'ring-4 ring-sky-300' : ''}`}
          >
            <Images size={24} strokeWidth={2.5} />
            {photos.filter(Boolean).length > 0 && (
              <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[10px] font-black text-[#0b4a9e] shadow">
                {photos.filter(Boolean).length}
              </span>
            )}
          </button>
          <span className={LABEL}>Gallery</span>
        </div>

        {/* Reset / Retake All */}
        <div className="flex flex-col items-center gap-1">
          <button
            onClick={handleRetakeAll}
            disabled={photos.filter(Boolean).length === 0}
            className={`${ROUND_BTN} disabled:opacity-40`}
            title="Reset & Ulangi Semua Foto"
          >
            <RotateCcw size={24} strokeWidth={2.5} />
          </button>
          <span className={LABEL}>Reset Semua</span>
        </div>
      </div>

      {/* Keyboard hint (desktop) */}
      <div className="relative z-10 hidden items-center justify-center gap-6 pb-2 text-[10px] text-white/80 sm:flex">
        <span><kbd className="rounded bg-white/25 px-1.5 py-0.5">SPACE</kbd> ambil foto</span>
        <span><kbd className="rounded bg-white/25 px-1.5 py-0.5">R</kbd> reset semua</span>
        <span><kbd className="rounded bg-white/25 px-1.5 py-0.5">ESC</kbd> keluar</span>
      </div>
    </div>
  );
}

function sleep(ms: number) {
  return new Promise((r) => setTimeout(r, ms));
}
