import { useEffect, useRef, useState } from 'react';
import type { Template } from '@/types';

/** Mode live: dipakai di halaman kamera supaya preview ikut berubah saat foto diambil. */
export interface LiveState {
  activeIndex: number;            // slot yang sedang difoto (0, 1, 2, ...)
  photos: (string | null)[];      // hasil foto per slot (dataURL), null = belum diambil
  stream: MediaStream | null;     // stream kamera yang sama dengan video utama
  mirrored?: boolean;
  filterCss?: string;             // CSS filter yang sedang dipilih (opsional)
}

interface Props {
  template: Template;
  compact?: boolean;
  live?: LiveState;
  /** Batas tinggi preview dalam px. Default 220 untuk strip, tanpa batas untuk lainnya. */
  maxHeight?: number;
  /** Isi penuh kotak induk (induk harus punya style containerType: 'size'). */
  fit?: boolean;
}

function getSlotClipStyle(s: Template['photoSlots'][0]): React.CSSProperties {
  const shape = s.shape || 'rectangle';
  switch (shape) {
    case 'circle':
      return { borderRadius: '50%', clipPath: 'circle(50% at 50% 50%)' };
    case 'oval':
      return { borderRadius: '50%', clipPath: 'ellipse(50% 50% at 50% 50%)' };
    case 'triangle':
      return { clipPath: 'polygon(50% 0%, 0% 100%, 100% 100%)' };
    case 'diamond':
      return { clipPath: 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)' };
    case 'custom':
      if (s.points && s.points.length >= 3) {
        return { clipPath: `polygon(${s.points.map((p) => `${p.x}% ${p.y}%`).join(', ')})` };
      }
      return { borderRadius: s.borderRadius ?? 0 };
    case 'heart':
      return { clipPath: 'polygon(50% 15%, 65% 0%, 85% 0%, 100% 15%, 100% 35%, 85% 55%, 50% 95%, 15% 55%, 0% 35%, 0% 15%, 15% 0%, 35% 0%)' };
    case 'star':
      return { clipPath: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)' };
    case 'arch':
      return { borderRadius: '50% 50% 0 0 / 30% 30% 0 0' };
    case 'rectangle':
    case 'rounded':
    default:
      return { borderRadius: s.borderRadius ?? 0 };
  }
}

function slotStyle(t: Template, i: number): React.CSSProperties {
  const s = t.photoSlots[i];
  if (!s) return {};
  const sx = t.width;
  const sy = t.height;
  const clip = getSlotClipStyle(s);
  return {
    left: `${(s.x / sx) * 100}%`,
    top: `${(s.y / sy) * 100}%`,
    width: `${(s.width / sx) * 100}%`,
    height: `${(s.height / sy) * 100}%`,
    transform: s.rotation ? `rotate(${s.rotation}deg)` : undefined,
    ...clip,
  };
}

function LiveVideo({ stream, mirrored, filterCss }: { stream: MediaStream | null; mirrored?: boolean; filterCss?: string }) {
  const ref = useRef<HTMLVideoElement>(null);
  useEffect(() => {
    if (ref.current) ref.current.srcObject = stream;
  }, [stream]);
  return (
    <video
      ref={ref}
      autoPlay
      muted
      playsInline
      className="h-full w-full object-cover"
      style={{ transform: mirrored ? 'scaleX(-1)' : undefined, filter: filterCss || undefined }}
    />
  );
}

function slotHasContent(live: LiveState | undefined, i: number): boolean {
  if (!live) return false;
  return Boolean(live.photos[i]) || (i === live.activeIndex && Boolean(live.stream));
}

function SlotContent({ live, i }: { live: LiveState; i: number }) {
  const photo = live.photos[i];
  if (photo) return <img src={photo} alt={`Foto ${i + 1}`} className="h-full w-full object-cover" />;
  return <LiveVideo stream={live.stream} mirrored={live.mirrored ?? true} filterCss={live.filterCss} />;
}

export function TemplatePreview({ template, live, maxHeight, fit }: Props) {
  const [imgError, setImgError] = useState(false);
  const bg =
    template.background?.type === 'solid'
      ? template.background.color
      : template.background?.direction === 'horizontal'
        ? `linear-gradient(to right, ${template.background?.colors?.join(', ')})`
        : template.background?.direction === 'diagonal'
          ? `linear-gradient(to bottom right, ${template.background?.colors?.join(', ')})`
          : `linear-gradient(to bottom, ${template.background?.colors?.join(', ')})`;

  const aspect = template.width / template.height;
  const isStrip = aspect < 0.6;
  const hasImage = Boolean(template.templateImage) && !imgError;

  const mh = maxHeight ?? (isStrip ? 220 : undefined);
  const sizeStyle: React.CSSProperties = fit
    ? {
        aspectRatio: `${template.width} / ${template.height}`,
        width: `min(100cqw, calc(100cqh * ${aspect}))`,
      }
    : {
        aspectRatio: `${template.width} / ${template.height}`,
        maxHeight: mh,
        maxWidth: mh ? Math.round(mh * aspect) : '100%',
        margin: mh ? '0 auto' : undefined,
      };
  const activeOutline = (i: number): React.CSSProperties =>
    live && i === live.activeIndex && !live.photos[i] ? { outline: '2px solid #38bdf8', outlineOffset: -2 } : {};

  if (hasImage) {
    return (
      <div
        className="relative w-full overflow-hidden rounded-md shadow-sm flex items-center justify-center bg-neutral-900/40"
        style={sizeStyle}
      >
        <img
          src={template.templateImage}
          alt={template.name}
          className="relative w-full h-full object-contain drop-shadow-md select-none pointer-events-none"
          crossOrigin="anonymous"
          onError={() => setImgError(true)}
        />
        {/* Mode live: slot foto digambar DI ATAS gambar template (PNG-nya tidak punya lubang transparan sungguhan) */}
        {live &&
          template.photoSlots.map((_, i) => (
            <div key={i} className="absolute overflow-hidden" style={{ ...slotStyle(template, i), ...activeOutline(i) }}>
              {slotHasContent(live, i) && <SlotContent live={live} i={i} />}
            </div>
          ))}
      </div>
    );
  }

  return (
    <div
      className="relative w-full overflow-hidden rounded-md shadow-sm"
      style={{
        background: bg,
        ...sizeStyle,
        border: template.border ? `${Math.max(2, template.border.width)}px solid ${template.border.color}` : undefined,
      }}
    >
      {template.photoSlots.map((_, i) => {
        const pb = template.photoBorder;
        return (
          <div
            key={i}
            className="absolute overflow-hidden"
            style={{
              ...slotStyle(template, i),
              background: pb ? pb.color : '#e5e5e5',
              border: pb && pb.width > 0 ? `${Math.max(1, pb.width)}px solid ${pb.color}` : undefined,
              padding: pb?.padding ? pb.padding : 0,
              boxShadow: 'inset 0 0 0 1px rgba(0,0,0,0.06)',
              ...activeOutline(i),
            }}
          >
            {live && slotHasContent(live, i) ? (
              <SlotContent live={live} i={i} />
            ) : (
              <div
                className="w-full h-full"
                style={{
                  background: 'repeating-linear-gradient(45deg, #d4d4d4 0, #d4d4d4 8px, #c4c4c4 8px, #c4c4c4 16px)',
                  borderRadius: (template.photoSlots[i].borderRadius ?? 0) - 2,
                  opacity: 0.5,
                }}
              />
            )}
          </div>
        );
      })}
      {template.text?.map((t, i) => (
        <div
          key={`t-${i}`}
          className="absolute whitespace-nowrap"
          style={{
            left: `${(t.x / template.width) * 100}%`,
            top: `${(t.y / template.height) * 100}%`,
            transform: `translate(-50%, -50%)${t.rotation ? ` rotate(${t.rotation}deg)` : ''}`,
            fontSize: Math.max(5, (t.fontSize ?? 12) * 0.32),
            color: t.color,
            fontWeight: t.fontWeight as React.CSSProperties['fontWeight'],
            letterSpacing: (t.letterSpacing ?? 0) * 0.3,
            textAlign: t.align ?? 'center',
            fontFamily: t.fontFamily ?? 'sans-serif',
          }}
        >
          {t.text}
        </div>
      ))}
    </div>
  );
}
