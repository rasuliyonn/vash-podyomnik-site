import { eq } from "drizzle-orm";
import { getAdminSession } from "../../../../../lib/admin-auth";
import { getDb } from "../../../../../db";
import { equipmentTypes } from "../../../../../db/schema";

async function guardAdmin() {
  const ok = await getAdminSession();
  if (!ok) return Response.json({ error: "Не авторизован" }, { status: 401 });
  return null;
}

export async function GET(
  _req: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id } = await params;
  const db = getDb();
  const [row] = await db
    .select()
    .from(equipmentTypes)
    .where(eq(equipmentTypes.id, parseInt(id, 10)));

  if (!row) return Response.json({ error: "Не найдено" }, { status: 404 });
  return Response.json({ equipment: row });
}

export async function PUT(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id } = await params;
  const body = (await request.json()) as Partial<{
    slug: string;
    title: string;
    description: string;
    featuresJson: string;
    advantagesJson: string;
    applicationsJson: string;
    techSpecsJson: string;
    videoUrl: string | null;
    sortOrder: number;
    visible: boolean;
  }>;

  const db = getDb();
  const [row] = await db
    .update(equipmentTypes)
    .set(body)
    .where(eq(equipmentTypes.id, parseInt(id, 10)))
    .returning();

  if (!row) return Response.json({ error: "Не найдено" }, { status: 404 });
  return Response.json({ equipment: row });
}

export async function DELETE(
  _req: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id } = await params;
  const db = getDb();
  await db
    .delete(equipmentTypes)
    .where(eq(equipmentTypes.id, parseInt(id, 10)));

  return Response.json({ ok: true });
}
