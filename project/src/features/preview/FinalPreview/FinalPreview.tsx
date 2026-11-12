import { useEffect, useRef, useState, type PointerEvent as ReactPointerEvent } from 'react';
import { Download, RotateCcw, Images, Check, Printer, Camera, Aperture, Focus, Zap } from 'lucide-react';
import type { Template } from '@/types';
import { allTemplates } from '@/features/template/templates';
import { fetchTemplates } from '@/features/template/api';
import { renderTemplate } from '@/features/template/canvasRenderer';
import { sound } from '@/features/common/sound';
import { TemplatePreview } from '@/features/template/TemplatePreview';
import { buildPrintSheet, printSheet } from './printSheet';
import { QRCodeSVG } from 'qrcode.react';

interface Props {
  template: Template;
  photos: string[];
  onRetakeAll: () => void;
  onRetakeSlot: (slotIndex: number) => void;
  onChangeTemplate: (id: string) => void;
  onRestart: () => void;
}

export function FinalPreview({
  template,
  photos,
  onRetakeAll,
  onRetakeSlot,
  onChangeTemplate,
  onRestart,
}: Props) {
  const [result, setResult] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [showTemplates, setShowTemplates] = useState(false);
  const [templateList, setTemplateList] = useState<Template[]>(allTemplates);
const [drive, setDrive] = useState<{ url: string; forResult: string; forStickers: string } | null>(null);
  const [uploading, setUploading] = useState(false);
  const [driveError, setDriveError] = useState<string | null>(null);
  const [stickerCatalog, setStickerCatalog] = useState<StickerItem[]>([]);
const [placed, setPlaced] = useState<PlacedSticker[]>([]);
const [selected, setSelected] = useState<string | null>(null);
const boxRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    fetchTemplates()
      .then((data) => {
        if (data && data.length > 0) setTemplateList(data);
      })
      .catch((err) => console.error(err));
  }, []);

  useEffect(() => {
  fetch('http://localhost/photobooth/backend/index.php/Stickers/all')
    .then((r) => r.json())
    .then((d) => { if (Array.isArray(d)) setStickerCatalog(d); })
    .catch((e) => console.error(e));
}, []);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setResult(null);
    void (async () => {
      try {
        const url = await renderTemplate(template, photos, 2);
        if (!cancelled) {
          setResult(url);
          setLoading(false);
        }
      } catch {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [template, photos]);


  const addSticker = async (st: StickerItem) => {
  sound.click();
  const im = await loadImg(st.url);
  const uid = `${st.id}-${Date.now()}`;
setPlaced((p) => [...p, { uid, src: st.url, x: 0.5, y: 0.5, w: 0.3, ratio: im.naturalHeight / im.naturalWidth, rotation: 0 }]);
  setSelected(uid);
};

const scaleSticker = (uid: string, factor: number) => {
  setPlaced((list) =>
    list.map((s) => (s.uid === uid ? { ...s, w: Math.min(1, Math.max(0.06, s.w * factor)) } : s)),
  );
};

const removeSticker = (uid: string) => {
  setPlaced((p) => p.filter((s) => s.uid !== uid));
  setSelected(null);
};

const startDrag = (e: ReactPointerEvent, uid: string, mode: 'move' | 'resize' | 'rotate') => {
  e.stopPropagation();
  e.preventDefault();
  setSelected(uid);
  const box = boxRef.current;
  if (!box) return;
  const rect = box.getBoundingClientRect();

    const move = (ev: PointerEvent) => {
    const px = (ev.clientX - rect.left) / rect.width;
    const py = (ev.clientY - rect.top) / rect.height;
    setPlaced((list) =>
      list.map((s) => {
        if (s.uid !== uid) return s;
        if (mode === 'move') {
          return { ...s, x: Math.min(1, Math.max(0, px)), y: Math.min(1, Math.max(0, py)) };
        }
        const cx = rect.left + s.x * rect.width;
        const cy = rect.top + s.y * rect.height;
        const dx = ev.clientX - cx;
        const dy = ev.clientY - cy;
        if (mode === 'rotate') {
          let deg = (Math.atan2(dy, dx) * 180) / Math.PI + 90; // 0° = handle tepat di atas
          if (ev.shiftKey) deg = Math.round(deg / 15) * 15;    // tahan Shift = snap tiap 15°
          return { ...s, rotation: deg };
        }
        // resize: putar balik pointer ke sumbu stiker supaya tetap benar saat sudah miring
        const r = (-s.rotation * Math.PI) / 180;
        const localX = dx * Math.cos(r) - dy * Math.sin(r);
        return { ...s, w: Math.min(1, Math.max(0.06, (Math.abs(localX) * 2) / rect.width)) };
      }),
    );
  };
  const up = () => {
    window.removeEventListener('pointermove', move);
    window.removeEventListener('pointerup', up);
  };
  window.addEventListener('pointermove', move);
  window.addEventListener('pointerup', up);
};

  const handleDownload = async () => {
    if (!result || uploading) return;
    sound.click();
    setSelected(null);
let finalImg = result;
try {
  finalImg = await composeWithStickers(result, placed);
} catch (err) {
  setDriveError('Stiker gagal digabung: ' + String(err));
  return;
}
const stickerSig = JSON.stringify(placed);

    const a = document.createElement('a');
    a.href = finalImg;
    a.download = `photobooth-${template.id}-${Date.now()}.png`;
    a.click();
    photos.forEach((p, i) => {
  setTimeout(() => {
    const link = document.createElement('a');
    link.href = p;
    link.download = `photobooth-${template.id}-mentah-${i + 1}.jpg`;
    link.click();
  }, 400 * (i + 1));            // diberi jeda supaya browser tidak memblokir
});

let customerName = '';
try { customerName = sessionStorage.getItem('pb_customer_name') || ''; } catch {}


    setUploading(true);
    setDrive(null);
    setDriveError(null);
    try {
      const res = await fetch('http://localhost/photobooth/backend/index.php/Photos/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          image: finalImg,
          customer_name: customerName,
          template_slug: template.id,
          raw_photos: photos,        // <-- tambahan: semua foto mentah
        }),
      });
      const text = await res.text();
      console.log('save response:', text);

      let data: any = null;
      try {
        data = JSON.parse(text);
      } catch {
        setDriveError('Respons server bukan JSON: ' + text.slice(0, 300));
        return;
      }

    if (data.drive_url) setDrive({ url: data.folder_url || data.drive_url, forResult: result, forStickers: stickerSig });
      else setDriveError('drive_url kosong. ' + JSON.stringify(data.drive_debug ?? data));
    } catch (err) {
      setDriveError('Gagal menghubungi server: ' + String(err));
    } finally {
      setUploading(false);
    }
  };

 const handlePrint = async () => {
  if (!result) return;
  sound.click();
  setSelected(null);
  const finalImg = await composeWithStickers(result, placed);
  printSheet(await buildPrintSheet(finalImg));
};

  // QR hanya tampil selama gambar di layar sama dengan gambar yang diunggah
const driveUrl = drive && drive.forResult === result && drive.forStickers === JSON.stringify(placed) ? drive.url : null;


  return (
    <div className="relative z-10 min-h-screen overflow-hidden bg-gradient-to-br from-[#082f6b] via-[#0b4a9e] to-[#1a73d9]">
      <style>{PV_CSS}</style>

      {/* Latar: ikon kamera digital yang bergerak pelan */}
      <div className="pointer-events-none absolute inset-0 overflow-hidden">
        <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -right-24 bottom-1/4 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl" />
        {PV_FLOATERS.map((f, i) => {
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

      <div className="relative z-10 mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16">
        <div className="text-center" style={{ animation: 'pv-rise .6s ease-out backwards' }}>
          <div className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 shadow-[0_4px_0_rgba(0,20,60,0.35)]">
            <Check size={14} className="text-sky-500" strokeWidth={3} />
            <span className="text-xs font-semibold text-[#0b4a9e]">Photos captured</span>
          </div>
          <h2
            className="mt-4 text-2xl font-extrabold uppercase text-white drop-shadow sm:text-3xl"
            style={{ letterSpacing: '0.2em' }}
          >
            Your Photo is Ready
          </h2>
        </div>

        <div className="mt-10 flex flex-col items-center gap-8 lg:flex-row lg:items-start lg:justify-center">

        {/* Panel stiker */}
<div
  className="order-2 w-full max-w-md rounded-2xl bg-white/10 p-3.5 ring-1 ring-white/25 backdrop-blur lg:order-1 lg:w-64"
  style={{ animation: 'pv-rise .5s .15s ease-out backwards' }}
>
  <p className="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-white/90">Tambah Stiker</p>
  {stickerCatalog.length === 0 ? (
    <p className="text-xs text-white/70">Belum ada stiker.</p>
  ) : (
    <div className="grid grid-cols-3 gap-2">
      {stickerCatalog.map((st) => (
        <button key={st.id} onClick={() => addSticker(st)} title={st.name} className="pb-btn rounded-lg bg-white p-1.5 shadow">
          <img src={st.url} crossOrigin="anonymous" alt={st.name} draggable={false} className="h-16 w-full object-contain" />
        </button>
      ))}
    </div>
  )}
  <p className="mt-2.5 text-[10px] leading-snug text-white/70">
    Tap stiker untuk menempel. Seret untuk memindah, tarik titik biru untuk memperbesar.
  </p>
  {placed.length > 0 && (
    <button
      onClick={() => { setPlaced([]); setSelected(null); }}
      className="mt-2 text-[11px] font-semibold text-white/80 underline"
    >
      Hapus semua stiker
    </button>
  )}
</div>

{/* Result */}
<div className="order-1 flex flex-col items-center gap-4 lg:order-2" style={{ animation: 'pv-rise .7s .1s ease-out backwards' }}>

       <div className="relative rounded-3xl border-[6px] border-white bg-white/95 p-4 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]">
  {['left-2 top-2 border-l-4 border-t-4 rounded-tl-lg', 'right-2 top-2 border-r-4 border-t-4 rounded-tr-lg', 'bottom-2 left-2 border-b-4 border-l-4 rounded-bl-lg', 'bottom-2 right-2 border-b-4 border-r-4 rounded-br-lg'].map((c) => (
    <span key={c} className={`pointer-events-none absolute h-6 w-6 border-[#0b4a9e]/60 ${c}`} />
  ))}
  {loading ? (
    <div className="flex items-center justify-center" style={{ width: 300, height: 400 }}>
      <div className="h-10 w-10 animate-spin rounded-full border-4 border-sky-200 border-t-[#0b4a9e]" />
    </div>
  ) : result ? (
    <div ref={boxRef} className="relative inline-block select-none" onPointerDown={() => setSelected(null)}>
      <img
        src={result}
        alt="Final photobooth result"
        draggable={false}
        className="block rounded-lg object-contain shadow-lg"
        style={{ maxHeight: 'min(78vh, 780px)' }}
      />
      {placed.map((s) => (
        <div
          key={s.uid}
          onPointerDown={(e) => startDrag(e, s.uid, 'move')}
          className="absolute cursor-move touch-none"
          style={{
            left: `${s.x * 100}%`,
            top: `${s.y * 100}%`,
            width: `${s.w * 100}%`,
              transform: `translate(-50%, -50%) rotate(${s.rotation}deg)`,
            outline: selected === s.uid ? '2px dashed #38bdf8' : 'none',
          }}
        >
          <img src={s.src} crossOrigin="anonymous" alt="" draggable={false} className="pointer-events-none block w-full select-none" />
         {selected === s.uid && (
          <>
            {/* Hapus */}
            <button
              onPointerDown={(e) => e.stopPropagation()}
              onClick={() => removeSticker(s.uid)}
              className="absolute -right-3 -top-3 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white shadow"
              aria-label="Hapus stiker"
            >
              ✕
            </button>

            {/* Putar (hijau) */}
            <span
              onPointerDown={(e) => startDrag(e, s.uid, 'rotate')}
              className="absolute -top-8 left-1/2 h-6 w-6 -translate-x-1/2 cursor-grab touch-none rounded-full border-2 border-white bg-emerald-500 shadow"
              title="Putar"
            />

            {/* Perbesar/perkecil dengan drag (biru) */}
            <span
              onPointerDown={(e) => startDrag(e, s.uid, 'resize')}
              className="absolute -bottom-3 -right-3 h-6 w-6 cursor-nwse-resize touch-none rounded-full border-2 border-white bg-sky-500 shadow"
              title="Perbesar / perkecil"
            />

            {/* Tombol − dan + */}
            <div
              className="absolute -bottom-10 left-1/2 flex -translate-x-1/2 gap-2"
              onPointerDown={(e) => e.stopPropagation()}
            >
              <button
                onClick={() => scaleSticker(s.uid, 0.9)}
                className="flex h-7 w-7 items-center justify-center rounded-full bg-white text-base font-bold text-[#0b4a9e] shadow"
                aria-label="Perkecil"
              >
                −
              </button>
              <button
                onClick={() => scaleSticker(s.uid, 1.1)}
                className="flex h-7 w-7 items-center justify-center rounded-full bg-white text-base font-bold text-[#0b4a9e] shadow"
                aria-label="Perbesar"
              >
                +
              </button>
            </div>
          </>
        )}
        </div>
      ))}
    </div>
  ) : (
    <p className="text-sm text-neutral-500">Failed to render.</p>
  )}
</div>

  {loading && (
    <p className="text-xs font-medium text-white/80">Rendering your photos...</p>
  )}
</div>


          {/* Sidebar Actions */}
          <div className="order-3 flex w-full max-w-md flex-col gap-3 lg:w-72">
            <button
              onClick={handleDownload}
              disabled={loading || !result || uploading}
              className="pb-btn pv-ring flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)] disabled:opacity-40"
              style={{ animation: 'pv-rise .5s .2s ease-out backwards, pv-ring 2.2s 1s infinite' }}
            >
              <Download size={18} strokeWidth={2.5} /> Download PNG
            </button>

            <button
              onClick={handlePrint}
              disabled={loading || !result}
              className="pb-btn flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)] disabled:opacity-40"
              style={{ animation: 'pv-rise .5s .3s ease-out backwards' }}
            >
              <Printer size={18} strokeWidth={2.5} /> Print
            </button>

            {uploading && (
              <p className="text-center text-xs text-white/80">Menyimpan ke Google Drive...</p>
            )}
            {driveUrl && (
              <div className="flex flex-col items-center gap-3 rounded-2xl bg-white p-5 text-center shadow-lg" style={{ animation: 'pv-rise .5s ease-out backwards' }}>
                <p className="text-sm font-bold text-[#0b4a9e]">Ambil semua fotomu di HP</p>
                <QRCodeSVG value={driveUrl} size={176} level="M" fgColor="#0b4a9e" bgColor="#ffffff" />
                <p className="text-xs leading-snug text-neutral-600">
                  Scan QR ini untuk membuka folder berisi hasil template dan foto mentah.
                </p>
              </div>
            )}
            {driveError && (
              <p className="break-all rounded-lg bg-white/90 p-2 text-xs text-red-600">{driveError}</p>
            )}

            <button
              onClick={() => {
                sound.click();
                setShowTemplates((v) => !v);
              }}
              className="pb-btn flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/15 px-6 py-3 text-sm font-semibold text-white shadow-[0_4px_0_rgba(0,20,60,0.3)] hover:bg-white/25"
              style={{ animation: 'pv-rise .5s .4s ease-out backwards' }}
            >
              <Images size={18} /> Ganti Template
            </button>

            <button
              onClick={() => {
                sound.click();
                onRetakeAll();
              }}
              className="pb-btn flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/15 px-6 py-3 text-sm font-semibold text-white shadow-[0_4px_0_rgba(0,20,60,0.3)] hover:bg-white/25"
              style={{ animation: 'pv-rise .5s .5s ease-out backwards' }}
            >
              <RotateCcw size={18} /> Ulangi Semua Foto (Retake All)
            </button>

            <button
              onClick={() => {
                sound.click();
                onRestart();
              }}
              className="cursor-pointer rounded-xl px-6 py-2.5 text-center text-sm font-medium text-white/80 transition-all hover:-translate-y-0.5 hover:text-white"
              style={{ animation: 'pv-rise .5s .6s ease-out backwards' }}
            >
              Mulai Sesi Baru
            </button>

            {/* Individual Photo Retake Box */}
            <div className="mt-2 rounded-2xl bg-white/10 p-3.5 ring-1 ring-white/25 backdrop-blur" style={{ animation: 'pv-rise .5s .7s ease-out backwards' }}>
              <p className="mb-2.5 text-[11px] font-bold uppercase tracking-wider text-white/90">
                Ulangi Foto Tertentu (Retake)
              </p>
              <div className="grid grid-cols-3 gap-2">
                {photos.map((p, idx) => (
                  <div key={idx} className="flex flex-col items-center gap-1.5 rounded-lg bg-white p-1.5 shadow">
                    <img src={p} alt={`Foto ${idx + 1}`} className="h-16 w-full rounded object-cover" />
                    <button
                      onClick={() => {
                        sound.click();
                        onRetakeSlot(idx);
                      }}
                      className="pb-btn flex w-full cursor-pointer items-center justify-center gap-1 rounded bg-[#0b4a9e] py-1 text-[10px] font-semibold text-white hover:bg-[#1a73d9]"
                      title={`Ulangi Foto ${idx + 1}`}
                    >
                      <RotateCcw size={10} /> Retake #{idx + 1}
                    </button>
                  </div>
                ))}
              </div>
            </div>

            {/* Template Switcher */}
            {showTemplates && (
              <div className="mt-2 rounded-2xl bg-[#082f6b]/95 p-3.5 shadow-2xl ring-1 ring-white/30" style={{ animation: 'pv-rise .35s ease-out backwards' }}>
                <div className="mb-2 flex items-center justify-between">
                  <p className="text-[10px] font-semibold uppercase tracking-widest text-white/80">Pilih Template Lain</p>
                  <button onClick={() => setShowTemplates(false)} className="text-xs text-white/70 hover:text-white">✕</button>
                </div>
                <div className="grid max-h-72 grid-cols-2 gap-2 overflow-y-auto">
                  {templateList.map((t) => (
                    <button
                      key={t.id}
                      onClick={() => {
                        sound.click();
                        onChangeTemplate(t.id);
                        setShowTemplates(false);
                      }}
                      className={`rounded-lg p-2 transition-all hover:-translate-y-0.5 ${
                        template.id === t.id ? 'bg-white/25 ring-2 ring-sky-300' : 'hover:bg-white/10'
                      }`}
                    >
                      <div className="flex items-center justify-center rounded bg-white/90 p-1.5" style={{ minHeight: 80 }}>
                        <TemplatePreview template={t} />
                      </div>
                      <p className="mt-1.5 truncate text-[10px] text-white/80">{t.name}</p>
                    </button>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

/* ====== TARUH BLOK DI BAWAH INI DI LUAR KOMPONEN (misalnya di paling bawah file) ====== */

const PV_FLOATERS = [
  { icon: Camera, x: '5%', y: '16%', size: 44, anim: 'pv-float', dur: 7 },
  { icon: Aperture, x: '90%', y: '14%', size: 52, anim: 'pv-spin', dur: 18 },
  { icon: Focus, x: '4%', y: '50%', size: 40, anim: 'pv-pulse', dur: 3 },
  { icon: Zap, x: '93%', y: '48%', size: 36, anim: 'pv-flash', dur: 2.6 },
  { icon: Aperture, x: '6%', y: '78%', size: 56, anim: 'pv-spin', dur: 24 },
  { icon: Camera, x: '91%', y: '76%', size: 42, anim: 'pv-float', dur: 8 },
  { icon: Focus, x: '28%', y: '92%', size: 30, anim: 'pv-pulse', dur: 3.6 },
  { icon: Zap, x: '72%', y: '93%', size: 32, anim: 'pv-flash', dur: 3.2 },
];

const PV_CSS = `
@keyframes pv-float { 0%,100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-14px) rotate(4deg); } }
@keyframes pv-spin { to { transform: rotate(360deg); } }
@keyframes pv-pulse { 0%,100% { transform: scale(1); opacity: .25; } 50% { transform: scale(1.25); opacity: .6; } }
@keyframes pv-flash { 0%,60%,100% { opacity: .15; } 70%,80% { opacity: .75; } }
@keyframes pv-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pv-ring { 0% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,.7); } 70% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 16px rgba(255,255,255,0); } 100% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,0); } }
@keyframes pb-shine { to { left: 130%; } }
@keyframes pb-wiggle { 0%,100% { transform: rotate(0); } 25% { transform: rotate(-14deg) scale(1.15); } 75% { transform: rotate(14deg) scale(1.15); } }
.pb-btn { position: relative; overflow: hidden; transition: transform .2s ease, background-color .2s ease; }
.pb-btn:hover:not(:disabled) { transform: translateY(-3px) scale(1.02); }
.pb-btn:active:not(:disabled) { transform: translateY(3px) scale(.97); }
.pb-btn::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.55), transparent); transform: skewX(-20deg); pointer-events: none; }
.pb-btn:hover:not(:disabled)::after { animation: pb-shine .7s ease; }
.pb-btn:hover:not(:disabled) svg { animation: pb-wiggle .5s ease; }
`;


interface StickerItem { id: number; name: string; url: string }
interface PlacedSticker { uid: string; src: string; x: number; y: number; w: number; ratio: number; rotation: number }

function loadImg(src: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const img = new Image();
    if (!src.startsWith('data:')) img.crossOrigin = 'anonymous';
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = src;
  });
}

/** Gabungkan stiker ke hasil template. x,y = titik tengah, w = lebar (semuanya pecahan 0-1 dari gambar). */
async function composeWithStickers(base: string, placed: PlacedSticker[]): Promise<string> {
  if (placed.length === 0) return base;
  const baseImg = await loadImg(base);
  const canvas = document.createElement('canvas');
  canvas.width = baseImg.naturalWidth;
  canvas.height = baseImg.naturalHeight;
  const ctx = canvas.getContext('2d')!;
  ctx.drawImage(baseImg, 0, 0);
  for (const s of placed) {
    const im = await loadImg(s.src);
    const w = s.w * canvas.width;
    const h = w * s.ratio;
    ctx.save();
    ctx.translate(s.x * canvas.width, s.y * canvas.height);
    ctx.rotate((s.rotation * Math.PI) / 180);
    ctx.drawImage(im, -w / 2, -h / 2, w, h);
    ctx.restore();
  }
  return canvas.toDataURL('image/png');
}