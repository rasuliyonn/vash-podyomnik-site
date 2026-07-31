import { asc } from "drizzle-orm";
import { requireAdmin } from "../../../lib/admin-auth";
import { getDb } from "../../../db";
import { equipmentTypes } from "../../../db/schema";
import { LogoutButton } from "../logout-button";

export default async function AdminEquipmentList() {
  await requireAdmin();

  let rows: typeof equipmentTypes.$inferSelect[] = [];
  let dbError = "";
  try {
    const db = getDb();
    rows = await db
      .select()
      .from(equipmentTypes)
      .orderBy(asc(equipmentTypes.sortOrder), asc(equipmentTypes.id));
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
        <a href="/admin/equipment" className="active">Оборудование</a>
        <a href="/admin/submissions">Заявки</a>
      </nav>

      <main className="admin-main">
        <div className="admin-list-header">
          <h1>Оборудование</h1>
          <a href="/admin/equipment/new" className="primary-button">
            + Добавить тип
          </a>
        </div>

        {dbError && (
          <div className="admin-error-box">
            <b>Ошибка БД:</b> {dbError}
            <br />
            <small>
              Выполните <code>npm run db:generate</code> и примените миграцию.
            </small>
          </div>
        )}

        {rows.length === 0 && !dbError && (
          <p className="admin-empty">
            Нет записей. Добавьте первый тип оборудования.
          </p>
        )}

        <div className="admin-table-wrap">
          {rows.length > 0 && (
            <table className="admin-table">
              <thead>
                <tr>
                  <th>Slug</th>
                  <th>Название</th>
                  <th>Порядок</th>
                  <th>Видимость</th>
                  <th>Действия</th>
                </tr>
              </thead>
              <tbody>
                {rows.map((row) => (
                  <tr key={row.id}>
                    <td>
                      <code>{row.slug}</code>
                    </td>
                    <td>{row.title}</td>
                    <td>{row.sortOrder}</td>
                    <td>
                      <span className={row.visible ? "admin-badge green" : "admin-badge gray"}>
                        {row.visible ? "Опубликован" : "Скрыт"}
                      </span>
                    </td>
                    <td>
                      <div className="admin-actions">
                        <a
                          href={`/admin/equipment/${row.id}`}
                          className="admin-action-btn"
                        >
                          Редактировать
                        </a>
                        <a
                          href={`/equipment/${row.slug}`}
                          target="_blank"
                          rel="noopener"
                          className="admin-action-btn secondary"
                        >
                          На сайте ↗
                        </a>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </main>
    </div>
  );
}
