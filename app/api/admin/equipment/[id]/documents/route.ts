import { asc, eq } from "drizzle-orm";
import { getAdminSession } from "../../../../../../lib/admin-auth";
import { getDb } from "../../../../../../db";
import { equipmentDocuments } from "../../../../../../db/schema";

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
  const rows = await db
    .select()
    .from(equipmentDocuments)
    .where(eq(equipmentDocuments.equipmentId, parseInt(id, 10)))
    .orderBy(asc(equipmentDocuments.id));

  return Response.json({ documents: rows });
}

export async function POST(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id } = await params;
  const body = (await request.json()) as {
    url?: string;
    name?: string;
    docType?: string;
    sizeKb?: number;
  };

  const url = body.url?.trim() ?? "";
  const name = body.name?.trim() ?? "";
  if (!url || !name) return Response.json({ error: "url и name обязательны" }, { status: 400 });

  const db = getDb();
  const [row] = await db
    .insert(equipmentDocuments)
    .values({
      equipmentId: parseInt(id, 10),
      url,
      name,
      docType: body.docType ?? "pdf",
      sizeKb: body.sizeKb ?? null,
    })
    .returning();

  return Response.json({ document: row }, { status: 201 });
}

export async function DELETE(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id: _equipId } = await params;
  const body = (await request.json()) as { documentId?: number };
  if (!body.documentId) return Response.json({ error: "documentId обязателен" }, { status: 400 });

  const db = getDb();
  await db
    .delete(equipmentDocuments)
    .where(eq(equipmentDocuments.id, body.documentId));

  return Response.json({ ok: true });
}
