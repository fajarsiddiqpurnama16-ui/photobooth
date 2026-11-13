import { useRef, useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { FILTERS } from '@/features/camera/filters';
import { sound } from '@/features/common/sound';

interface Props {
  active: string;
  onSelect: (id: string) => void;
}

function Thumb({ id, name }: { id: string; name: string }) {
  return (
    <div
      className="relative h-20 w-24 overflow-hidden rounded-lg bg-gradient-to-br from-neutral-700 to-neutral-900 pointer-events-none select-none"
      style={{ filter: FILTERS.find((f) => f.id === id)?.css }}
    >
      <div className="absolute inset-0 flex items-end p-2">
        <span className="text-[9px] font-semibold leading-none text-white drop-shadow">{name}</span>
      </div>
    </div>
  );
}

export function FilterStrip({ active, onSelect }: Props) {
  const scrollRef = useRef<HTMLDivElement>(null);
  const [isDragging, setIsDragging] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeft, setScrollLeft] = useState(0);

  const scroll = (direction: 'left' | 'right') => {
    if (scrollRef.current) {
      const amount = direction === 'left' ? -200 : 200;
      scrollRef.current.scrollBy({ left: amount, behavior: 'smooth' });
    }
  };

  const handleWheel = (e: React.WheelEvent) => {
    if (scrollRef.current) {
      scrollRef.current.scrollLeft += e.deltaY;
    }
  };

  const handleMouseDown = (e: React.MouseEvent) => {
    if (!scrollRef.current) return;
    setIsDragging(true);
    setStartX(e.pageX - scrollRef.current.offsetLeft);
    setScrollLeft(scrollRef.current.scrollLeft);
  };

  const handleMouseMove = (e: React.MouseEvent) => {
    if (!isDragging || !scrollRef.current) return;
    e.preventDefault();
    const x = e.pageX - scrollRef.current.offsetLeft;
    const walk = (x - startX) * 1.5;
    scrollRef.current.scrollLeft = scrollLeft - walk;
  };

  const handleMouseUp = () => {
    setIsDragging(false);
  };

  return (
    <div className="relative w-full flex items-center py-1">
      {/* Scroll Left Button for PC */}
      <button
        type="button"
        onClick={(e) => {
          e.stopPropagation();
          sound.click();
          scroll('left');
        }}
        className="shrink-0 z-30 mr-1.5 flex h-8 w-8 items-center justify-center rounded-full bg-neutral-800 text-white shadow-lg ring-1 ring-white/20 transition-all hover:bg-neutral-700 hover:scale-105 active:scale-95 cursor-pointer"
        title="Geser Efek Kiri"
      >
        <ChevronLeft size={18} />
      </button>

      {/* Filter Scrollable List */}
      <div
        ref={scrollRef}
        onWheel={handleWheel}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseUp}
        className="flex flex-1 gap-2 overflow-x-auto pb-1 scroll-smooth select-none cursor-grab active:cursor-grabbing scrollbar-hide"
        style={{ scrollbarWidth: 'thin' }}
      >
        {FILTERS.map((f) => (
          <button
            key={f.id}
            onClick={() => {
              sound.click();
              onSelect(f.id);
            }}
            className={`flex shrink-0 flex-col items-center gap-1.5 rounded-lg p-1 transition-all ${
              active === f.id
                ? 'bg-white/20 ring-2 ring-white/60 scale-105 shadow-md'
                : 'opacity-65 hover:opacity-100 hover:bg-white/5'
            }`}
          >
            <Thumb id={f.id} name={f.name} />
            <span className="text-[11px] font-semibold tracking-wide text-neutral-200">{f.name}</span>
          </button>
        ))}
      </div>

      {/* Scroll Right Button for PC */}
      <button
        type="button"
        onClick={(e) => {
          e.stopPropagation();
          sound.click();
          scroll('right');
        }}
        className="shrink-0 z-30 ml-1.5 flex h-8 w-8 items-center justify-center rounded-full bg-neutral-800 text-white shadow-lg ring-1 ring-white/20 transition-all hover:bg-neutral-700 hover:scale-105 active:scale-95 cursor-pointer"
        title="Geser Efek Kanan"
      >
        <ChevronRight size={18} />
      </button>
    </div>
  );
}
