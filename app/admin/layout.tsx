// Admin sub-layout — no <html>/<body>, those are in app/layout.tsx.
// Auth check is done per-page via requireAdmin() to allow the login page through.

export const metadata = { title: "Административная панель — Ваш Подъёмник" };

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  return <div className="admin-root">{children}</div>;
}
