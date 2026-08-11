import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { SITE_URL, CONTACT_EMAIL } from "../lib/site-config";
import { Analytics } from "./components/analytics";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

const HOME_TITLE = "Грузовые подъёмники и лифты под ваш объект — Ваш Подъёмник";

const HOME_DESCRIPTION =
  "«Ваш Подъёмник» — производство, проектирование и монтаж грузовых подъёмников и лифтов под ваш объект. Доставка по всей России.";

const ORGANIZATION_JSON_LD = {
  "@context": "https://schema.org",
  "@type": "Organization",
  name: "Ваш Подъёмник",
  url: SITE_URL,
  logo: `${SITE_URL}/vash-podyomnik-logo.jpg`,
  email: CONTACT_EMAIL,
  address: {
    "@type": "PostalAddress",
    streetAddress: "ул. Промышленная, д. 12",
    addressLocality: "Ростов-на-Дону",
    postalCode: "344000",
    addressCountry: "RU",
  },
};

export const metadata: Metadata = {
  metadataBase: new URL(SITE_URL),
  title: {
    default: HOME_TITLE,
    template: "%s — Ваш Подъёмник",
  },
  description: HOME_DESCRIPTION,
  alternates: {
    canonical: "/",
  },
  openGraph: {
    type: "website",
    locale: "ru_RU",
    siteName: "Ваш Подъёмник",
    title: HOME_TITLE,
    description: HOME_DESCRIPTION,
    url: "/",
    images: [
      {
        url: "/vash-podyomnik-logo.jpg",
        alt: "Логотип Ваш Подъёмник",
      },
    ],
  },
  twitter: {
    card: "summary",
    title: HOME_TITLE,
    description: HOME_DESCRIPTION,
    images: ["/vash-podyomnik-logo.jpg"],
  },
  icons: {
    icon: "/vash-podyomnik-logo.jpg",
    shortcut: "/vash-podyomnik-logo.jpg",
    apple: "/vash-podyomnik-logo.jpg",
  },
  other: {
    "codex-preview": "development",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ru">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased`}
      >
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(ORGANIZATION_JSON_LD) }}
        />
        {children}
        <Analytics />
      </body>
    </html>
  );
}
