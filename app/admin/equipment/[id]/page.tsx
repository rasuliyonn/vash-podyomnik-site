import { eq, asc } from "drizzle-orm";
import { notFound } from "next/navigation";
import { requireAdmin } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { equipmentTypes, equipmentImages, equipmentDocuments } from "../../../../db/schema";
import { LogoutButton } from "../../logout-button";
import { EquipmentForm } from "../equipment-form";
import { DeleteEquipmentButton } from "./delete-button";

export default async function EditEquipmentPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  await requireAdmin();

  const { id } = await params;
  const numericId = parseInt(id, 10);

  const db = getDb();

  const [row] = await db
    .select()
    .from(equipmentTypes)
    .where(eq(equipmentTypes.id, numericId));

  if (!row) notFound();

  const images = await db
    .select()
    .from(equipmentImages)
    .where(eq(equipmentImages.equipmentId, numericId))
    .orderBy(asc(equipmentImages.sortOrder), asc(equipmentImages.id));

  const documents = await db
    .select()
    .from(equipmentDocuments)
    .where(eq(equipmentDocuments.equipmentId, numericId));

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
        <span className="admin-nav-current">{row.title}</span>
      </nav>

      <main className="admin-main">
        <div className="admin-list-header">
          <h1>{row.title}</h1>
          <div className="admin-actions">
            <a
              href={`/equipment/${row.slug}`}
              target="_blank"
              rel="noopener"
              className="admin-action-btn secondary"
            >
              На сайте ↗
            </a>
            <DeleteEquipmentButton equipmentId={row.id} />
          </div>
        </div>

        <EquipmentForm
          equipmentId={row.id}
          initial={{
            slug: row.slug,
            title: row.title,
            description: row.description,
            features: JSON.parse(row.featuresJson) as string[],
            advantages: JSON.parse(row.advantagesJson) as string[],
            applications: JSON.parse(row.applicationsJson) as string[],
            techSpecs: JSON.parse(row.techSpecsJson) as { name: string; value: string }[],
            videoUrl: row.videoUrl ?? "",
            sortOrder: row.sortOrder,
            visible: row.visible,
          }}
          initialImages={images}
          initialDocuments={documents}
        />
      </main>
    </div>
  );
}
