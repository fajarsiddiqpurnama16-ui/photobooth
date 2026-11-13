import { BG_EFFECTS, type BgEffectId } from '@/features/camera/bgEffects';
import { sound } from '@/features/common/sound';
import type { SegmenterStatus } from '@/features/camera/useBodySegmenter';

interface Props {
  active: BgEffectId;
  onSelect: (id: BgEffectId) => void;
  status: SegmenterStatus;
}

/** Preview thumbnail untuk tiap efek background */
function BgThumb({ effect }: { effect: typeof BG_EFFECTS[0] }) {
  const isNone = effect.id === 'bg-none';
  const isBlur = !!effect.blur;

  const bgStyle = isNone
    ? { background: 'repeating-linear-gradient(45deg, #555 0, #555 4px, #333 4px, #333 8px)' }
    : isBlur
    ? { background: 'linear-gradient(135deg, #667eea, #764ba2)', filter: 'blur(3px)' }
    : effect.gradient
    ? { background: effect.gradient }
    : { background: '#333' };

  return (
    <div
      className="relative h-10 w-10 overflow-hidden rounded-lg border border-white/20 shadow"
      style={bgStyle}
    >
      {/* Siluet orang kecil di thumbnail */}
      <div
        className="absolute bottom-0 left-1/2 -translate-x-1/2"
        style={{
          width: 14,
          height: 22,
          background: 'rgba(0,0,0,0.5)',
          borderRadius: '40% 40% 0 0',
        }}
      />
      {isBlur && (
        <div className="absolute inset-0 backdrop-blur-sm" />
      )}
    </div>
  );
}

export function BgEffectStrip({ active, onSelect, status }: Props) {
  return (
    <div>
      <div className="flex gap-2 overflow-x-auto pb-1" style={{ scrollbarWidth: 'thin' }}>
        {BG_EFFECTS.map((e) => (
          <button
            key={e.id}
            type="button"
            onClick={() => {
              sound.click();
              onSelect(e.id);
            }}
            className={`flex shrink-0 flex-col items-center gap-1 rounded-lg px-2 py-2 transition-all ${
              active === e.id
                ? 'bg-white/20 ring-2 ring-white/60 scale-105 shadow-md'
                : 'opacity-70 hover:opacity-100 hover:bg-white/5'
            }`}
          >
            <BgThumb effect={e} />
            <span className="text-[9px] font-medium tracking-wide text-neutral-300 max-w-[52px] text-center leading-tight">
              {e.name}
            </span>
          </button>
        ))}
      </div>

      {active !== 'bg-none' && status === 'loading' && (
        <p className="mt-2 text-[10px] text-neutral-400">
          ⏳ Memuat segmentasi… (pertama kali butuh koneksi internet)
        </p>
      )}
      {active !== 'bg-none' && status === 'error' && (
        <p className="mt-2 text-[10px] text-rose-300">
          ❌ Efek background gagal dimuat. Cek koneksi lalu coba lagi.
        </p>
      )}
    </div>
  );
}

