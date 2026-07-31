import { requireAdmin } from "../../../../lib/admin-auth";
import { LogoutButton } from "../../logout-button";
import { EquipmentForm } from "../equipment-form";

export default async function NewEquipmentPage() {
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
        <a href="/admin">← Панель</a>
        <a href="/admin/equipment">Оборудование</a>
        <span className="admin-nav-current">Новый тип</span>
      </nav>

      <main className="admin-main">
        <h1>Новый тип оборудования</h1>
        <EquipmentForm />
      </main>
    </div>
  );
}
