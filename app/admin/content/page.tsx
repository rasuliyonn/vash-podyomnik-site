import { requireAdmin } from "../../../lib/admin-auth";
import { getSiteContent } from "../../../lib/site-content";
import { ContentEditor } from "./content-editor";

export default async function AdminContentPage() {
  await requireAdmin();
  const content = await getSiteContent();

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div className="brand">
          <img className="brand-logo" src="/vash-podyomnik-logo.jpg" alt="Логотип" />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Редактор контента</small>
          </span>
        </div>
        <a href="/admin" className="admin-action-btn secondary">← Панель</a>
      </header>

      <nav className="admin-nav">
        <a href="/admin/equipment">Оборудование</a>
        <a href="/admin/submissions">Заявки</a>
        <a href="/admin/content" className="active">Контент</a>
        <a href="/" target="_blank" rel="noopener">Сайт ↗</a>
      </nav>

      <main className="admin-main">
        <h1>Контент сайта</h1>
        <p className="admin-hint">Каждый раздел сохраняется отдельно. Изменения применяются немедленно.</p>
        <ContentEditor initialContent={content} />
      </main>
    </div>
  );
}
