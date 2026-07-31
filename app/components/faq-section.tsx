"use client";

import { useState } from "react";

type FaqItem = { q: string; a: string };

export function FaqSection({ heading, items }: { heading: string; items: FaqItem[] }) {
  const [activeFaq, setActiveFaq] = useState<number | null>(0);

  return (
    <section className="faq-section">
      <div className="faq-heading">
        <span className="section-index">/ 06</span>
        <p>ЧАСТЫЕ ВОПРОСЫ</p>
        <h2>{heading}</h2>
      </div>
      <div className="faq-list">
        {items.map((item, index) => {
          const isOpen = activeFaq === index;
          return (
            <article className={isOpen ? "open" : ""} key={item.q}>
              <button
                type="button"
                onClick={() => setActiveFaq(isOpen ? null : index)}
                aria-expanded={isOpen}
              >
                <span>{String(index + 1).padStart(2, "0")}</span>
                <b>{item.q}</b>
                <i aria-hidden="true">{isOpen ? "−" : "+"}</i>
              </button>
              <div className="faq-answer">
                <p>{item.a}</p>
              </div>
            </article>
          );
        })}
      </div>
    </section>
  );
}
