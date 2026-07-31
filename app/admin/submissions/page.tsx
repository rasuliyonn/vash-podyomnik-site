import { desc } from "drizzle-orm";
import { requireAdmin } from "../../../lib/admin-auth";
import { getDb } from "../../../db";
import { formSubmissions } from "../../../db/schema";
import { LogoutButton } from "../logout-button";
import { SubmissionsClient } from "./submissions-client";

export default async function SubmissionsPage() {
  await requireAdmin();

  let rows: typeof formSubmissions.$inferSelect[] = [];
  let dbError = "";

  try {
    const db = getDb();
    rows = await db
      .select()
      .from(formSubmissions)
      .orderBy(desc(formSubmissions.createdAt), desc(formSubmissions.id))
      .limit(200);
  } catch (e) {
    dbError = String(e);
  }

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
        <a href="/admin">← Панель</a>
        <a href="/admin/equipment">Оборудование</a>
        <a href="/admin/submissions" className="active">Заявки</a>
      </nav>

      <main className="admin-main">
        <h1>Заявки на расчёт</h1>

        {dbError && (
          <div className="admin-error-box">
            <b>Ошибка БД:</b> {dbError}
          </div>
        )}

        {rows.length === 0 && !dbError && (
          <p className="admin-empty">Заявок пока нет.</p>
        )}

        {rows.length > 0 && <SubmissionsClient initialRows={rows} />}
      </main>
    </div>
  );
}
