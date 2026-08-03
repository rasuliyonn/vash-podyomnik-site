import { requireAdmin } from "../../lib/admin-auth";
import { LogoutButton } from "./logout-button";

export default async function AdminDashboard() {
  await requireAdmin();

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div className="brand">
          <img className="brand-logo" src="/vash-podyomnik-logo.jpg" alt="Логотип" />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Административная панель</small>
          </span>
        </div>
        <LogoutButton />
      </header>

      <nav className="admin-nav">
        <a href="/admin/equipment">Оборудование</a>
        <a href="/admin/works">Наши работы</a>
        <a href="/admin/submissions">Заявки</a>
        <a href="/admin/content">Контент</a>
        <a href="/" target="_blank" rel="noopener">Сайт ↗</a>
      </nav>

      <main className="admin-main">
        <h1>Панель управления</h1>
        <div className="admin-dashboard-grid">
          <a href="/admin/equipment" className="admin-card">
            <span className="admin-card-icon">🏗</span>
            <h2>Оборудование</h2>
            <p>Управление типами, галереями, описаниями и документами</p>
          </a>
          <a href="/admin/submissions" className="admin-card">
            <span className="admin-card-icon">📬</span>
            <h2>Заявки</h2>
            <p>Просмотр и обработка входящих заявок на расчёт</p>
          </a>
          <a href="/admin/works" className="admin-card">
            <span className="admin-card-icon">🖼</span>
            <h2>Наши работы</h2>
            <p>Управление галереей выполненных объектов (добавление, удаление, порядок)</p>
          </a>
          <a href="/admin/content" className="admin-card">
            <span className="admin-card-icon">✏️</span>
            <h2>Контент</h2>
            <p>Редактирование всех текстов, заголовков, списков и настроек сайта</p>
          </a>
          <a href="/admin/settings" className="admin-card">
            <span className="admin-card-icon">⚙️</span>
            <h2>Настройки</h2>
            <p>Смена пароля администратора</p>
          </a>
        </div>
      </main>
    </div>
  );
}
