import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export const metadata: Metadata = {
  title: {
    default: "Ваш Подъёмник",
    template: "%s — Ваш Подъёмник",
  },
  description:
    "«Ваш Подъёмник» — производство, проектирование и монтаж грузовых подъёмников и лифтов под ваш объект. Доставка по всей России.",
  other: {
    "codex-preview": "development",
  },
  icons: {
    icon: "/vash-podyomnik-logo.jpg",
    shortcut: "/vash-podyomnik-logo.jpg",
    apple: "/vash-podyomnik-logo.jpg",
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
        {children}
      </body>
    </html>
  );
}
