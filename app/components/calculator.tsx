"use client";

import { FormEvent, useMemo, useState } from "react";
import Link from "next/link";
import type { CalculatorContent } from "../../lib/default-content";

export function Calculator({ content }: { content: CalculatorContent }) {
  const [lift, setLift] = useState(content.liftTypes[0] ?? "Грузовой подъёмник");
  const [capacity, setCapacity] = useState(content.capacities[1] ?? "до 500 кг");
  const [height, setHeight] = useState("");
  const [platformWidth, setPlatformWidth] = useState("");
  const [platformDepth, setPlatformDepth] = useState("");
  const [floors, setFloors] = useState(content.stops[0] ?? "2 остановки");
  const [location, setLocation] = useState("");
  const [copied, setCopied] = useState(false);
  const [consentData, setConsentData] = useState(false);
  const [consentNews, setConsentNews] = useState(false);

  const platformSize = [platformWidth && `${platformWidth} мм`, platformDepth && `${platformDepth} мм`].filter(Boolean).join(" × ");

  const requestText = useMemo(() => {
    const parts = [lift, capacity];
    if (height) parts.push(`высота подъёма ${height} мм`);
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
        platformWidth,
        platformDepth,
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
            <input
              type="number"
              value={height}
              onChange={(e) => setHeight(e.target.value)}
              placeholder="Высота в мм, напр. 3000"
              min={0}
            />
          </label>

          <label>
            <span>04 · ГАБАРИТЫ ПЛАТФОРМЫ</span>
            <div className="platform-inputs">
              <input
                type="number"
                value={platformWidth}
                onChange={(e) => setPlatformWidth(e.target.value)}
                placeholder="Ширина, мм"
                min={0}
              />
              <input
                type="number"
                value={platformDepth}
                onChange={(e) => setPlatformDepth(e.target.value)}
                placeholder="Глубина, мм"
                min={0}
              />
            </div>
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

        <div className="form-consents">
          <label className="consent-label">
            <input
              type="checkbox"
              checked={consentData}
              onChange={(e) => setConsentData(e.target.checked)}
              required
            />
            <span>
              Я согласен(-на) на обработку моих персональных данных в соответствии с{" "}
              <Link href="/privacy" target="_blank">Политикой по обработке и защиты персональных данных</Link>
            </span>
          </label>
          <label className="consent-label">
            <input
              type="checkbox"
              checked={consentNews}
              onChange={(e) => setConsentNews(e.target.checked)}
            />
            <span>
              Я согласен(-на) на получение новостных рассылок, рассылок рекламного, информационного характера
            </span>
          </label>
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
