import { Aperture, Camera, Clock, Focus, Gift, Heart, Printer, Sparkles, Star, Tag, Zap } from 'lucide-react';
import type { ReactNode } from 'react';
import { sound } from '@/features/common/sound';
import { useEffect, useState } from 'react';
// @ts-ignore
import TemplateShowcase from '../../TemplateShowcase';

interface Props {
  onStart: () => void;
}

interface CardItem { id: string; icon: string; title: string; description: string; image?: string | null; }
interface Info { price: number; session_minutes: number; print_copies: number; cards: CardItem[]; }

const INFO_API = 'http://localhost/photobooth/backend/Payments/info';

const ICONS: Record<string, ReactNode> = {
  sparkles: <Sparkles size={18} />, gift: <Gift size={18} />, tag: <Tag size={18} />,
  clock: <Clock size={18} />, printer: <Printer size={18} />, camera: <Camera size={18} />,
  heart: <Heart size={18} />, star: <Star size={18} />,
};

function rupiah(n: number) {
  return n % 1000 === 0 ? `${n / 1000}k` : `Rp ${n.toLocaleString('id-ID')}`;
}

function InfoCard({ icon, title, text, index = 0 }: { icon: ReactNode; title: string; text: string; index?: number }) {
  return (
    <div
      className="hr-card flex w-40 flex-col items-center gap-1 rounded-2xl bg-white px-4 py-3 shadow-[0_5px_0_rgba(0,20,60,0.35)]"
      style={{ animation: `hr-rise .5s ${0.1 * index}s ease-out backwards` }}
    >
      <div className="hr-icon text-[#0b4a9e]">{icon}</div>
      <div className="text-[11px] font-semibold uppercase tracking-widest text-sky-600">{title}</div>
      <div className="text-sm font-bold text-[#0b4a9e]">{text}</div>
    </div>
  );
}

function ImageCard({ src, title, text }: { src: string; title: string; text: string }) {
  return (
    <div className="relative h-full w-full overflow-hidden rounded-2xl border-4 border-white shadow-[0_6px_0_rgba(0,20,60,0.35)]">
      <img src={src} alt={title} className="h-full w-full object-cover" />
      {(title || text) && (
        <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-5 py-3 text-left text-white">
          <div className="text-[11px] uppercase tracking-widest opacity-80">{title}</div>
          <div className="text-sm font-semibold">{text}</div>
        </div>
      )}
    </div>
  );
}

const HR_FLOATERS = [
  { icon: Camera, x: '6%', y: '10%', size: 48, anim: 'hr-float', dur: 7 },
  { icon: Aperture, x: '90%', y: '8%', size: 56, anim: 'hr-spin', dur: 18 },
  { icon: Focus, x: '12%', y: '38%', size: 40, anim: 'hr-pulse', dur: 3 },
  { icon: Zap, x: '86%', y: '34%', size: 38, anim: 'hr-flash', dur: 2.6 },
  { icon: Aperture, x: '3%', y: '58%', size: 44, anim: 'hr-spin', dur: 24 },
  { icon: Camera, x: '94%', y: '58%', size: 42, anim: 'hr-float', dur: 8 },
];

const HR_CSS = `
@keyframes hr-float { 0%,100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-14px) rotate(4deg); } }
@keyframes hr-spin { to { transform: rotate(360deg); } }
@keyframes hr-pulse { 0%,100% { transform: scale(1); opacity: .25; } 50% { transform: scale(1.25); opacity: .6; } }
@keyframes hr-flash { 0%,60%,100% { opacity: .15; } 70%,80% { opacity: .75; } }
@keyframes hr-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes hr-ring { 0% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,.7); } 70% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 16px rgba(255,255,255,0); } 100% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,0); } }
@keyframes hr-shine { to { left: 130%; } }
@keyframes hr-bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px) scale(1.2); } }
.hr-card { transition: transform .2s ease; }
.hr-card:hover { transform: translateY(-5px) scale(1.04); }
.hr-card:hover .hr-icon { animation: hr-bounce .5s ease; }
.hr-btn { position: relative; overflow: hidden; transition: transform .2s ease; }
.hr-btn:hover { transform: translateY(-3px) scale(1.04); }
.hr-btn:active { transform: translateY(3px) scale(.97); }
.hr-btn::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.7), transparent); transform: skewX(-20deg); pointer-events: none; }
.hr-btn:hover::after { animation: hr-shine .7s ease; }
`;

export function Hero({ onStart }: Props) {
  const [info, setInfo] = useState<Info | null>(null);

  useEffect(() => {
    fetch(INFO_API)
      .then((r) => r.json())
      .then((j) => setInfo(j.data))
      .catch(() => {});
  }, []);

  return (
    <div className="relative z-10 flex h-screen flex-col overflow-hidden bg-gradient-to-br from-[#082f6b] via-[#0b4a9e] to-[#1a73d9] text-white">
      <style>{HR_CSS}</style>

      {/* Latar: ikon kamera digital yang bergerak pelan */}
      <div className="pointer-events-none absolute inset-0 overflow-hidden">
        <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -right-24 bottom-1/3 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl" />
        {HR_FLOATERS.map((f, i) => {
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

      <section className="relative z-10 flex min-h-0 flex-1 flex-col items-center justify-start px-0 pt-4 text-center">
        <div
          className="mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-[0_5px_0_rgba(0,20,60,0.35)]"
          style={{ animation: 'hr-float 5s ease-in-out infinite' }}
        >
          <Camera size={26} className="text-[#0b4a9e]" strokeWidth={2.2} />
        </div>
        <h1
          className="text-4xl font-extrabold uppercase drop-shadow sm:text-5xl md:text-6xl"
          style={{ letterSpacing: '0.3em', fontFamily: '"Segoe UI", "Helvetica Neue", Arial, sans-serif', animation: 'hr-rise .6s ease-out backwards' }}
        >
          <span className="text-white">Photo</span>
          <span className="text-sky-200">booth</span>
        </h1>
        <p className="mt-3 max-w-md text-sm leading-relaxed text-white/85" style={{ animation: 'hr-rise .6s .1s ease-out backwards' }}>
          Capture moments with beautiful templates, real-time filters, and smart timers.
          Your photos, styled like a real photo studio.
        </p>
        <button
          onClick={() => {
            sound.click();
            onStart();
          }}
          className="hr-btn group mt-5 flex items-center gap-3 rounded-full bg-white px-8 py-3 text-sm font-bold text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)]"
          style={{ animation: 'hr-rise .6s .2s ease-out backwards, hr-ring 2.2s 1s infinite' }}
        >
          Enter for payment
          <span className="transition-transform group-hover:translate-x-1">→</span>
        </button>

        

        {info && (
          <div className="mt-5 flex min-h-0 w-full flex-1 flex-col items-center gap-4">
            <div className="flex flex-wrap justify-center gap-3">
              <InfoCard index={0} icon={<Tag size={18} />} title="Harga" text={`${rupiah(info.price)} / sesi`} />
              <InfoCard index={1} icon={<Clock size={18} />} title="Durasi" text={`${info.session_minutes} menit`} />
              <InfoCard index={2} icon={<Printer size={18} />} title="Cetak" text={`${info.print_copies} foto`} />
              {info.cards
                ?.filter((c) => !c.image)
                .map((c, i) => (
                  <InfoCard key={c.id} index={3 + i} icon={ICONS[c.icon] ?? <Sparkles size={18} />} title={c.title} text={c.description} />
                ))}
            </div>

            {info.cards?.some((c) => c.image) && (
              <div className="flex min-h-0 w-full flex-1 gap-3 px-6 pb-4">
                {info.cards
                  .filter((c) => c.image)
                  .map((c) => (
                    <div key={c.id} className="h-full min-w-0 flex-1">
                      <ImageCard src={c.image as string} title={c.title} text={c.description} />
                    </div>
                  ))}
              </div>
            )}
          </div>
        )}
      </section>

      {/* Contoh template: menyatu dengan latar biru */}
      <div className="relative z-10 text-center text-white">
        <TemplateShowcase />
      </div>
    </div>
  );
}
