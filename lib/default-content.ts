export type HeroContent = {
  eyebrow: string;
  h1: string;
  h1em: string;
  lead: string;
  ctaPrimary: string;
  ctaSecondary: string;
  metrics: Array<{ value: string; label: string }>;
  visualTop: string;
  visualBottom: string;
  capacityLabel: string;
  capacityValue: string;
};

export type HeaderContent = {
  brandName: string;
  brandSubtitle: string;
  ctaText: string;
};

export type EquipmentSectionContent = {
  label: string;
  heading: string;
  headingEm: string;
};

export type EquipmentCard = {
  number: string;
  slug: string;
  title: string;
  text: string;
  tags: string[];
};

export type ObjectSectionContent = {
  location: string;
  productionLabel: string;
  kicker: string;
  heading: string;
  lead: string;
  items: string[];
  cta: string;
};

export type ProcessContent = {
  headingMain: string;
  headingEm: string;
  steps: Array<{ number: string; title: string; text: string }>;
};

export type GuaranteesContent = {
  heading: string;
  headingEm: string;
  items: Array<{ number: string; title: string; text: string }>;
};

export type CalculatorContent = {
  sectionLabel: string;
  heading: string;
  intro: string;
  engineerNote: string;
  engineerNoteExtra: string;
  liftTypes: string[];
  capacities: string[];
  heights: string[];
  platforms: string[];
  stops: string[];
  buttonText: string;
  copiedText: string;
  privacyText: string;
};

export type FaqContent = {
  heading: string;
  items: Array<{ q: string; a: string }>;
};

export type FooterContent = {
  tagline: string;
  taglineEm: string;
  actionText: string;
  cta: string;
  copyright: string;
  brandSubtitle: string;
};

export type SiteContent = {
  header: HeaderContent;
  hero: HeroContent;
  trust_strip: string[];
  equipment_section: EquipmentSectionContent;
  equipment_cards: EquipmentCard[];
  object_section: ObjectSectionContent;
  process: ProcessContent;
  guarantees: GuaranteesContent;
  calculator: CalculatorContent;
  faq: FaqContent;
  footer: FooterContent;
  // Image flags — actual data served via /api/content-image?key=img_*
  img_logo?: boolean;
  img_hero?: boolean;
  img_object?: boolean;
};

export const defaultContent: SiteContent = {
  header: {
    brandName: "ВАШ ПОДЪЁМНИК",
    brandSubtitle: "Ростов-на-Дону · вся Россия",
    ctaText: "Получить расчёт",
  },
  hero: {
    eyebrow: "Собственное производство",
    h1: "Лифты и грузовые",
    h1em: "под ваш объект",
    lead: "Проектируем, производим и устанавливаем подъёмное оборудование любых размеров и характеристик под особенности вашего объекта.",
    ctaPrimary: "Рассчитать стоимость",
    ctaSecondary: "Смотреть решения",
    metrics: [
      { value: "от 5", label: "рабочих дней\nна производство" },
      { value: "по РФ", label: "доставка\nи монтаж" },
      { value: "ТР ТС", label: "сертифицированное\nоборудование" },
    ],
    visualTop: "ИНЖЕНЕРНОЕ\nРЕШЕНИЕ",
    visualBottom: "ГРУЗОПОДЪЁМНОСТЬ\nПОД ВАШУ ЗАДАЧУ",
    capacityLabel: "ПАРАМЕТРЫ",
    capacityValue: "ИНДИВИДУАЛЬНО",
  },
  trust_strip: [
    "СОБСТВЕННОЕ ПРОИЗВОДСТВО",
    "ФИКСИРОВАННАЯ ЦЕНА",
    "ПРОЕКТ ПОД ОБЪЕКТ",
    "ГАРАНТИЯ И МОНТАЖ",
  ],
  equipment_section: {
    label: "НАШИ РЕШЕНИЯ",
    heading: "Не продаём «типовую коробку».",
    headingEm: "Подбираем решение под задачу.",
  },
  equipment_cards: [
    {
      number: "01",
      slug: "shaftnye",
      title: "Шахтные подъёмники",
      text: "Для складов, производств и торговых объектов. Внутреннее или наружное исполнение.",
      tags: ["по проекту", "любые габариты"],
    },
    {
      number: "02",
      slug: "konsolnye",
      title: "Консольные подъёмники",
      text: "Компактное решение для подъёма груза между этажами без капитальной шахты.",
      tags: ["под ваш объект", "новые и действующие здания"],
    },
    {
      number: "03",
      slug: "servisnye",
      title: "Сервисные подъёмники",
      text: "Малые грузовые лифты для ресторанов, кафе, гостиниц и магазинов.",
      tags: ["компактные", "малые грузовые"],
    },
    {
      number: "04",
      slug: "kottedzhnye",
      title: "Коттеджные лифты",
      text: "Индивидуальное решение для частного дома с учётом интерьера и параметров здания.",
      tags: ["для дома", "под параметры здания"],
    },
    {
      number: "05",
      slug: "platformy",
      title: "Грузовые платформы",
      text: "Проектируем под нестандартные задачи, тяжёлые грузы и особенности вашего объекта.",
      tags: ["по ТЗ", "для бизнеса"],
    },
  ],
  object_section: {
    location: "РОСТОВ-НА-ДОНУ",
    productionLabel: "ПРОИЗВОДСТВО →",
    kicker: "ДЛЯ ЛЮБЫХ ОБЪЕКТОВ",
    heading: "Впишем подъёмник в новое или действующее здание",
    lead: "Изучим планировку, ограничения и рабочие процессы. Подберём размеры кабины, высоту подъёма, грузоподъёмность и тип исполнения.",
    items: [
      "Склады и логистические центры",
      "Производственные предприятия",
      "Магазины и торговые центры",
      "Автосервисы и техцентры",
      "Рестораны и кафе",
      "Гостиницы",
      "Частные дома и коттеджи",
      "Другие объекты с вертикальной транспортировкой грузов",
    ],
    cta: "Обсудить ваш объект",
  },
  process: {
    headingMain: "От задачи до запуска —",
    headingEm: "одна команда",
    steps: [
      { number: "01", title: "Заявка", text: "Уточняем задачу, объект, груз и желаемые сроки." },
      { number: "02", title: "Инженерный расчёт", text: "Подбираем конструкцию и фиксируем стоимость." },
      { number: "03", title: "Проектирование", text: "Готовим техническое решение под ваш объект." },
      { number: "04", title: "Производство", text: "Изготавливаем оборудование в Ростове-на-Дону." },
      { number: "05", title: "Доставка и монтаж", text: "Доставляем по России, монтируем и запускаем." },
    ],
  },
  guarantees: {
    heading: "Производитель отвечает",
    headingEm: "за весь результат",
    items: [
      { number: "01", title: "Сертификат ТР ТС", text: "Оборудование соответствует требованиям технического регламента." },
      { number: "02", title: "Цена по договору", text: "Фиксируем согласованную стоимость до начала производства." },
      { number: "03", title: "Проект под объект", text: "Не заставляем объект подстраиваться под типовое решение." },
      { number: "04", title: "Профессиональный монтаж", text: "Организуем доставку, монтаж и ввод оборудования в работу." },
      { number: "05", title: "Гарантия", text: "Отвечаем за оборудование и качество выполненных работ." },
      { number: "06", title: "Инженер на связи", text: "Помогаем на каждом этапе — от первой идеи до запуска." },
    ],
  },
  calculator: {
    sectionLabel: "БЕСПЛАТНЫЙ ПРЕДВАРИТЕЛЬНЫЙ РАСЧЁТ",
    heading: "Расскажите о задаче — инженер предложит решение",
    intro: "Выберите базовые параметры. Мы подготовим структуру заявки, чтобы быстрее рассчитать подходящую конструкцию.",
    engineerNote: "Инженер бесплатно изучит задачу",
    engineerNoteExtra: "При необходимости организуем выезд на замер.",
    liftTypes: [
      "Грузовой подъёмник",
      "Шахтный подъёмник",
      "Консольный подъёмник",
      "Сервисный подъёмник",
      "Коттеджный лифт",
      "Грузовая платформа",
    ],
    capacities: ["до 100 кг", "до 500 кг", "до 1 000 кг", "до 3 000 кг", "более 3 000 кг"],
    heights: ["до 3 м", "3–6 м", "6–10 м", "10–20 м", "более 20 м"],
    platforms: [
      "до 1 000×800 мм",
      "до 1 500×1 000 мм",
      "до 2 000×1 500 мм",
      "до 3 000×2 000 мм",
      "нестандартные",
    ],
    stops: ["2 остановки", "3 остановки", "4 остановки", "5 и более"],
    buttonText: "Подготовить заявку",
    copiedText: "Заявка скопирована",
    privacyText: "Нажимая кнопку, вы копируете готовый текст заявки — его можно отправить менеджеру в чат или мессенджер.",
  },
  faq: {
    heading: "Коротко о главном",
    items: [
      {
        q: "Можно установить подъёмник в действующем здании?",
        a: "Да. Инженер оценит планировку, доступные проёмы, высоту подъёма и предложит внутреннее либо наружное исполнение с минимальным вмешательством в объект.",
      },
      {
        q: "Сколько стоит грузовой подъёмник?",
        a: "Стоимость зависит от грузоподъёмности, размеров платформы или кабины, высоты подъёма, количества остановок и комплектации. После вводных данных подготовим точный расчёт и зафиксируем цену в договоре.",
      },
      {
        q: "Какие сроки изготовления?",
        a: "От 5 до 60 рабочих дней. Точный срок зависит от сложности проекта, размеров и комплектации оборудования.",
      },
      {
        q: "Работаете только по Ростовской области?",
        a: "Нет. Производство находится в Ростове-на-Дону, доставку и монтаж организуем по всей России.",
      },
    ],
  },
  footer: {
    tagline: "Поднимем вашу задачу",
    taglineEm: "на нужный уровень",
    actionText: "Получите консультацию инженера и предварительный расчёт оборудования под ваш объект.",
    cta: "Рассчитать проект",
    copyright: "© 2026 · Ростов-на-Дону · Работаем по России",
    brandSubtitle: "Проектирование · производство · монтаж",
  },
};
