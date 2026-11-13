import { EFFECTS, needsFaces, type EffectId } from '@/features/camera/effects';
import { useCustomEffectDefs } from '@/features/camera/customEffects';
import type { TrackerStatus } from '@/features/camera/useFaceTracker';
import { sound } from '@/features/common/sound';

interface Props {
  active: EffectId;
  onSelect: (id: EffectId) => void;
  status: TrackerStatus;
}

export function EffectStrip({ active, onSelect, status }: Props) {
  // Ambil efek custom dari database admin
  const custom = useCustomEffectDefs();

  // Gabungkan efek bawaan dan efek custom
  const list = [...EFFECTS, ...custom];

  return (
    <div>
      <div className="grid grid-cols-4 gap-2 overflow-y-auto pr-0.5" style={{ scrollbarWidth: 'thin', maxHeight: '999px' }}>
        {list.map((e) => (
          <button
            key={e.id}
            type="button"
            onClick={() => {
              sound.click();
              onSelect(e.id);
            }}
            className={`flex flex-col items-center gap-1.5 rounded-xl px-1 py-3 transition-all ${
              active === e.id
                ? 'bg-white/20 ring-2 ring-white/60 scale-105 shadow-md'
                : 'opacity-70 hover:opacity-100 hover:bg-white/10'
            }`}
          >
            <span className="text-2xl leading-none">{e.emoji}</span>
            <span className="text-[9px] font-semibold tracking-wide text-neutral-200 text-center leading-tight line-clamp-2">
              {e.name}
            </span>
          </button>
        ))}
      </div>

      {needsFaces(active) && status === 'loading' && (
        <p className="mt-2 text-[10px] text-neutral-400">
          Memuat deteksi wajah… (pertama kali butuh koneksi internet)
        </p>
      )}

      {needsFaces(active) && status === 'error' && (
        <p className="mt-2 text-[10px] text-rose-300">
          Efek wajah gagal dimuat. Cek koneksi internet lalu pilih efeknya lagi.
        </p>
      )}
    </div>
  );
}