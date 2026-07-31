"use client";

import { useState, useEffect, useCallback } from "react";

type Image = {
  id: number;
  url: string;
  caption: string;
  imageType: string;
};

const TAB_LABELS: Record<string, string> = {
  gallery: "Галерея",
  installation: "Монтаж",
  project: "Объекты",
};

export function Gallery({ images }: { images: Image[] }) {
  const tabs = Array.from(new Set(images.map((i) => i.imageType))).filter(
    (t) => TAB_LABELS[t],
  );
  const [activeTab, setActiveTab] = useState(tabs[0] ?? "gallery");
  const [lightbox, setLightbox] = useState<number | null>(null);

  const filtered = images.filter((i) => i.imageType === activeTab);

  const close = useCallback(() => setLightbox(null), []);

  const prev = useCallback(() => {
    setLightbox((cur) =>
      cur === null ? null : cur === 0 ? filtered.length - 1 : cur - 1,
    );
  }, [filtered.length]);

  const next = useCallback(() => {
    setLightbox((cur) =>
      cur === null ? null : cur === filtered.length - 1 ? 0 : cur + 1,
    );
  }, [filtered.length]);

  useEffect(() => {
    if (lightbox === null) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") prev();
      if (e.key === "ArrowRight") next();
    }
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, [lightbox, close, prev, next]);

  if (images.length === 0) return null;

  return (
    <div className="eq-gallery">
      {tabs.length > 1 && (
        <div className="gallery-tabs" role="tablist">
          {tabs.map((tab) => (
            <button
              key={tab}
              role="tab"
              aria-selected={activeTab === tab}
              className={activeTab === tab ? "active" : ""}
              onClick={() => setActiveTab(tab)}
            >
              {TAB_LABELS[tab] ?? tab}
            </button>
          ))}
        </div>
      )}

      <div className="gallery-grid" role="list">
        {filtered.map((img, idx) => (
          <button
            key={img.id}
            className="gallery-thumb"
            role="listitem"
            aria-label={img.caption || `Фото ${idx + 1}`}
            onClick={() => setLightbox(idx)}
          >
            <img src={img.url} alt={img.caption} loading="lazy" />
            {img.caption && <span>{img.caption}</span>}
          </button>
        ))}
      </div>

      {lightbox !== null && (
        <div
          className="lightbox-overlay"
          role="dialog"
          aria-modal="true"
          aria-label="Просмотр изображения"
          onClick={close}
        >
          <button
            className="lightbox-close"
            aria-label="Закрыть"
            onClick={close}
          >
            ×
          </button>
          <button
            className="lightbox-nav prev"
            aria-label="Предыдущее"
            onClick={(e) => { e.stopPropagation(); prev(); }}
          >
            ‹
          </button>
          <div
            className="lightbox-content"
            onClick={(e) => e.stopPropagation()}
          >
            <img
              src={filtered[lightbox].url}
              alt={filtered[lightbox].caption}
            />
            {filtered[lightbox].caption && (
              <p className="lightbox-caption">{filtered[lightbox].caption}</p>
            )}
            <small>{lightbox + 1} / {filtered.length}</small>
          </div>
          <button
            className="lightbox-nav next"
            aria-label="Следующее"
            onClick={(e) => { e.stopPropagation(); next(); }}
          >
            ›
          </button>
        </div>
      )}
    </div>
  );
}
