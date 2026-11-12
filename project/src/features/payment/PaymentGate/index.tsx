import { useEffect, useState, type ReactNode } from 'react';
import { Aperture, Camera, Focus, Zap, AlertTriangle, ArrowLeft } from 'lucide-react';
import { fetchPaymentSettings, createPaymentRequest, checkPaymentStatus } from '@/features/payment/api';

interface Props {
  onApproved: (sessionMinutes: number) => void;
  onBack?: () => void;
}

type PayStep = 'loading' | 'name' | 'qris' | 'waiting' | 'error';

const PAY_KEY = 'pb_payment_v1';
const APP_KEY = 'pb_session_v1';
const PAY_TTL_MS = 15 * 60 * 1000; // simpan paling lama 15 menit

interface SavedPay {
  step: 'name' | 'qris' | 'waiting';
  name: string;
  code: string | null;
}

function loadSavedPay(): SavedPay | null {
  try {
    const raw = sessionStorage.getItem(PAY_KEY);
    if (!raw) return null;
    const s = JSON.parse(raw);
    if (typeof s.ts !== 'number' || Date.now() - s.ts > PAY_TTL_MS) {
      sessionStorage.removeItem(PAY_KEY);
      return null;
    }
    let step: SavedPay['step'] = s.step === 'qris' || s.step === 'waiting' ? s.step : 'name';
    const code = typeof s.code === 'string' && s.code ? s.code : null;
    if (step === 'waiting' && !code) step = 'qris';
    return { step, name: typeof s.name === 'string' ? s.name : '', code };
  } catch {
    return null;
  }
}

function clearSavedPay() {
  try {
    sessionStorage.removeItem(PAY_KEY);
  } catch {
    /* abaikan */
  }
}

const PG_FLOATERS = [
  { icon: Camera, x: '6%', y: '12%', size: 48, anim: 'pg-float', dur: 7 },
  { icon: Aperture, x: '90%', y: '10%', size: 56, anim: 'pg-spin', dur: 18 },
  { icon: Focus, x: '8%', y: '48%', size: 40, anim: 'pg-pulse', dur: 3 },
  { icon: Zap, x: '92%', y: '46%', size: 38, anim: 'pg-flash', dur: 2.6 },
  { icon: Aperture, x: '5%', y: '80%', size: 54, anim: 'pg-spin', dur: 24 },
  { icon: Camera, x: '90%', y: '80%', size: 44, anim: 'pg-float', dur: 8 },
  { icon: Focus, x: '30%', y: '92%', size: 30, anim: 'pg-pulse', dur: 3.6 },
  { icon: Zap, x: '70%', y: '93%', size: 32, anim: 'pg-flash', dur: 3.2 },
];

const PG_CSS = `
@keyframes pg-float { 0%,100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-14px) rotate(4deg); } }
@keyframes pg-spin { to { transform: rotate(360deg); } }
@keyframes pg-pulse { 0%,100% { transform: scale(1); opacity: .25; } 50% { transform: scale(1.25); opacity: .6; } }
@keyframes pg-flash { 0%,60%,100% { opacity: .15; } 70%,80% { opacity: .75; } }
@keyframes pg-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pg-scan { 0% { top: 6%; } 100% { top: 94%; } }
@keyframes pg-blink { 0%,100% { opacity: 1; } 50% { opacity: .2; } }
@keyframes pg-ring { 0% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,.7); } 70% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 16px rgba(255,255,255,0); } 100% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,0); } }
@keyframes pg-shine { to { left: 130%; } }
.pg-btn { position: relative; overflow: hidden; transition: transform .2s ease; }
.pg-btn:hover { transform: translateY(-3px) scale(1.04); }
.pg-btn:active { transform: translateY(3px) scale(.97); }
.pg-btn::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.7), transparent); transform: skewX(-20deg); pointer-events: none; }
.pg-btn:hover::after { animation: pg-shine .7s ease; }
`;

/** Kerangka halaman: gradient biru + ikon kamera digital melayang + judul. */
function Shell({ children }: { children: ReactNode }) {
  return (
    <div className="relative z-10 flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-[#082f6b] via-[#0b4a9e] to-[#1a73d9] px-4 py-10 text-center text-white">
      <style>{PG_CSS}</style>

      <div className="pointer-events-none absolute inset-0 overflow-hidden">
        <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -right-24 bottom-1/4 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl" />
        {PG_FLOATERS.map((f, i) => {
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

      <div className="relative z-10 flex w-full max-w-md flex-col items-center gap-5">
        <div className="flex items-center gap-3 rounded-full bg-white px-6 py-1.5 shadow-[0_5px_0_rgba(0,20,60,0.35)]" style={{ animation: 'pg-rise .5s ease-out backwards' }}>
          <Camera size={20} strokeWidth={2.5} className="text-[#0b4a9e]" />
          <span
            className="text-lg font-extrabold uppercase"
            style={{ letterSpacing: '0.32em', fontFamily: '"Segoe UI", "Helvetica Neue", Arial, sans-serif' }}
          >
            <span className="text-[#0b4a9e]">Photo</span>
            <span className="text-sky-500">booth</span>
          </span>
        </div>
        {children}
      </div>
    </div>
  );
}

export function PaymentGate({ onApproved }: Props) {
  const [saved] = useState(loadSavedPay);
  const [step, setStep] = useState<PayStep>('loading');
  const [name, setName] = useState(saved?.name ?? '');
  const [qrisImage, setQrisImage] = useState<string | null>(null);
  const [price, setPrice] = useState(0);
  const [code, setCode] = useState<string | null>(saved?.code ?? null);

  // Muat pengaturan, lalu lanjutkan dari langkah terakhir (kalau ada)
  useEffect(() => {
    fetchPaymentSettings()
      .then((s) => {
        setQrisImage(s.qris_image);
        setPrice(s.price);
        setStep(saved?.step ?? 'name');
      })
      .catch(() => setStep('error'));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Simpan langkah, nama, dan kode supaya refresh tidak mengulang dari awal
  useEffect(() => {
    if (step !== 'name' && step !== 'qris' && step !== 'waiting') return;
    try {
      sessionStorage.setItem(PAY_KEY, JSON.stringify({ step, name, code, ts: Date.now() }));
    } catch {
      /* abaikan kalau storage tidak tersedia */
    }
  }, [step, name, code]);

  // Cek status pembayaran tiap 4 detik selama menunggu konfirmasi admin
  useEffect(() => {
    if (step !== 'waiting' || !code) return;

    const interval = setInterval(async () => {
      try {
        const result = await checkPaymentStatus(code);
        if (result.payment_status === 'approved') {
          clearInterval(interval);
          clearSavedPay();
          onApproved(result.session_minutes || 10);
        } else if (result.payment_status === 'rejected') {
          clearInterval(interval);
          clearSavedPay();
          setStep('error');
        }
      } catch {
        /* koneksi putus sebentar, coba lagi di putaran berikutnya */
      }
    }, 4000);

    return () => clearInterval(interval);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [step, code]);

  const handleNameNext = () => {
    const clean = name.trim();
    if (clean.length < 2) return;
    try {
      sessionStorage.setItem('pb_customer_name', clean);
    } catch {
      /* abaikan */
    }
    setStep('qris');
  };

  const handleConfirmTransfer = async () => {
    const newCode = await createPaymentRequest();
    setCode(newCode);
    setStep('waiting');
  };

  const handleBackToHero = () => {
    clearSavedPay();
    try {
      sessionStorage.removeItem(APP_KEY);
    } catch {
      /* abaikan */
    }
    window.location.href = '/photobooth/';
  };

  if (step === 'loading') {
    return (
      <Shell>
        <Aperture size={48} strokeWidth={1.8} style={{ animation: 'pg-spin 1.6s linear infinite' }} />
        <p className="text-sm font-semibold tracking-wide text-white/90">Memuat...</p>
      </Shell>
    );
  }

  if (step === 'error') {
    return (
      <Shell>
        <div
          className="flex flex-col items-center gap-4 rounded-3xl border-[6px] border-white bg-white px-8 py-8 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]"
          style={{ animation: 'pg-rise .5s ease-out backwards' }}
        >
          <AlertTriangle size={40} className="text-red-500" />
          <p className="text-sm font-semibold text-red-600">Terjadi kesalahan atau pembayaran ditolak.</p>
          <button
            onClick={() => {
              setCode(null);
              setStep('qris');
            }}
            className="pg-btn rounded-full bg-[#0b4a9e] px-8 py-2.5 text-sm font-bold text-white shadow-[0_5px_0_rgba(0,20,60,0.35)]"
          >
            Coba Lagi
          </button>
        </div>
      </Shell>
    );
  }

  if (step === 'name') {
    return (
      <Shell>
        <button
          type="button"
          onClick={handleBackToHero}
          className="fixed left-4 top-4 z-20 flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/25"
        >
          <ArrowLeft size={16} />
          Kembali
        </button>

        <div
          className="flex w-full flex-col items-center gap-4 rounded-3xl border-[6px] border-white bg-white px-8 py-8 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]"
          style={{ animation: 'pg-rise .5s ease-out backwards' }}
        >
          <h2 className="text-xl font-extrabold uppercase text-[#0b4a9e]" style={{ letterSpacing: '0.12em' }}>
            Siapa Namamu?
          </h2>
          <p className="text-center text-xs text-slate-500">
            Nama ini dipakai untuk folder dan nama file fotomu.
          </p>
          <input
            value={name}
            onChange={(e) => setName(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && handleNameNext()}
            maxLength={30}
            autoFocus
            placeholder="Contoh: Budi"
            className="w-full rounded-full border-2 border-sky-200 px-5 py-3 text-center text-base font-semibold text-[#0b4a9e] outline-none focus:border-[#0b4a9e]"
          />
          <button
            onClick={handleNameNext}
            disabled={name.trim().length < 2}
            className="pg-btn rounded-full bg-[#0b4a9e] px-8 py-3 text-sm font-bold text-white shadow-[0_5px_0_rgba(0,20,60,0.35)] disabled:opacity-40"
          >
            Lanjut ke Pembayaran
          </button>
        </div>
      </Shell>
    );
  }

  if (step === 'waiting') {
    return (
      <Shell>
        <div
          className="flex flex-col items-center gap-4 rounded-3xl border-[6px] border-white bg-white/95 px-10 py-8 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]"
          style={{ animation: 'pg-rise .5s ease-out backwards' }}
        >
          <Aperture size={56} strokeWidth={1.8} className="text-[#0b4a9e]" style={{ animation: 'pg-spin 2.4s linear infinite' }} />
          <h2 className="text-xl font-extrabold uppercase text-[#0b4a9e]" style={{ letterSpacing: '0.12em' }}>
            Menunggu Konfirmasi Admin
          </h2>
          <p className="rounded-full bg-sky-100 px-4 py-1.5 text-sm text-[#0b4a9e]">
            Kode: <b>{code}</b>
          </p>
          <p className="flex items-center gap-2 text-xs text-slate-500">
            <span className="h-2 w-2 rounded-full bg-red-500" style={{ animation: 'pg-blink 1.2s infinite' }} />
            Halaman ini akan lanjut otomatis setelah dikonfirmasi
          </p>
        </div>
      </Shell>
    );
  }

  return (
    <Shell>
      <h2
        className="text-xl font-extrabold uppercase text-white drop-shadow sm:text-2xl"
        style={{ letterSpacing: '0.18em', animation: 'pg-rise .5s .05s ease-out backwards' }}
      >
        Scan QRIS untuk Membayar
      </h2>
      <p
        className="rounded-full bg-white px-8 py-1.5 text-3xl font-black text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)]"
        style={{ animation: 'pg-rise .5s .15s ease-out backwards' }}
      >
        Rp {price.toLocaleString('id-ID')}
      </p>

      {qrisImage ? (
        <div
          className="relative rounded-3xl border-[6px] border-white bg-white p-4 shadow-[0_10px_0_rgba(0,20,60,0.35),0_20px_50px_rgba(0,20,60,0.45)]"
          style={{ animation: 'pg-rise .6s .25s ease-out backwards, pg-float 6s 1s ease-in-out infinite' }}
        >
          {/* sudut viewfinder */}
          {['left-2 top-2 border-l-4 border-t-4 rounded-tl-lg', 'right-2 top-2 border-r-4 border-t-4 rounded-tr-lg', 'bottom-2 left-2 border-b-4 border-l-4 rounded-bl-lg', 'bottom-2 right-2 border-b-4 border-r-4 rounded-br-lg'].map((c) => (
            <span key={c} className={`pointer-events-none absolute h-6 w-6 border-[#0b4a9e]/70 ${c}`} />
          ))}
          <img src={qrisImage} alt="QRIS" className="w-64 rounded-lg" />
          {/* garis scan */}
          <div className="pointer-events-none absolute inset-x-4 h-0.5 bg-sky-400/70 shadow-[0_0_8px_rgba(56,189,248,0.9)]" style={{ animation: 'pg-scan 2.4s ease-in-out infinite alternate' }} />
        </div>
      ) : (
        <p className="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-red-600">QRIS belum diatur oleh admin.</p>
      )}

      <button
        onClick={handleConfirmTransfer}
        className="pg-btn rounded-full bg-white px-8 py-3.5 text-sm font-bold text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)]"
        style={{ animation: 'pg-rise .5s .4s ease-out backwards, pg-ring 2.2s 1.2s infinite' }}
      >
        Saya Sudah Transfer
      </button>
    </Shell>
  );
}
