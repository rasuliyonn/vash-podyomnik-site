"use client";

import { useState, useRef } from "react";
import type {
  SiteContent,
  HeroContent,
  HeaderContent,
  EquipmentSectionContent,
  EquipmentCard,
  ObjectSectionContent,
  ProcessContent,
  GuaranteesContent,
  CalculatorContent,
  FaqContent,
  FooterContent,
} from "../../../lib/default-content";

async function saveSection(key: string, value: unknown): Promise<string | null> {
  try {
    const res = await fetch("/api/admin/content", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ key, value }),
    });
    if (!res.ok) {
      const data = (await res.json()) as { error?: string };
      return data.error ?? "Ошибка сохранения";
    }
    return null;
  } catch (e) {
    return String(e);
  }
}

function SaveStatus({ status }: { status: "idle" | "saving" | "ok" | string }) {
  if (status === "idle" || status === "saving") return null;
  if (status === "ok") return <span className="admin-save-ok">✓ Сохранено</span>;
  return <span className="admin-save-error">✗ {status}</span>;
}

function useSectionSave(key: string, getValue: () => unknown) {
  const [status, setStatus] = useState<"idle" | "saving" | "ok" | string>("idle");

  async function save() {
    setStatus("saving");
    const err = await saveSection(key, getValue());
    setStatus(err ?? "ok");
    if (!err) setTimeout(() => setStatus("idle"), 3000);
  }

  return { status, save };
}

// Simple string list editor (one per line textarea)
function StringListEditor({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string[];
  onChange: (v: string[]) => void;
}) {
  return (
    <label className="admin-label">
      <span>{label}</span>
      <textarea
        rows={Math.max(3, value.length + 1)}
        value={value.join("\n")}
        onChange={(e) => onChange(e.target.value.split("\n").map((s) => s.trimEnd()).filter(Boolean))}
      />
      <small>По одному на строку</small>
    </label>
  );
}

// Object list editor (array of objects with fixed fields)
function ObjectListEditor({
  label,
  value,
  onChange,
  fields,
  newItem,
}: {
  label: string;
  value: Record<string, string>[];
  onChange: (v: Record<string, string>[]) => void;
  fields: Array<{ key: string; label: string; multiline?: boolean }>;
  newItem: Record<string, string>;
}) {
  function update(i: number, k: string, v: string) {
    onChange(value.map((item, idx) => (idx === i ? { ...item, [k]: v } : item)));
  }
  function remove(i: number) {
    onChange(value.filter((_, idx) => idx !== i));
  }
  function add() {
    onChange([...value, { ...newItem }]);
  }

  return (
    <div className="admin-object-list">
      <div className="admin-specs-header">
        <span>{label}</span>
        <button type="button" className="admin-action-btn" onClick={add}>
          + Добавить
        </button>
      </div>
      {value.map((item, i) => (
        <div key={i} className="admin-object-item">
          {fields.map((f) => (
            <label key={f.key} className="admin-label">
              <span>{f.label}</span>
              {f.multiline ? (
                <textarea
                  rows={3}
                  value={String(item[f.key] ?? "")}
                  onChange={(e) => update(i, f.key, e.target.value)}
                />
              ) : (
                <input
                  value={String(item[f.key] ?? "")}
                  onChange={(e) => update(i, f.key, e.target.value)}
                />
              )}
            </label>
          ))}
          <button
            type="button"
            className="admin-action-btn danger"
            onClick={() => remove(i)}
          >
            Удалить
          </button>
        </div>
      ))}
    </div>
  );
}

// ── Client-side image resize to JPEG 80%, max 1200px ──
async function resizeImage(file: File): Promise<string> {
  return new Promise((resolve) => {
    const img = new Image();
    const objectUrl = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(objectUrl);
      const maxW = 1200;
      const scale = img.width > maxW ? maxW / img.width : 1;
      const canvas = document.createElement("canvas");
      canvas.width = Math.round(img.width * scale);
      canvas.height = Math.round(img.height * scale);
      const ctx = canvas.getContext("2d")!;
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      resolve(canvas.toDataURL("image/jpeg", 0.8));
    };
    img.src = objectUrl;
  });
}

function ImageUploadField({
  label,
  imgKey,
  hasImage,
}: {
  label: string;
  imgKey: string;
  hasImage: boolean;
}) {
  const [preview, setPreview] = useState<string | null>(null);
  const [status, setStatus] = useState<"idle" | "saving" | "ok" | string>("idle");
  const inputRef = useRef<HTMLInputElement>(null);

  async function handleFile(file: File) {
    const dataUrl = await resizeImage(file);
    setPreview(dataUrl);
    setStatus("idle");
  }

  async function handleSave() {
    if (!preview) return;
    setStatus("saving");
    const err = await saveSection(imgKey, preview);
    setStatus(err ?? "ok");
    if (!err) setTimeout(() => setStatus("idle"), 3000);
  }

  return (
    <div className="admin-image-upload-field">
      <span className="admin-label-text">{label}</span>
      {(hasImage || preview) && (
        <img
          src={preview ?? `/api/content-image?key=${imgKey}`}
          alt="Текущее фото"
          style={{ display: "block", maxWidth: 320, maxHeight: 200, objectFit: "cover", borderRadius: 6, marginBottom: 8 }}
        />
      )}
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        style={{ display: "none" }}
        onChange={async (e) => {
          const file = e.target.files?.[0];
          if (file) await handleFile(file);
          e.target.value = "";
        }}
      />
      <div style={{ display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" }}>
        <button
          type="button"
          className="admin-action-btn"
          onClick={() => inputRef.current?.click()}
        >
          📎 {hasImage || preview ? "Заменить фото" : "Загрузить фото"}
        </button>
        {preview && (
          <button
            type="button"
            className="primary-button"
            onClick={handleSave}
            disabled={status === "saving"}
            style={{ marginTop: 0 }}
          >
            {status === "saving" ? "Сохранение…" : "Сохранить фото"} <span>✓</span>
          </button>
        )}
        <SaveStatus status={status} />
      </div>
    </div>
  );
}

export function ContentEditor({ initialContent }: { initialContent: SiteContent }) {
  // ─── Header ───
  const [header, setHeader] = useState<HeaderContent>(initialContent.header);
  const headerSave = useSectionSave("header", () => header);

  // ─── Hero ───
  const [hero, setHero] = useState<HeroContent>(initialContent.hero);
  const heroSave = useSectionSave("hero", () => hero);

  // ─── Trust Strip ───
  const [trustStrip, setTrustStrip] = useState<string[]>(initialContent.trust_strip);
  const trustSave = useSectionSave("trust_strip", () => trustStrip);

  // ─── Equipment Section ───
  const [equipSection, setEquipSection] = useState<EquipmentSectionContent>(
    initialContent.equipment_section,
  );
  const equipSectionSave = useSectionSave("equipment_section", () => equipSection);

  // ─── Equipment Cards ───
  const [equipCards, setEquipCards] = useState<EquipmentCard[]>(initialContent.equipment_cards);
  const equipCardsSave = useSectionSave("equipment_cards", () => equipCards);

  // ─── Object Section ───
  const [objSection, setObjSection] = useState<ObjectSectionContent>(initialContent.object_section);
  const objSave = useSectionSave("object_section", () => objSection);

  // ─── Process ───
  const [process, setProcess] = useState<ProcessContent>(initialContent.process);
  const processSave = useSectionSave("process", () => process);

  // ─── Guarantees ───
  const [guarantees, setGuarantees] = useState<GuaranteesContent>(initialContent.guarantees);
  const guaranteesSave = useSectionSave("guarantees", () => guarantees);

  // ─── Calculator ───
  const [calc, setCalc] = useState<CalculatorContent>(initialContent.calculator);
  const calcSave = useSectionSave("calculator", () => calc);

  // ─── FAQ ───
  const [faq, setFaq] = useState<FaqContent>(initialContent.faq);
  const faqSave = useSectionSave("faq", () => faq);

  // ─── Footer ───
  const [footer, setFooter] = useState<FooterContent>(initialContent.footer);
  const footerSave = useSectionSave("footer", () => footer);

  return (
    <div className="admin-content-editor">

      {/* ═══ HEADER ═══ */}
      <section className="admin-content-section">
        <h2>Шапка сайта</h2>
        <ImageUploadField label="Логотип" imgKey="img_logo" hasImage={!!initialContent.img_logo} />
        <div className="admin-form-row" style={{ marginTop: 16 }}>
          <label className="admin-label">
            <span>Название бренда</span>
            <input value={header.brandName} onChange={(e) => setHeader({ ...header, brandName: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Подзаголовок бренда</span>
            <input value={header.brandSubtitle} onChange={(e) => setHeader({ ...header, brandSubtitle: e.target.value })} />
          </label>
        </div>
        <label className="admin-label">
          <span>Текст кнопки «Получить расчёт»</span>
          <input value={header.ctaText} onChange={(e) => setHeader({ ...header, ctaText: e.target.value })} />
        </label>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={headerSave.save} disabled={headerSave.status === "saving"}>
            {headerSave.status === "saving" ? "Сохранение…" : "Сохранить шапку"} <span>✓</span>
          </button>
          <SaveStatus status={headerSave.status} />
        </div>
      </section>

      {/* ═══ HERO ═══ */}
      <section className="admin-content-section">
        <h2>Главный экран (Герой)</h2>
        <ImageUploadField label="Фоновое фото героя" imgKey="img_hero" hasImage={!!initialContent.img_hero} />
        <label className="admin-label" style={{ marginTop: 16 }}>
          <span>Надпись над заголовком (eyebrow)</span>
          <input value={hero.eyebrow} onChange={(e) => setHero({ ...hero, eyebrow: e.target.value })} />
        </label>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Заголовок H1 (первая строка)</span>
            <input value={hero.h1} onChange={(e) => setHero({ ...hero, h1: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Заголовок H1 (курсив, вторая строка)</span>
            <input value={hero.h1em} onChange={(e) => setHero({ ...hero, h1em: e.target.value })} />
          </label>
        </div>
        <label className="admin-label">
          <span>Подзаголовок (lead)</span>
          <textarea rows={3} value={hero.lead} onChange={(e) => setHero({ ...hero, lead: e.target.value })} />
        </label>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Кнопка 1 (основная)</span>
            <input value={hero.ctaPrimary} onChange={(e) => setHero({ ...hero, ctaPrimary: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Кнопка 2 (ссылка)</span>
            <input value={hero.ctaSecondary} onChange={(e) => setHero({ ...hero, ctaSecondary: e.target.value })} />
          </label>
        </div>
        <div className="admin-specs-header"><span>Метрики (3 блока)</span></div>
        {hero.metrics.map((m, i) => (
          <div key={i} className="admin-form-row">
            <label className="admin-label">
              <span>Метрика {i + 1} — значение</span>
              <input value={m.value} onChange={(e) => setHero({ ...hero, metrics: hero.metrics.map((x, j) => j === i ? { ...x, value: e.target.value } : x) })} />
            </label>
            <label className="admin-label">
              <span>Метрика {i + 1} — подпись</span>
              <input value={m.label} onChange={(e) => setHero({ ...hero, metrics: hero.metrics.map((x, j) => j === i ? { ...x, label: e.target.value } : x) })} />
            </label>
          </div>
        ))}
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Визуальный блок — верхняя надпись</span>
            <input value={hero.visualTop} onChange={(e) => setHero({ ...hero, visualTop: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Визуальный блок — нижняя надпись</span>
            <input value={hero.visualBottom} onChange={(e) => setHero({ ...hero, visualBottom: e.target.value })} />
          </label>
        </div>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Карточка «Параметры» — метка</span>
            <input value={hero.capacityLabel} onChange={(e) => setHero({ ...hero, capacityLabel: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Карточка «Параметры» — значение</span>
            <input value={hero.capacityValue} onChange={(e) => setHero({ ...hero, capacityValue: e.target.value })} />
          </label>
        </div>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={heroSave.save} disabled={heroSave.status === "saving"}>
            {heroSave.status === "saving" ? "Сохранение…" : "Сохранить герой"} <span>✓</span>
          </button>
          <SaveStatus status={heroSave.status} />
        </div>
      </section>

      {/* ═══ TRUST STRIP ═══ */}
      <section className="admin-content-section">
        <h2>Доверительная полоса</h2>
        <StringListEditor label="Пункты (по одному на строку)" value={trustStrip} onChange={setTrustStrip} />
        <div className="admin-section-footer">
          <button className="primary-button" onClick={trustSave.save} disabled={trustSave.status === "saving"}>
            {trustSave.status === "saving" ? "Сохранение…" : "Сохранить"} <span>✓</span>
          </button>
          <SaveStatus status={trustSave.status} />
        </div>
      </section>

      {/* ═══ EQUIPMENT SECTION HEADING ═══ */}
      <section className="admin-content-section">
        <h2>Секция оборудования — заголовок</h2>
        <label className="admin-label">
          <span>Метка секции</span>
          <input value={equipSection.label} onChange={(e) => setEquipSection({ ...equipSection, label: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Заголовок H2</span>
          <input value={equipSection.heading} onChange={(e) => setEquipSection({ ...equipSection, heading: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Заголовок H2 (курсив, вторая строка)</span>
          <input value={equipSection.headingEm} onChange={(e) => setEquipSection({ ...equipSection, headingEm: e.target.value })} />
        </label>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={equipSectionSave.save} disabled={equipSectionSave.status === "saving"}>
            {equipSectionSave.status === "saving" ? "Сохранение…" : "Сохранить"} <span>✓</span>
          </button>
          <SaveStatus status={equipSectionSave.status} />
        </div>
      </section>

      {/* ═══ EQUIPMENT CARDS ═══ */}
      <section className="admin-content-section">
        <h2>Карточки оборудования на главной</h2>
        <p className="admin-hint">Slug должен совпадать с адресом страницы /equipment/[slug]</p>
        {equipCards.map((card, i) => (
          <div key={i} className="admin-object-item">
            <div className="admin-form-row">
              <label className="admin-label">
                <span>Номер</span>
                <input value={card.number} onChange={(e) => setEquipCards(equipCards.map((c, j) => j === i ? { ...c, number: e.target.value } : c))} />
              </label>
              <label className="admin-label">
                <span>Slug (URL)</span>
                <input value={card.slug} onChange={(e) => setEquipCards(equipCards.map((c, j) => j === i ? { ...c, slug: e.target.value } : c))} />
              </label>
            </div>
            <label className="admin-label">
              <span>Название</span>
              <input value={card.title} onChange={(e) => setEquipCards(equipCards.map((c, j) => j === i ? { ...c, title: e.target.value } : c))} />
            </label>
            <label className="admin-label">
              <span>Краткое описание на карточке</span>
              <textarea rows={2} value={card.text} onChange={(e) => setEquipCards(equipCards.map((c, j) => j === i ? { ...c, text: e.target.value } : c))} />
            </label>
            <label className="admin-label">
              <span>Теги (по одному на строку)</span>
              <textarea rows={2} value={card.tags.join("\n")} onChange={(e) => setEquipCards(equipCards.map((c, j) => j === i ? { ...c, tags: e.target.value.split("\n").map(s => s.trimEnd()).filter(Boolean) } : c))} />
            </label>
            <button type="button" className="admin-action-btn danger" onClick={() => setEquipCards(equipCards.filter((_, j) => j !== i))}>
              Удалить карточку
            </button>
          </div>
        ))}
        <button
          type="button"
          className="admin-action-btn"
          onClick={() => setEquipCards([...equipCards, { number: String(equipCards.length + 1).padStart(2, "0"), slug: "", title: "", text: "", tags: [] }])}
        >
          + Добавить карточку
        </button>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={equipCardsSave.save} disabled={equipCardsSave.status === "saving"}>
            {equipCardsSave.status === "saving" ? "Сохранение…" : "Сохранить карточки"} <span>✓</span>
          </button>
          <SaveStatus status={equipCardsSave.status} />
        </div>
      </section>

      {/* ═══ OBJECT SECTION ═══ */}
      <section className="admin-content-section">
        <h2>Секция «Для любых объектов»</h2>
        <ImageUploadField label="Фото секции объектов" imgKey="img_object" hasImage={!!initialContent.img_object} />
        <div className="admin-form-row" style={{ marginTop: 16 }}>
          <label className="admin-label">
            <span>Надпись на фото — город</span>
            <input value={objSection.location} onChange={(e) => setObjSection({ ...objSection, location: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Надпись на фото — производство</span>
            <input value={objSection.productionLabel} onChange={(e) => setObjSection({ ...objSection, productionLabel: e.target.value })} />
          </label>
        </div>
        <label className="admin-label">
          <span>Кикер</span>
          <input value={objSection.kicker} onChange={(e) => setObjSection({ ...objSection, kicker: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Заголовок H2</span>
          <input value={objSection.heading} onChange={(e) => setObjSection({ ...objSection, heading: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Подзаголовок</span>
          <textarea rows={3} value={objSection.lead} onChange={(e) => setObjSection({ ...objSection, lead: e.target.value })} />
        </label>
        <StringListEditor label="Типы объектов (по одному на строку)" value={objSection.items} onChange={(v) => setObjSection({ ...objSection, items: v })} />
        <label className="admin-label">
          <span>Текст кнопки</span>
          <input value={objSection.cta} onChange={(e) => setObjSection({ ...objSection, cta: e.target.value })} />
        </label>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={objSave.save} disabled={objSave.status === "saving"}>
            {objSave.status === "saving" ? "Сохранение…" : "Сохранить"} <span>✓</span>
          </button>
          <SaveStatus status={objSave.status} />
        </div>
      </section>

      {/* ═══ PROCESS ═══ */}
      <section className="admin-content-section">
        <h2>Этапы работы</h2>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Заголовок</span>
            <input value={process.headingMain} onChange={(e) => setProcess({ ...process, headingMain: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Заголовок (курсив)</span>
            <input value={process.headingEm} onChange={(e) => setProcess({ ...process, headingEm: e.target.value })} />
          </label>
        </div>
        <ObjectListEditor
          label="Шаги"
          value={process.steps as Record<string, string>[]}
          onChange={(v) => setProcess({ ...process, steps: v as ProcessContent["steps"] })}
          fields={[
            { key: "number", label: "Номер" },
            { key: "title", label: "Название" },
            { key: "text", label: "Описание", multiline: true },
          ]}
          newItem={{ number: "0" + (process.steps.length + 1), title: "", text: "" }}
        />
        <div className="admin-section-footer">
          <button className="primary-button" onClick={processSave.save} disabled={processSave.status === "saving"}>
            {processSave.status === "saving" ? "Сохранение…" : "Сохранить этапы"} <span>✓</span>
          </button>
          <SaveStatus status={processSave.status} />
        </div>
      </section>

      {/* ═══ GUARANTEES ═══ */}
      <section className="admin-content-section">
        <h2>Гарантии</h2>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Заголовок</span>
            <input value={guarantees.heading} onChange={(e) => setGuarantees({ ...guarantees, heading: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Заголовок (курсив)</span>
            <input value={guarantees.headingEm} onChange={(e) => setGuarantees({ ...guarantees, headingEm: e.target.value })} />
          </label>
        </div>
        <ObjectListEditor
          label="Пункты гарантий"
          value={guarantees.items as Record<string, string>[]}
          onChange={(v) => setGuarantees({ ...guarantees, items: v as GuaranteesContent["items"] })}
          fields={[
            { key: "number", label: "Номер" },
            { key: "title", label: "Заголовок" },
            { key: "text", label: "Описание", multiline: true },
          ]}
          newItem={{ number: "", title: "", text: "" }}
        />
        <div className="admin-section-footer">
          <button className="primary-button" onClick={guaranteesSave.save} disabled={guaranteesSave.status === "saving"}>
            {guaranteesSave.status === "saving" ? "Сохранение…" : "Сохранить гарантии"} <span>✓</span>
          </button>
          <SaveStatus status={guaranteesSave.status} />
        </div>
      </section>

      {/* ═══ CALCULATOR ═══ */}
      <section className="admin-content-section">
        <h2>Калькулятор (форма расчёта)</h2>
        <label className="admin-label">
          <span>Метка секции</span>
          <input value={calc.sectionLabel} onChange={(e) => setCalc({ ...calc, sectionLabel: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Заголовок</span>
          <input value={calc.heading} onChange={(e) => setCalc({ ...calc, heading: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Вводный текст</span>
          <textarea rows={3} value={calc.intro} onChange={(e) => setCalc({ ...calc, intro: e.target.value })} />
        </label>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Заметка инженера (жирный текст)</span>
            <input value={calc.engineerNote} onChange={(e) => setCalc({ ...calc, engineerNote: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Заметка инженера (дополнение)</span>
            <input value={calc.engineerNoteExtra} onChange={(e) => setCalc({ ...calc, engineerNoteExtra: e.target.value })} />
          </label>
        </div>
        <StringListEditor label="Типы оборудования" value={calc.liftTypes} onChange={(v) => setCalc({ ...calc, liftTypes: v })} />
        <StringListEditor label="Грузоподъёмность" value={calc.capacities} onChange={(v) => setCalc({ ...calc, capacities: v })} />
        <StringListEditor label="Высота подъёма" value={calc.heights} onChange={(v) => setCalc({ ...calc, heights: v })} />
        <StringListEditor label="Габариты платформы" value={calc.platforms} onChange={(v) => setCalc({ ...calc, platforms: v })} />
        <StringListEditor label="Количество остановок" value={calc.stops} onChange={(v) => setCalc({ ...calc, stops: v })} />
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Текст кнопки</span>
            <input value={calc.buttonText} onChange={(e) => setCalc({ ...calc, buttonText: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Текст кнопки после копирования</span>
            <input value={calc.copiedText} onChange={(e) => setCalc({ ...calc, copiedText: e.target.value })} />
          </label>
        </div>
        <label className="admin-label">
          <span>Текст под кнопкой (конфиденциальность)</span>
          <textarea rows={2} value={calc.privacyText} onChange={(e) => setCalc({ ...calc, privacyText: e.target.value })} />
        </label>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={calcSave.save} disabled={calcSave.status === "saving"}>
            {calcSave.status === "saving" ? "Сохранение…" : "Сохранить калькулятор"} <span>✓</span>
          </button>
          <SaveStatus status={calcSave.status} />
        </div>
      </section>

      {/* ═══ FAQ ═══ */}
      <section className="admin-content-section">
        <h2>Часто задаваемые вопросы</h2>
        <label className="admin-label">
          <span>Заголовок секции</span>
          <input value={faq.heading} onChange={(e) => setFaq({ ...faq, heading: e.target.value })} />
        </label>
        <ObjectListEditor
          label="Вопросы и ответы"
          value={faq.items as Record<string, string>[]}
          onChange={(v) => setFaq({ ...faq, items: v as FaqContent["items"] })}
          fields={[
            { key: "q", label: "Вопрос" },
            { key: "a", label: "Ответ", multiline: true },
          ]}
          newItem={{ q: "", a: "" }}
        />
        <div className="admin-section-footer">
          <button className="primary-button" onClick={faqSave.save} disabled={faqSave.status === "saving"}>
            {faqSave.status === "saving" ? "Сохранение…" : "Сохранить FAQ"} <span>✓</span>
          </button>
          <SaveStatus status={faqSave.status} />
        </div>
      </section>

      {/* ═══ FOOTER ═══ */}
      <section className="admin-content-section">
        <h2>Футер</h2>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Слоган</span>
            <input value={footer.tagline} onChange={(e) => setFooter({ ...footer, tagline: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Слоган (курсив, вторая строка)</span>
            <input value={footer.taglineEm} onChange={(e) => setFooter({ ...footer, taglineEm: e.target.value })} />
          </label>
        </div>
        <label className="admin-label">
          <span>Текст призыва</span>
          <textarea rows={2} value={footer.actionText} onChange={(e) => setFooter({ ...footer, actionText: e.target.value })} />
        </label>
        <label className="admin-label">
          <span>Текст кнопки</span>
          <input value={footer.cta} onChange={(e) => setFooter({ ...footer, cta: e.target.value })} />
        </label>
        <div className="admin-form-row">
          <label className="admin-label">
            <span>Копирайт</span>
            <input value={footer.copyright} onChange={(e) => setFooter({ ...footer, copyright: e.target.value })} />
          </label>
          <label className="admin-label">
            <span>Подзаголовок бренда в футере</span>
            <input value={footer.brandSubtitle} onChange={(e) => setFooter({ ...footer, brandSubtitle: e.target.value })} />
          </label>
        </div>
        <div className="admin-section-footer">
          <button className="primary-button" onClick={footerSave.save} disabled={footerSave.status === "saving"}>
            {footerSave.status === "saving" ? "Сохранение…" : "Сохранить футер"} <span>✓</span>
          </button>
          <SaveStatus status={footerSave.status} />
        </div>
      </section>

    </div>
  );
}
