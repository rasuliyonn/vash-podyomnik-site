"use client";

import { useState, useEffect, useCallback, useRef } from "react";

const SCROLL_BY = 880;
const STATIC_FALLBACK = Array.from({ length: 71 }, (_, i) => `/works/work-${i + 1}.jpg`);

export function WorksGallery({ images: imagesProp }: { images?: string[] }) {
  const images = imagesProp?.length ? imagesProp : STATIC_FALLBACK;
  const [lightbox, setLightbox] = useState<number | null>(null);
  const [atStart, setAtStart] = useState(true);
  const [atEnd, setAtEnd] = useState(false);
  const stripRef = useRef<HTMLDivElement>(null);
  const drag = useRef({ active: false, startX: 0, scrollLeft: 0, moved: false });

  /* ── lightbox keyboard ── */
  const close = useCallback(() => setLightbox(null), []);
  const prev  = useCallback(() => setLightbox((c) => c === null ? null : c === 0 ? images.length - 1 : c - 1), []);
  const next  = useCallback(() => setLightbox((c) => c === null ? null : c === images.length - 1 ? 0 : c + 1), []);

  useEffect(() => {
    if (lightbox === null) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape")      close();
      if (e.key === "ArrowLeft")   prev();
      if (e.key === "ArrowRight")  next();
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [lightbox, close, prev, next]);

  useEffect(() => {
    document.body.style.overflow = lightbox !== null ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [lightbox]);

  /* ── arrow buttons ── */
  function scrollPrev() { stripRef.current?.scrollBy({ left: -SCROLL_BY, behavior: "smooth" }); }
  function scrollNext() { stripRef.current?.scrollBy({ left:  SCROLL_BY, behavior: "smooth" }); }

  function onScroll() {
    const el = stripRef.current;
    if (!el) return;
    setAtStart(el.scrollLeft <= 4);
    setAtEnd(el.scrollLeft + el.clientWidth >= el.scrollWidth - 4);
  }

  useEffect(() => {
    const el = stripRef.current;
    if (!el) return;
    el.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    return () => el.removeEventListener("scroll", onScroll);
  }, []);

  /* ── mouse drag-to-scroll (desktop only, global listeners) ── */
  useEffect(() => {
    const el = stripRef.current;
    if (!el) return;

    function onMouseDown(e: MouseEvent) {
      drag.current = { active: true, startX: e.clientX, scrollLeft: el!.scrollLeft, moved: false };
      el!.style.cursor = "grabbing";
    }

    function onMouseMove(e: MouseEvent) {
      if (!drag.current.active) return;
      const dx = e.clientX - drag.current.startX;
      if (Math.abs(dx) > 5) {
        drag.current.moved = true;
        el!.scrollLeft = drag.current.scrollLeft - dx;
      }
    }

    function onMouseUp() {
      drag.current.active = false;
      el!.style.cursor = "";
      // reset moved after a tick so click handler can read it
      setTimeout(() => { drag.current.moved = false; }, 0);
    }

    el.addEventListener("mousedown", onMouseDown);
    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
    return () => {
      el.removeEventListener("mousedown", onMouseDown);
      window.removeEventListener("mousemove", onMouseMove);
      window.removeEventListener("mouseup", onMouseUp);
    };
  }, []);

  return (
    <div className="works-gallery">
      <button
        className={`works-arrow works-arrow-prev${atStart ? " works-arrow-hidden" : ""}`}
        aria-label="Назад"
        onClick={scrollPrev}
      >
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M12.5 4L7 10L12.5 16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      </button>

      <div className="works-strip" ref={stripRef}>
        {images.map((src, idx) => (
          <button
            key={src}
            className="works-card"
            aria-label={`Наша работа ${idx + 1}`}
            onClick={() => {
              if (!drag.current.moved) setLightbox(idx);
            }}
          >
            <img src={src} alt={`Выполненный объект ${idx + 1}`} loading="lazy" draggable={false} />
            <span className="works-card-num">{String(idx + 1).padStart(2, "0")}</span>
          </button>
        ))}
      </div>

      <button
        className={`works-arrow works-arrow-next${atEnd ? " works-arrow-hidden" : ""}`}
        aria-label="Вперёд"
        onClick={scrollNext}
      >
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M7.5 4L13 10L7.5 16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
        </svg>
      </button>

      {lightbox !== null && (
        <div
          className="lightbox-overlay"
          role="dialog"
          aria-modal="true"
          aria-label="Просмотр работы"
          onClick={close}
        >
          <button className="lightbox-close" aria-label="Закрыть" onClick={close}>×</button>
          <button className="lightbox-nav prev" aria-label="Предыдущее" onClick={(e) => { e.stopPropagation(); prev(); }}>‹</button>
          <div className="lightbox-content" onClick={(e) => e.stopPropagation()}>
            <img src={images[lightbox]} alt={`Выполненный объект ${lightbox + 1}`} />
            <small>{lightbox + 1} / {images.length}</small>
          </div>
          <button className="lightbox-nav next" aria-label="Следующее" onClick={(e) => { e.stopPropagation(); next(); }}>›</button>
        </div>
      )}
    </div>
  );
}
