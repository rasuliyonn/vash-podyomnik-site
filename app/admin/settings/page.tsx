import { requireAdmin } from "../../../lib/admin-auth";
import { LogoutButton } from "../logout-button";
import { SettingsForm } from "./settings-form";

export default async function AdminSettingsPage() {
  await requireAdmin();

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div className="brand">
          <img className="brand-logo" src="/vash-podyomnik-logo.jpg" alt="Логотип" />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Настройки</small>
          </span>
        </div>
        <LogoutButton />
      </header>

      <nav className="admin-nav">
        <a href="/admin">← Панель</a>
        <a href="/admin/equipment">Оборудование</a>
        <a href="/admin/submissions">Заявки</a>
        <a href="/admin/content">Контент</a>
        <a href="/admin/settings" className="active">Настройки</a>
        <a href="/" target="_blank" rel="noopener">Сайт ↗</a>
      </nav>

      <main className="admin-main">
        <h1>Настройки</h1>
        <SettingsForm />
      </main>
    </div>
  );
}
