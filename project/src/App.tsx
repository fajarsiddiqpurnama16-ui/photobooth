import { useCallback, useEffect, useState } from 'react';
import type { Stage, Template } from '@/types';
import { allTemplates } from '@/features/template/templates';
import { fetchTemplates } from '@/features/template/api';
import { AmbientBackground } from '@/features/common/AmbientBackground';
import { Hero } from '@/features/hero';
import { PaymentGate } from '@/features/payment/PaymentGate';
import { TemplateSelector } from '@/features/template/TemplateSelector';
import { CameraStudio } from '@/features/camera/CameraStudio';
import { FinalPreview } from '@/features/preview/FinalPreview';

type AppStage = Stage | 'payment';

const STORAGE_KEY = 'pb_session_v1';
const PAYMENT_IDLE_MS = 5 * 60 * 1000;

function loadSaved(): { stage: AppStage; selectedId?: string; expiresAt: number | null } {
  try {
    const raw = sessionStorage.getItem(STORAGE_KEY);
    if (!raw) return { stage: 'hero', expiresAt: null };
    const s = JSON.parse(raw);
    const expiresAt = typeof s.expiresAt === 'number' && s.expiresAt > Date.now() ? s.expiresAt : null;
    let stage: AppStage = s.stage ?? 'hero';
    if (stage === 'loading') stage = 'hero';
    if (stage === 'preview') stage = 'camera'; // foto tidak disimpan, ulang dari kamera
    if ((stage === 'template' || stage === 'camera') && !expiresAt) stage = 'hero';
    return { stage, selectedId: s.selectedId, expiresAt };
  } catch {
    return { stage: 'hero', expiresAt: null };
  }
}

function App() {
  const [saved] = useState(loadSaved);
  const [stage, setStage] = useState<AppStage>(saved.stage);
  const [templates, setTemplates] = useState<Template[]>(allTemplates);
  const [selectedId, setSelectedId] = useState(saved.selectedId ?? 'classic3');
  const [photos, setPhotos] = useState<string[]>([]);
  const [retakeSlotIndex, setRetakeSlotIndex] = useState<number | null>(null);
  const [sessionExpiresAt, setSessionExpiresAt] = useState<number | null>(saved.expiresAt);
  const [remainingSeconds, setRemainingSeconds] = useState<number | null>(
    saved.expiresAt ? Math.max(0, Math.floor((saved.expiresAt - Date.now()) / 1000)) : null,
  );

  useEffect(() => {
    fetchTemplates()
      .then((data) => {
        if (data && data.length > 0) {
          setTemplates(data);
          if (data[0] && !saved.selectedId) {
            setSelectedId(data[0].id);
          }
        }
      })
      .catch((err) => {
        console.error('Failed to load templates from API:', err);
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Muat ulang daftar template tiap masuk layar pilih template,
  // supaya template yang baru ditambah admin ikut terbaca.
  useEffect(() => {
    if (stage !== 'template') return;
    fetchTemplates()
      .then((data) => {
        if (data && data.length > 0) setTemplates(data);
      })
      .catch((err) => console.error('Failed to refresh templates:', err));
  }, [stage]);

  const resetToHero = useCallback(() => {
    try {
      sessionStorage.removeItem('pb_payment_v1'); // bersihkan sisa data pembayaran sesi sebelumnya
    } catch {
      /* abaikan */
    }
    setPhotos([]);
    setRetakeSlotIndex(null);
    setSessionExpiresAt(null);
    setRemainingSeconds(null);
    setStage('hero');
  }, []);

  // Timer sesi: cek tiap detik, kalau habis balik ke hero
  useEffect(() => {
    if (!sessionExpiresAt) return;

    const interval = setInterval(() => {
      const diff = Math.max(0, Math.floor((sessionExpiresAt - Date.now()) / 1000));
      setRemainingSeconds(diff);
      if (diff <= 0) {
        clearInterval(interval);
        resetToHero();
      }
    }, 1000);

    return () => clearInterval(interval);
  }, [sessionExpiresAt, resetToHero]);

  // Simpan langkah aktif supaya refresh tidak mengulang dari awal
  useEffect(() => {
    try {
      if (stage === 'hero') {
        sessionStorage.removeItem(STORAGE_KEY);
      } else {
        sessionStorage.setItem(
          STORAGE_KEY,
          JSON.stringify({ stage, selectedId, expiresAt: sessionExpiresAt }),
        );
      }
    } catch {
      /* abaikan kalau storage tidak tersedia */
    }
  }, [stage, selectedId, sessionExpiresAt]);

  // Diam terlalu lama di halaman pembayaran -> kembali ke awal
  useEffect(() => {
    if (stage !== 'payment') return;
    let timer = setTimeout(resetToHero, PAYMENT_IDLE_MS);
    const bump = () => {
      clearTimeout(timer);
      timer = setTimeout(resetToHero, PAYMENT_IDLE_MS);
    };
    const evts = ['pointerdown', 'keydown', 'touchstart'];
    evts.forEach((e) => window.addEventListener(e, bump));
    return () => {
      clearTimeout(timer);
      evts.forEach((e) => window.removeEventListener(e, bump));
    };
  }, [stage, resetToHero]);

  const getTemplate = useCallback(
    (id: string): Template => {
      return (
        templates.find((t) => t.id === id) ??
        allTemplates.find((t) => t.id === id) ??
        templates[0] ??
        allTemplates[0]
      );
    },
    [templates]
  );

  const handleCaptureComplete = useCallback((imgs: string[]) => {
    setPhotos(imgs);
    setRetakeSlotIndex(null);
    setStage('preview');
  }, []);

  const formatTime = (secs: number) => {
    const m = Math.floor(secs / 60);
    const s = secs % 60;
    return `${m}:${s.toString().padStart(2, '0')}`;
  };

  return (
    <div className="min-h-screen bg-neutral-950 text-neutral-100 antialiased">
      <AmbientBackground />

      {stage === 'hero' && <Hero onStart={() => setStage('payment')} />}

      {stage === 'payment' && (
        <PaymentGate
          onApproved={(sessionMinutes) => {
            setSessionExpiresAt(Date.now() + sessionMinutes * 60 * 1000);
            setStage('template');
          }}
        />
      )}

      {/* Badge timer sesi, muncul di semua stage setelah payment approved */}
      {remainingSeconds !== null && stage !== 'payment' && stage !== 'hero' && (
        <div className="fixed top-4 right-4 z-50 rounded-full bg-black/70 px-4 py-2 text-sm font-medium text-white backdrop-blur">
          ⏱ {formatTime(remainingSeconds)}
        </div>
      )}

      {stage === 'template' && (
        <TemplateSelector
          selected={selectedId}
          onSelect={setSelectedId}
          onContinue={() => {
            setPhotos([]);
            setRetakeSlotIndex(null);
            setStage('camera');
          }}
        />
      )}

      {stage === 'camera' && (
        <CameraStudio
          template={getTemplate(selectedId)}
          initialPhotos={photos}
          initialRetakeIndex={retakeSlotIndex}
          onCaptureComplete={handleCaptureComplete}
          onChangeTemplate={() => setStage('template')}
          onExit={resetToHero}
        />
      )}

      {stage === 'preview' && (
        <FinalPreview
          template={getTemplate(selectedId)}
          photos={photos}
          onRetakeAll={() => {
            setPhotos([]);
            setRetakeSlotIndex(null);
            setStage('camera');
          }}
          onRetakeSlot={(slotIdx: number) => {
            setRetakeSlotIndex(slotIdx);
            setStage('camera');
          }}
          onChangeTemplate={(id) => setSelectedId(id)}
          onRestart={resetToHero}
        />
      )}
    </div>
  );
}

export default App;
