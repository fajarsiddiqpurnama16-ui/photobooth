import { useEffect, useMemo, useRef, useState } from 'react';
import { Aperture, Camera, Check, ChevronLeft, ChevronRight, Focus, MoreHorizontal, Zap } from 'lucide-react';
import type { Template, Category } from '@/types';
import { categories } from '@/features/template/templates';
import { fetchTemplates } from '@/features/template/api';
import { TemplatePreview } from '@/features/template/TemplatePreview';
import { sound } from '@/features/common/sound';

interface Props {
  selected: string;
  onSelect: (id: string) => void;
  onContinue: () => void;
}

const PAGE_SIZE_OPTIONS = [8, 12, 16];

const TS_FLOATERS = [
  { icon: Camera, x: '3%', y: '14%', size: 48, anim: 'ts-float', dur: 7 },
  { icon: Aperture, x: '94%', y: '12%', size: 56, anim: 'ts-spin', dur: 18 },
  { icon: Focus, x: '2%', y: '46%', size: 40, anim: 'ts-pulse', dur: 3 },
  { icon: Zap, x: '95%', y: '44%', size: 38, anim: 'ts-flash', dur: 2.6 },
  { icon: Aperture, x: '3%', y: '76%', size: 54, anim: 'ts-spin', dur: 24 },
  { icon: Camera, x: '94%', y: '76%', size: 44, anim: 'ts-float', dur: 8 },
];

const TS_CSS = `
@keyframes ts-float { 0%,100% { transform: translateY(0) rotate(-4deg); } 50% { transform: translateY(-14px) rotate(4deg); } }
@keyframes ts-spin { to { transform: rotate(360deg); } }
@keyframes ts-pulse { 0%,100% { transform: scale(1); opacity: .25; } 50% { transform: scale(1.25); opacity: .6; } }
@keyframes ts-flash { 0%,60%,100% { opacity: .15; } 70%,80% { opacity: .75; } }
@keyframes ts-rise { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes ts-ring { 0% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,.7); } 70% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 16px rgba(255,255,255,0); } 100% { box-shadow: 0 5px 0 rgba(0,20,60,.35), 0 0 0 0 rgba(255,255,255,0); } }
@keyframes ts-shine { to { left: 130%; } }
@keyframes ts-pop { 0% { transform: scale(0); } 70% { transform: scale(1.25); } 100% { transform: scale(1); } }
.ts-btn { position: relative; overflow: hidden; transition: transform .2s ease, background-color .2s ease; }
.ts-btn:hover:not(:disabled) { transform: translateY(-3px) scale(1.04); }
.ts-btn:active:not(:disabled) { transform: translateY(3px) scale(.97); }
.ts-btn::after { content: ''; position: absolute; top: 0; left: -75%; width: 50%; height: 100%; background: linear-gradient(120deg, transparent, rgba(255,255,255,.7), transparent); transform: skewX(-20deg); pointer-events: none; }
.ts-btn:hover:not(:disabled)::after { animation: ts-shine .7s ease; }
.ts-card { transition: transform .25s ease, box-shadow .25s ease; }
.ts-card:hover { transform: translateY(-6px) scale(1.02); }
.ts-card:active { transform: translateY(2px) scale(.98); }
.ts-pop { animation: ts-pop .35s ease-out; }
`;

export function TemplateSelector({ selected, onSelect, onContinue }: Props) {
  const [cat, setCat] = useState<Category | 'all'>('all');
  const [templates, setTemplates] = useState<Template[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Pagination states
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(8);
  const gridTopRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let cancelled = false;

    fetchTemplates()
      .then((data) => {
        if (!cancelled) {
          setTemplates(data);
          setLoading(false);
        }
      })
      .catch((err) => {
        if (!cancelled) {
          console.error('Failed to load templates:', err);
          setError('Failed to load templates. Please try again.');
          setLoading(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, []);

  const filtered = useMemo(
    () => (cat === 'all' ? templates : templates.filter((t) => t.category === cat)),
    [cat, templates],
  );

  // Total pages based on filtered templates
  const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));

  // Reset to page 1 whenever category changes or page size changes
  useEffect(() => {
    setCurrentPage(1);
  }, [cat, pageSize]);

  // Ensure currentPage is within valid range
  useEffect(() => {
    if (currentPage > totalPages) {
      setCurrentPage(totalPages);
    }
  }, [totalPages, currentPage]);

  // Paginated items
  const paginatedTemplates = useMemo(() => {
    const startIndex = (currentPage - 1) * pageSize;
    return filtered.slice(startIndex, startIndex + pageSize);
  }, [filtered, currentPage, pageSize]);

  const handlePageChange = (page: number) => {
    if (page < 1 || page > totalPages || page === currentPage) return;
    sound.click();
    setCurrentPage(page);
    if (gridTopRef.current) {
      gridTopRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  // Helper to build page items: numbers and 'ellipsis'
  const paginationItems = useMemo(() => {
    if (totalPages <= 7) {
      return Array.from({ length: totalPages }, (_, i) => i + 1);
    }

    const items: (number | 'ellipsis-left' | 'ellipsis-right')[] = [];

    // Always show page 1
    items.push(1);

    if (currentPage <= 4) {
      for (let i = 2; i <= Math.min(5, totalPages - 1); i++) {
        items.push(i);
      }
      if (totalPages > 6) {
        items.push('ellipsis-right');
      }
    } else if (currentPage >= totalPages - 3) {
      items.push('ellipsis-left');
      for (let i = totalPages - 4; i < totalPages; i++) {
        items.push(i);
      }
    } else {
      items.push('ellipsis-left');
      items.push(currentPage - 1);
      items.push(currentPage);
      items.push(currentPage + 1);
      items.push('ellipsis-right');
    }

    // Always show last page
    items.push(totalPages);
    return items;
  }, [currentPage, totalPages]);

  const startIndex = (currentPage - 1) * pageSize;
  const endIndex = Math.min(startIndex + pageSize, filtered.length);

  return (
    <div className="relative z-10 min-h-screen overflow-hidden bg-gradient-to-br from-[#082f6b] via-[#0b4a9e] to-[#1a73d9] text-white">
      <style>{TS_CSS}</style>

      {/* Latar: ikon kamera digital yang bergerak pelan */}
      <div className="pointer-events-none fixed inset-0 overflow-hidden">
        <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-white/10 blur-3xl" />
        <div className="absolute -right-24 bottom-1/4 h-80 w-80 rounded-full bg-sky-300/20 blur-3xl" />
        {TS_FLOATERS.map((f, i) => {
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

      <section className="relative z-10 mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
        <div className="text-center" ref={gridTopRef} style={{ animation: 'ts-rise .5s ease-out backwards' }}>
          <h2
            className="text-2xl font-extrabold uppercase text-white drop-shadow sm:text-3xl"
            style={{ letterSpacing: '0.2em' }}
          >
            Choose Your Template
          </h2>
          <p className="mt-3 text-sm text-white/85">
            Select a frame for your photos. You can change it later without retaking.
          </p>
        </div>

        {/* Category Filter Pills */}
        <div className="mt-8 flex flex-wrap justify-center gap-2" style={{ animation: 'ts-rise .5s .1s ease-out backwards' }}>
          {categories.map((c) => (
            <button
              key={c.id}
              onClick={() => {
                sound.click();
                setCat(c.id);
              }}
              className={`ts-btn rounded-full px-5 py-2 text-xs font-bold tracking-wide shadow-[0_4px_0_rgba(0,20,60,0.35)] ${
                cat === c.id
                  ? 'scale-105 bg-white text-[#0b4a9e]'
                  : 'border border-white/30 bg-white/15 text-white hover:bg-white/25'
              }`}
            >
              {c.name}
            </button>
          ))}
        </div>

        {loading && (
          <div className="mt-10 flex flex-col items-center gap-3 text-sm font-semibold text-white/90">
            <Aperture size={40} strokeWidth={1.8} style={{ animation: 'ts-spin 1.6s linear infinite' }} />
            Loading templates...
          </div>
        )}

        {error && (
          <div className="mx-auto mt-10 max-w-md rounded-xl bg-white px-4 py-3 text-center text-sm font-semibold text-red-600">{error}</div>
        )}

        {!loading && !error && (
          <>
            {/* Templates Grid */}
            <div
              className="mt-10 grid auto-cols-[160px] grid-flow-col gap-4 overflow-x-auto px-1 pb-6 pt-2 sm:auto-cols-auto sm:grid-flow-row sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
              style={{ scrollbarWidth: 'thin' }}
            >
              {paginatedTemplates.map((t: Template, idx) => {
                const isSel = selected === t.id;
                return (
                  <button
                    key={t.id}
                    onClick={() => {
                      sound.click();
                      onSelect(t.id);
                    }}
                    className={`ts-card group relative rounded-2xl p-3 text-left ${
                      isSel
                        ? 'bg-white ring-4 ring-sky-300 shadow-[0_8px_0_rgba(0,20,60,0.35),0_16px_40px_rgba(0,20,60,0.45)]'
                        : 'bg-white/95 shadow-[0_6px_0_rgba(0,20,60,0.3)] hover:shadow-[0_8px_0_rgba(0,20,60,0.35),0_16px_40px_rgba(0,20,60,0.4)]'
                    }`}
                    style={{ animation: `ts-rise .5s ${Math.min(idx, 8) * 0.06}s ease-out backwards` }}
                  >
                    {isSel && (
                      <div className="ts-pop absolute -right-2 -top-2 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-[#0b4a9e] text-white shadow-lg ring-2 ring-white">
                        <Check size={16} strokeWidth={3} />
                      </div>
                    )}
                    <div className="flex items-center justify-center rounded-xl bg-sky-50 p-3" style={{ minHeight: 240 }}>
                      <TemplatePreview template={t} />
                    </div>
                    <div className="mt-3 flex items-center justify-between">
                      <span className="truncate pr-2 text-sm font-bold text-[#0b4a9e]">{t.name}</span>
                      <span className="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-sky-600">
                        {t.photoCount} photo{t.photoCount > 1 ? 's' : ''}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>

            {/* Pagination Navigation Bar */}
            {filtered.length > 0 && (
              <div className="mt-6 flex flex-col items-center justify-between gap-4 border-t border-white/25 pt-6 sm:flex-row">
                {/* Left: Summary info */}
                <div className="text-xs text-white/85">
                  Menampilkan <span className="font-bold text-white">{filtered.length > 0 ? startIndex + 1 : 0}</span>–<span className="font-bold text-white">{endIndex}</span> dari{' '}
                  <span className="font-bold text-white">{filtered.length}</span> template
                </div>

                {/* Center: Pagination Controls */}
                <div className="flex flex-wrap items-center justify-center gap-1.5">
                  <button
                    type="button"
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={currentPage <= 1}
                    className="ts-btn flex h-9 items-center gap-1 rounded-lg bg-white px-3 text-xs font-bold text-[#0b4a9e] shadow-[0_3px_0_rgba(0,20,60,0.35)] disabled:pointer-events-none disabled:opacity-40"
                    title="Halaman Sebelumnya"
                  >
                    <ChevronLeft size={15} />
                    <span className="hidden sm:inline">Previous</span>
                  </button>

                  {paginationItems.map((item, idx) => {
                    if (item === 'ellipsis-left' || item === 'ellipsis-right') {
                      return (
                        <span
                          key={`ellipsis-${idx}`}
                          className="flex h-9 w-8 select-none items-center justify-center text-xs text-white/70"
                          title="More pages"
                        >
                          <MoreHorizontal size={14} />
                        </span>
                      );
                    }

                    const isCurrent = currentPage === item;
                    return (
                      <button
                        key={`page-${item}`}
                        type="button"
                        onClick={() => handlePageChange(item)}
                        className={`ts-btn flex h-9 min-w-[36px] items-center justify-center rounded-lg px-2.5 text-xs font-bold shadow-[0_3px_0_rgba(0,20,60,0.35)] ${
                          isCurrent
                            ? 'scale-110 bg-sky-300 text-[#082f6b]'
                            : 'border border-white/30 bg-white/15 text-white hover:bg-white/25'
                        }`}
                      >
                        {item}
                      </button>
                    );
                  })}

                  <button
                    type="button"
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={currentPage >= totalPages}
                    className="ts-btn flex h-9 items-center gap-1 rounded-lg bg-white px-3 text-xs font-bold text-[#0b4a9e] shadow-[0_3px_0_rgba(0,20,60,0.35)] disabled:pointer-events-none disabled:opacity-40"
                    title="Halaman Selanjutnya"
                  >
                    <span className="hidden sm:inline">Next</span>
                    <ChevronRight size={15} />
                  </button>
                </div>

                {/* Right: Items per page selector */}
                <div className="flex items-center gap-2 text-xs text-white/85">
                  <span>Per hal:</span>
                  <select
                    value={pageSize}
                    onChange={(e) => {
                      sound.click();
                      setPageSize(Number(e.target.value));
                    }}
                    className="rounded-lg border-0 bg-white px-2.5 py-1 text-xs font-bold text-[#0b4a9e] outline-none"
                  >
                    {PAGE_SIZE_OPTIONS.map((opt) => (
                      <option key={opt} value={opt}>
                        {opt}
                      </option>
                    ))}
                    <option value={999}>Semua</option>
                  </select>
                </div>
              </div>
            )}
          </>
        )}

        {/* Continue Action */}
        <div className="mt-10 flex justify-center">
          <button
            onClick={() => {
              sound.click();
              onContinue();
            }}
            disabled={!selected}
            className="ts-btn flex items-center gap-2 rounded-full bg-white px-10 py-3.5 text-sm font-bold tracking-wide text-[#0b4a9e] shadow-[0_5px_0_rgba(0,20,60,0.35)] disabled:cursor-not-allowed disabled:opacity-40"
            style={selected ? { animation: 'ts-ring 2.2s 1s infinite' } : undefined}
          >
            <Camera size={18} strokeWidth={2.5} /> Open Camera →
          </button>
        </div>
      </section>
    </div>
  );
}
