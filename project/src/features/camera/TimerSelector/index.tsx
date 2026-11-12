interface Props {
  value: number;
  onChange: (v: number) => void;
}

const OPTIONS = [
  { label: 'OFF', value: 0 },
  { label: '3s', value: 3 },
  { label: '5s', value: 5 },
  { label: '10s', value: 10 },
];

export function TimerSelector({ value, onChange }: Props) {
  return (
    <div className="flex items-center gap-1.5 rounded-full bg-white/10 p-1 backdrop-blur">
      {OPTIONS.map((o) => (
        <button
          key={o.value}
          onClick={() => onChange(o.value)}
          className={`rounded-full px-3 py-1.5 text-xs font-semibold tracking-wide transition-all ${
            value === o.value
              ? 'bg-white text-neutral-900'
              : 'text-neutral-300 hover:text-white'
          }`}
        >
          {o.label}
        </button>
      ))}
    </div>
  );
}

export function CountdownOverlay({ count }: { count: number }) {
  if (count <= 0) return null;
  return (
    <div className="absolute inset-0 z-30 flex items-center justify-center">
      <div
        key={count}
        className="text-7xl font-bold text-white drop-shadow-[0_0_20px_rgba(255,255,255,0.5)] sm:text-8xl"
        style={{ animation: 'countdownPop 1s ease-out' }}
      >
        {count}
      </div>
    </div>
  );
}

export function ShutterFlash({ show }: { show: boolean }) {
  if (!show) return null;
  return (
    <div
      className="absolute inset-0 z-40 bg-white"
      style={{ animation: 'shutterFlash 0.3s ease-out forwards' }}
    />
  );
}
