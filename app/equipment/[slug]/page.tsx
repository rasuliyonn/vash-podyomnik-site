import { eq } from "drizzle-orm";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { Gallery } from "./gallery-client";
import { SITE_URL, CONTACT_EMAIL } from "../../../lib/site-config";

// ─── static fallback data ────────────────────────────────────────────────────

type StaticEquipment = {
  title: string;
  description: string;
  features: string[];
  advantages: string[];
  applications: string[];
  techSpecs: { name: string; value: string }[];
};

const STATIC: Record<string, StaticEquipment> = {
  shaftnye: {
    title: "Шахтные подъёмники",
    description:
      "Шахтные грузовые подъёмники — надёжное решение для складов, производственных предприятий и торговых объектов. Устанавливаются как внутри здания, так и снаружи. Кабина движется в закрытой металлической шахте, что обеспечивает максимальную безопасность груза и обслуживающего персонала.",
    features: [
      "Внутреннее и наружное исполнение",
      "Закрытая металлическая шахта",
      "Автоматические двери кабины и шахты",
      "Электронная система управления",
      "Любое количество остановок",
    ],
    advantages: [
      "Высокая степень защиты груза",
      "Тихая работа без вибрации",
      "Длительный ресурс узлов",
      "Соответствие ТР ТС 011/2011",
      "Проект под конкретный объект",
    ],
    applications: [
      "Склады и логистические центры",
      "Производственные предприятия",
      "Торговые центры и магазины",
      "Автосервисы",
      "Архивы и библиотеки",
    ],
    techSpecs: [
      { name: "Грузоподъёмность", value: "100 – 5 000 кг" },
      { name: "Высота подъёма", value: "до 50 м" },
      { name: "Скорость", value: "0,1 – 0,5 м/с" },
      { name: "Количество остановок", value: "2 и более" },
      { name: "Привод", value: "Электрический, канатный / цепной" },
      { name: "Управление", value: "Кнопочное / частотное" },
    ],
  },
  konsolnye: {
    title: "Консольные подъёмники",
    description:
      "Консольные подъёмники не требуют капитальной шахты и монтируются непосредственно у проёма перекрытия. Идеальны для действующих зданий, где невозможно возвести шахту, — решение устанавливается с минимальным вмешательством в конструкции.",
    features: [
      "Монтаж без капитальной шахты",
      "Работа через проём в перекрытии",
      "Компактные габариты",
      "Встраивание в существующее здание",
      "Раздвижные или распашные ограждения",
    ],
    advantages: [
      "Минимальное вмешательство в здание",
      "Быстрый монтаж",
      "Низкая стоимость",
      "Подходит для действующих объектов",
      "Не требует отдельного машинного отделения",
    ],
    applications: [
      "Рестораны и кафе",
      "Магазины на нескольких уровнях",
      "Склады с антресолями",
      "Офисные здания",
      "Частные дома",
    ],
    techSpecs: [
      { name: "Грузоподъёмность", value: "100 – 2 000 кг" },
      { name: "Высота подъёма", value: "до 15 м" },
      { name: "Скорость", value: "0,1 – 0,3 м/с" },
      { name: "Количество остановок", value: "2 и более" },
      { name: "Привод", value: "Электрический, гидравлический" },
      { name: "Управление", value: "Кнопочное" },
    ],
  },
  servisnye: {
    title: "Сервисные подъёмники",
    description:
      "Сервисные (малые грузовые) подъёмники предназначены для перемещения небольших грузов: блюда в ресторанах, товары на складе, документы в офисе. Компактные размеры позволяют встроить оборудование в стандартный проём.",
    features: [
      "Малые габариты платформы",
      "Тихая работа",
      "Быстрый цикл подъёма",
      "Нержавеющая или окрашенная кабина",
      "Встраивание в стену или стойку",
    ],
    advantages: [
      "Экономия времени персонала",
      "Снижение нагрузки на сотрудников",
      "Эстетичный вид",
      "Простое обслуживание",
      "Длительный ресурс",
    ],
    applications: [
      "Рестораны, кафе, бары",
      "Гостиницы",
      "Медицинские учреждения",
      "Архивы и библиотеки",
      "Торговые объекты",
    ],
    techSpecs: [
      { name: "Грузоподъёмность", value: "20 – 300 кг" },
      { name: "Высота подъёма", value: "до 20 м" },
      { name: "Скорость", value: "до 0,5 м/с" },
      { name: "Количество остановок", value: "2 и более" },
      { name: "Привод", value: "Электрический" },
      { name: "Управление", value: "Кнопочное" },
    ],
  },
  kottedzhnye: {
    title: "Коттеджные лифты",
    description:
      "Коттеджные лифты разрабатываются под параметры конкретного дома и интерьера. Подъёмник можно вписать в лестничный пролёт, установить в специально выделенном месте или смонтировать снаружи здания. Оформление кабины — под дизайн интерьера.",
    features: [
      "Дизайн под интерьер заказчика",
      "Монтаж в лестничном пролёте",
      "Тихая работа",
      "Автоматические двери",
      "Управление с каждого этажа",
    ],
    advantages: [
      "Комфорт для всей семьи",
      "Повышение ценности объекта",
      "Индивидуальное оформление",
      "Безопасность сертифицированного оборудования",
      "Простое обслуживание",
    ],
    applications: [
      "Частные дома и коттеджи",
      "Дачи",
      "Таунхаусы",
      "Малоэтажные здания",
    ],
    techSpecs: [
      { name: "Грузоподъёмность", value: "100 – 500 кг" },
      { name: "Высота подъёма", value: "до 20 м" },
      { name: "Скорость", value: "до 0,15 м/с" },
      { name: "Количество остановок", value: "2 и более" },
      { name: "Привод", value: "Электрический, гидравлический" },
      { name: "Управление", value: "Кнопочное / сенсорное" },
    ],
  },
  platformy: {
    title: "Грузовые платформы",
    description:
      "Грузовые подъёмные платформы проектируются под нестандартные задачи: крупногабаритные грузы, специфические условия эксплуатации, нестандартная форма площадки. Открытая конструкция позволяет работать с грузами, которые не помещаются в закрытую кабину.",
    features: [
      "Открытая платформа любых размеров",
      "Проектирование под ТЗ заказчика",
      "Тяжёлые нагрузки",
      "Работа в агрессивной среде",
      "Различные типы ограждений",
    ],
    advantages: [
      "Нет ограничений по габаритам груза",
      "Высокая грузоподъёмность",
      "Простота конструкции",
      "Лёгкость обслуживания",
      "Соответствие ТР ТС 011/2011",
    ],
    applications: [
      "Автосервисы и автомойки",
      "Производственные предприятия",
      "Склады",
      "Строительные объекты",
      "Агропромышленные комплексы",
    ],
    techSpecs: [
      { name: "Грузоподъёмность", value: "500 – 10 000 кг" },
      { name: "Высота подъёма", value: "до 20 м" },
      { name: "Скорость", value: "до 0,3 м/с" },
      { name: "Количество остановок", value: "2 и более" },
      { name: "Привод", value: "Электрический, гидравлический" },
      { name: "Управление", value: "Кнопочное / пультовое" },
    ],
  },
};

// ─── helpers ─────────────────────────────────────────────────────────────────

async function getEquipmentData(slug: string) {
  try {
    const { getDb } = await import("../../../db");
    const { equipmentTypes, equipmentImages, equipmentDocuments } = await import(
      "../../../db/schema"
    );
    const db = getDb();

    const [row] = await db
      .select()
      .from(equipmentTypes)
      .where(eq(equipmentTypes.slug, slug));

    if (!row) return null;

    const images = await db
      .select()
      .from(equipmentImages)
      .where(eq(equipmentImages.equipmentId, row.id));

    const documents = await db
      .select()
      .from(equipmentDocuments)
      .where(eq(equipmentDocuments.equipmentId, row.id));

    return {
      ...row,
      features: JSON.parse(row.featuresJson) as string[],
      advantages: JSON.parse(row.advantagesJson) as string[],
      applications: JSON.parse(row.applicationsJson) as string[],
      techSpecs: JSON.parse(row.techSpecsJson) as { name: string; value: string }[],
      images,
      documents,
    };
  } catch {
    return null;
  }
}

const DOC_ICONS: Record<string, string> = {
  pdf: "📄",
  certificate: "🏆",
  instruction: "📋",
  scheme: "📐",
  video: "🎬",
};

// ─── metadata ────────────────────────────────────────────────────────────────

export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  const staticData = STATIC[slug];
  const title = staticData?.title ?? "Оборудование";
  return {
    title,
    description: staticData?.description?.slice(0, 160),
    alternates: {
      canonical: `/equipment/${slug}`,
    },
  };
}

// ─── page ─────────────────────────────────────────────────────────────────────

export default async function EquipmentPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const staticData = STATIC[slug];
  if (!staticData) notFound();

  const dbData = await getEquipmentData(slug);

  const title = dbData?.title ?? staticData.title;
  const description = dbData?.description || staticData.description;
  const features = dbData?.features?.length ? dbData.features : staticData.features;
  const advantages = dbData?.advantages?.length ? dbData.advantages : staticData.advantages;
  const applications = dbData?.applications?.length ? dbData.applications : staticData.applications;
  const techSpecs = dbData?.techSpecs?.length ? dbData.techSpecs : staticData.techSpecs;
  const images = dbData?.images ?? [];
  const documents = dbData?.documents ?? [];
  const videoUrl = dbData?.videoUrl ?? null;

  return (
    <main className="eq-page">
      {/* ── header ── */}
      <header className="site-header">
        <a className="brand" href="/" aria-label="На главную">
          <img
            className="brand-logo"
            src="/vash-podyomnik-logo.jpg"
            alt="Логотип Ваш Подъёмник"
          />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Ростов-на-Дону · вся Россия</small>
          </span>
        </a>
        <nav aria-label="Основная навигация">
          <a href="/#equipment">Оборудование</a>
          <a href="/#process">Этапы</a>
          <a href="/#guarantees">Гарантии</a>
        </nav>
        <a className="header-cta" href="/#calculation">
          Получить расчёт
          <span aria-hidden="true">↗</span>
        </a>
      </header>

      {/* ── breadcrumb ── */}
      <nav className="breadcrumb" aria-label="Хлебные крошки">
        <a href="/">Главная</a>
        <span aria-hidden="true">›</span>
        <a href="/#equipment">Оборудование</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">{title}</span>
      </nav>

      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{
          __html: JSON.stringify({
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            itemListElement: [
              {
                "@type": "ListItem",
                position: 1,
                name: "Главная",
                item: SITE_URL,
              },
              {
                "@type": "ListItem",
                position: 2,
                name: "Оборудование",
                item: `${SITE_URL}/#equipment`,
              },
              {
                "@type": "ListItem",
                position: 3,
                name: title,
                item: `${SITE_URL}/equipment/${slug}`,
              },
            ],
          }),
        }}
      />

      {/* ── hero ── */}
      <section className="eq-hero">
        <div className="eq-hero-copy">
          <span className="eyebrow">
            <span />
            Производство · монтаж · гарантия
          </span>
          <h1>{title}</h1>
          <p>{description}</p>
          <a className="primary-button" href="/#calculation">
            Рассчитать стоимость
            <span aria-hidden="true">→</span>
          </a>
        </div>
      </section>

      {/* ── gallery ── */}
      {images.length > 0 && (
        <section className="eq-section">
          <div className="eq-section-label">
            <span>/ ГАЛЕРЕЯ</span>
          </div>
          <Gallery images={images} />
        </section>
      )}

      {/* ── features + advantages ── */}
      <section className="eq-section eq-two-col">
        <div>
          <div className="eq-section-label">
            <span>/ ОСОБЕННОСТИ</span>
          </div>
          <ul className="eq-list">
            {features.map((f, i) => (
              <li key={i}>
                <span>{String(i + 1).padStart(2, "0")}</span>
                {f}
              </li>
            ))}
          </ul>
        </div>
        <div>
          <div className="eq-section-label">
            <span>/ ПРЕИМУЩЕСТВА</span>
          </div>
          <ul className="eq-list advantages">
            {advantages.map((a, i) => (
              <li key={i}>
                <span>◆</span>
                {a}
              </li>
            ))}
          </ul>
        </div>
      </section>

      {/* ── tech specs ── */}
      <section className="eq-section">
        <div className="eq-section-label">
          <span>/ ТЕХНИЧЕСКИЕ ХАРАКТЕРИСТИКИ</span>
        </div>
        <table className="eq-specs-table">
          <tbody>
            {techSpecs.map((spec, i) => (
              <tr key={i}>
                <td>{spec.name}</td>
                <td>{spec.value}</td>
              </tr>
            ))}
          </tbody>
        </table>
        <p className="eq-specs-note">
          Точные параметры определяются при проектировании под ваш объект.
        </p>
      </section>

      {/* ── applications ── */}
      <section className="eq-section">
        <div className="eq-section-label">
          <span>/ СФЕРЫ ПРИМЕНЕНИЯ</span>
        </div>
        <div className="eq-applications">
          {applications.map((app, i) => (
            <div key={i} className="eq-app-card">
              <span>{String(i + 1).padStart(2, "0")}</span>
              <p>{app}</p>
            </div>
          ))}
        </div>
      </section>

      {/* ── video ── */}
      {videoUrl && (
        <section className="eq-section">
          <div className="eq-section-label">
            <span>/ ВИДЕО</span>
          </div>
          <div className="eq-video-wrap">
            <iframe
              src={videoUrl}
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowFullScreen
              title={`Видео: ${title}`}
            />
          </div>
        </section>
      )}

      {/* ── documents ── */}
      {documents.length > 0 && (
        <section className="eq-section">
          <div className="eq-section-label">
            <span>/ ДОКУМЕНТЫ</span>
          </div>
          <div className="eq-documents">
            {documents.map((doc) => (
              <a
                key={doc.id}
                href={doc.url}
                className="eq-doc-card"
                target="_blank"
                rel="noopener noreferrer"
                download
              >
                <span className="eq-doc-icon">
                  {DOC_ICONS[doc.docType] ?? "📄"}
                </span>
                <span className="eq-doc-name">{doc.name}</span>
                {doc.sizeKb && (
                  <span className="eq-doc-size">
                    {doc.sizeKb > 1024
                      ? `${(doc.sizeKb / 1024).toFixed(1)} МБ`
                      : `${doc.sizeKb} КБ`}
                  </span>
                )}
                <span className="eq-doc-dl">↓</span>
              </a>
            ))}
          </div>
        </section>
      )}

      {/* ── CTA ── */}
      <section className="eq-cta">
        <div>
          <h2>
            Нужен расчёт для вашего объекта?
          </h2>
          <p>
            Инженер бесплатно изучит задачу и подберёт оптимальные параметры.
          </p>
        </div>
        <a className="primary-button" href="/#calculation">
          Рассчитать стоимость
          <span aria-hidden="true">↗</span>
        </a>
      </section>

      {/* ── back link ── */}
      <div className="eq-back">
        <a href="/#equipment">← Все типы оборудования</a>
      </div>

      <footer>
        <div className="footer-main">
          <div>
            <div className="brand footer-brand">
              <img
                className="brand-logo"
                src="/vash-podyomnik-logo.jpg"
                alt="Логотип Ваш Подъёмник"
              />
              <span>
                <b>ВАШ ПОДЪЁМНИК</b>
                <small>Проектирование · производство · монтаж</small>
              </span>
            </div>
            <h2>
              Поднимем вашу задачу
              <br />
              <em>на нужный уровень</em>
            </h2>
          </div>
          <div className="footer-action">
            <p>
              Получите консультацию инженера и предварительный расчёт
              оборудования под ваш объект.
            </p>
            <a className="primary-button" href="/#calculation">
              Рассчитать проект
              <span aria-hidden="true">↗</span>
            </a>
          </div>
        </div>
        <div className="footer-bottom">
          <span>© 2026 · Ростов-на-Дону · Работаем по России</span>
          <div className="footer-links">
            <a href="/privacy">Политика конфиденциальности</a>
            <a href="/terms">Пользовательское соглашение</a>
            <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>
            <a href="/">НА ГЛАВНУЮ ↑</a>
          </div>
        </div>
      </footer>
    </main>
  );
}
