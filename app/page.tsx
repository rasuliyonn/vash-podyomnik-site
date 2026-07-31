import { getSiteContent } from "../lib/site-content";
import { Calculator } from "./components/calculator";
import { FaqSection } from "./components/faq-section";

export default async function Home() {
  const c = await getSiteContent();

  return (
    <main>
      <header className="site-header">
        <a className="brand" href="#top" aria-label="На главную">
          <img
            className="brand-logo"
            src={c.img_logo ? "/api/content-image?key=img_logo" : "/vash-podyomnik-logo.jpg"}
            alt="Логотип Ваш Подъёмник"
          />
          <span>
            <b>{c.header.brandName}</b>
            <small>{c.header.brandSubtitle}</small>
          </span>
        </a>
        <nav aria-label="Основная навигация">
          <a href="#equipment">Оборудование</a>
          <a href="#process">Этапы</a>
          <a href="#guarantees">Гарантии</a>
        </nav>
        <a className="header-cta" href="#calculation">
          {c.header.ctaText}
          <span aria-hidden="true">↗</span>
        </a>
      </header>

      <section
        className="hero"
        id="top"
        style={c.img_hero ? {
          backgroundImage: `linear-gradient(90deg,rgba(5,7,7,.99) 0%,rgba(5,7,7,.96) 31%,rgba(5,7,7,.63) 53%,rgba(5,7,7,.08) 78%),linear-gradient(0deg,rgba(0,0,0,.34),rgba(0,0,0,.08)),url("/api/content-image?key=img_hero")`,
        } : undefined}
      >
        <div className="hero-noise" aria-hidden="true" />
        <div className="hero-copy">
          <div className="eyebrow">
            <span />
            {c.hero.eyebrow}
          </div>
          <h1>
            {c.hero.h1}
            <br />
            подъёмники <em>{c.hero.h1em}</em>
          </h1>
          <p className="hero-lead">{c.hero.lead}</p>
          <div className="hero-actions">
            <a className="primary-button" href="#calculation">
              {c.hero.ctaPrimary}
              <span aria-hidden="true">→</span>
            </a>
            <a className="text-link" href="#equipment">
              {c.hero.ctaSecondary}
              <span aria-hidden="true">↓</span>
            </a>
          </div>
          <div className="hero-metrics" aria-label="Ключевые преимущества">
            {c.hero.metrics.map((m) => (
              <div key={m.value}>
                <strong>{m.value}</strong>
                <span style={{ whiteSpace: "pre-line" }}>{m.label}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="hero-visual" aria-label="Промышленное подъёмное оборудование">
          <div className="visual-label top">
            <span>01</span>
            <p style={{ whiteSpace: "pre-line" }}>{c.hero.visualTop}</p>
          </div>
          <div className="visual-label bottom">
            <p style={{ whiteSpace: "pre-line" }}>{c.hero.visualBottom}</p>
            <span>↗</span>
          </div>
          <div className="capacity-card">
            <small>{c.hero.capacityLabel}</small>
            <b>{c.hero.capacityValue}</b>
            <i />
          </div>
        </div>
      </section>

      <section className="trust-strip" aria-label="Ключевые гарантии">
        {c.trust_strip.flatMap((item, i) =>
          i === 0
            ? [<p key={item}>{item}</p>]
            : [<span key={`sep-${i}`}>◆</span>, <p key={item}>{item}</p>]
        )}
      </section>

      <section className="section equipment" id="equipment">
        <div className="section-heading">
          <div>
            <span className="section-index">/ 01</span>
            <p>{c.equipment_section.label}</p>
          </div>
          <h2>
            {c.equipment_section.heading}
            <br />
            <em>{c.equipment_section.headingEm}</em>
          </h2>
        </div>

        <div className="equipment-grid">
          {c.equipment_cards.map((item) => (
            <a
              className="equipment-card"
              href={`/equipment/${item.slug}`}
              key={item.number}
              aria-label={`Подробнее: ${item.title}`}
            >
              <div className="card-top">
                <span>{item.number}</span>
                <span aria-hidden="true">↗</span>
              </div>
              <div className={`lift-symbol symbol-${item.number}`} aria-hidden="true">
                <i />
                <i />
                <i />
              </div>
              <h3>{item.title}</h3>
              <p>{item.text}</p>
              <div className="tags">
                {item.tags.map((tag) => (
                  <span key={tag}>{tag}</span>
                ))}
              </div>
            </a>
          ))}
        </div>
      </section>

      <section className="object-section">
        <div
          className="object-image"
          role="img"
          aria-label="Современный складской объект"
          style={c.img_object ? {
            backgroundImage: `linear-gradient(180deg,rgba(5,7,7,.08),rgba(5,7,7,.72)),url("/api/content-image?key=img_object")`,
          } : undefined}
        >
          <div className="image-caption">
            <span>{c.object_section.location}</span>
            <span>{c.object_section.productionLabel}</span>
          </div>
        </div>
        <div className="object-copy">
          <span className="section-index">/ 02</span>
          <p className="kicker">{c.object_section.kicker}</p>
          <h2>{c.object_section.heading}</h2>
          <p className="object-lead">{c.object_section.lead}</p>
          <div className="object-list">
            {c.object_section.items.map((item, index) => (
              <div key={item}>
                <span>{String(index + 1).padStart(2, "0")}</span>
                <p>{item}</p>
              </div>
            ))}
          </div>
          <a className="primary-button dark" href="#calculation">
            {c.object_section.cta}
            <span aria-hidden="true">→</span>
          </a>
        </div>
      </section>

      <section className="section process" id="process">
        <div className="section-heading compact">
          <div>
            <span className="section-index">/ 03</span>
            <p>КАК МЫ РАБОТАЕМ</p>
          </div>
          <h2>
            {c.process.headingMain}
            <br />
            <em>{c.process.headingEm}</em>
          </h2>
        </div>

        <div className="timeline">
          {c.process.steps.map((step) => (
            <article key={step.number}>
              <span className="timeline-number">{step.number}</span>
              <div className="timeline-dot" />
              <h3>{step.title}</h3>
              <p>{step.text}</p>
            </article>
          ))}
        </div>
      </section>

      <section className="guarantees" id="guarantees">
        <div className="guarantee-title">
          <span className="section-index">/ 04</span>
          <p>ПОЧЕМУ НАМ ДОВЕРЯЮТ</p>
          <h2>
            {c.guarantees.heading}
            <br />
            <em>{c.guarantees.headingEm}</em>
          </h2>
        </div>
        <div className="guarantee-grid">
          {c.guarantees.items.map((item) => (
            <article key={item.number}>
              <span>{item.number}</span>
              <h3>{item.title}</h3>
              <p>{item.text}</p>
            </article>
          ))}
        </div>
      </section>

      <Calculator content={c.calculator} />

      <FaqSection heading={c.faq.heading} items={c.faq.items} />

      <footer>
        <div className="footer-main">
          <div>
            <div className="brand footer-brand">
              <img
                className="brand-logo"
                src={c.img_logo ? "/api/content-image?key=img_logo" : "/vash-podyomnik-logo.jpg"}
                alt="Логотип Ваш Подъёмник"
              />
              <span>
                <b>{c.header.brandName}</b>
                <small>{c.footer.brandSubtitle}</small>
              </span>
            </div>
            <h2>
              {c.footer.tagline}
              <br />
              <em>{c.footer.taglineEm}</em>
            </h2>
          </div>
          <div className="footer-action">
            <p>{c.footer.actionText}</p>
            <a className="primary-button" href="#calculation">
              {c.footer.cta}
              <span aria-hidden="true">↗</span>
            </a>
          </div>
        </div>
        <div className="footer-bottom">
          <span>{c.footer.copyright}</span>
          <a href="#top">НАВЕРХ ↑</a>
        </div>
      </footer>
    </main>
  );
}
