import { useEffect, useRef, useState } from "react";
import "./TemplateShowcase.css";

const API = "http://localhost/photobooth/backend/Templates/showcase";

function StripCard({ t }) {
  const ref = useRef(null);
  const move = (e) => {
    const r = ref.current.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width - 0.5;
    const y = (e.clientY - r.top) / r.height - 0.5;
    ref.current.style.transform =
      `rotateY(${x * 26}deg) rotateX(${-y * 26}deg) translateZ(24px)`;
  };
  const leave = () => (ref.current.style.transform = "");

  const hasImg = !!t.templateImage;
  const ratio = t.width && t.height ? `${t.width} / ${t.height}` : "3 / 4";
  const bg = t.background && t.background.type === "solid" ? t.background.color : "#ffffff";

  return (
    <div className="tpl-wrap" onMouseMove={move} onMouseLeave={leave}>
      <div
        className="tpl-card"
        ref={ref}
        style={{
          aspectRatio: ratio,
          backgroundColor: bg,
          ...(hasImg ? { backgroundImage: `url(${t.templateImage})` } : {}),
        }}
      >
        {!hasImg &&
          Array.from({ length: t.photoCount || 1 }).map((_, i) => (
            <div key={i} className="tpl-slot" />
          ))}
      </div>
      <span className="tpl-name">{t.name}</span>
    </div>
  );
}

export default function TemplateShowcase() {
  const [list, setList] = useState([]);
  useEffect(() => {
    fetch(API)
      .then((r) => r.json())
      .then((j) => setList(Array.isArray(j) ? j : j.data || []))
      .catch(() => {});
  }, []);
  if (!list.length) return null;

  const loop = [...list, ...list];

  return (
    <section className="tpl-section">
      <div className="tpl-label">Contoh Template</div>
      <div className="tpl-marquee">
        <div className="tpl-track">
          {loop.map((t, i) => (
            <StripCard key={`${t.id}-${i}`} t={t} />
          ))}
        </div>
      </div>
    </section>
  );
}