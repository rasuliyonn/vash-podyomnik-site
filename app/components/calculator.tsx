"use client";

import { FormEvent, useMemo, useState } from "react";
import type { CalculatorContent } from "../../lib/default-content";

export function Calculator({ content }: { content: CalculatorContent }) {
  const [lift, setLift] = useState(content.liftTypes[0] ?? "Грузовой подъёмник");
  const [capacity, setCapacity] = useState(content.capacities[1] ?? "до 500 кг");
  const [height, setHeight] = useState("");
  const [platformSize, setPlatformSize] = useState("");
  const [floors, setFloors] = useState(content.stops[0] ?? "2 остановки");
  const [location, setLocation] = useState("");
  const [copied, setCopied] = useState(false);

  const requestText = useMemo(() => {
    const parts = [lift, capacity];
    if (height) parts.push(`высота ${height}`);
    if (platformSize) parts.push(`платформа ${platformSize}`);
    parts.push(floors);
    if (location) parts.push(`объект: ${location}`);
    return `Заявка на расчёт: ${parts.join(", ")}. Нужна консультация инженера и предварительная стоимость.`;
  }, [lift, capacity, height, platformSize, floors, location]);

  async function handleRequest(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    fetch("/api/calculate", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        liftType: lift,
        capacity,
        height,
        platformSize,
        city: location,
        stops: floors,
      }),
    }).catch(() => {});

    try {
      await navigator.clipboard.writeText(requestText);
    } catch {
      // clipboard blocked
    }
    setCopied(true);
    window.setTimeout(() => setCopied(false), 3500);
  }

  return (
    <section className="calculator" id="calculation">
      <div className="calculator-intro">
        <span className="section-index">/ 05</span>
        <p>{content.sectionLabel}</p>
        <h2>{content.heading}</h2>
        <p>{content.intro}</p>
        <div className="engineer-note">
          <span>И</span>
          <p>
            <b>{content.engineerNote}</b>
            {content.engineerNoteExtra}
          </p>
        </div>
      </div>

      <form className="calculator-form" onSubmit={handleRequest}>
        <label>
          <span>01 · ТИП ОБОРУДОВАНИЯ</span>
          <select value={lift} onChange={(e) => setLift(e.target.value)}>
            {content.liftTypes.map((t) => <option key={t}>{t}</option>)}
          </select>
        </label>

        <label>
          <span>02 · ГРУЗОПОДЪЁМНОСТЬ</span>
          <select value={capacity} onChange={(e) => setCapacity(e.target.value)}>
            {content.capacities.map((c) => <option key={c}>{c}</option>)}
          </select>
        </label>

        <div className="form-row">
          <label>
            <span>03 · ВЫСОТА ПОДЪЁМА</span>
            <select value={height} onChange={(e) => setHeight(e.target.value)}>
              <option value="">Не знаю / уточним</option>
              {content.heights.map((h) => <option key={h}>{h}</option>)}
            </select>
          </label>

          <label>
            <span>04 · ГАБАРИТЫ ПЛАТФОРМЫ</span>
            <select value={platformSize} onChange={(e) => setPlatformSize(e.target.value)}>
              <option value="">Не знаю / уточним</option>
              {content.platforms.map((p) => <option key={p}>{p}</option>)}
            </select>
          </label>
        </div>

        <label>
          <span>05 · ГОРОД ИЛИ ОБЪЕКТ</span>
          <input
            value={location}
            onChange={(e) => setLocation(e.target.value)}
            placeholder="Например: склад, Краснодар"
          />
        </label>

        <label>
          <span>06 · КОЛИЧЕСТВО ОСТАНОВОК</span>
          <select value={floors} onChange={(e) => setFloors(e.target.value)}>
            {content.stops.map((s) => <option key={s}>{s}</option>)}
          </select>
        </label>

        <div className="request-preview">
          <small>ВАША ЗАЯВКА</small>
          <p>{requestText}</p>
        </div>
        <button className="primary-button form-button" type="submit">
          {copied ? content.copiedText : content.buttonText}
          <span aria-hidden="true">{copied ? "✓" : "→"}</span>
        </button>
        <small className="privacy">{content.privacyText}</small>
      </form>
    </section>
  );
}
