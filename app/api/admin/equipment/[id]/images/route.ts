import { asc, eq } from "drizzle-orm";
import { getAdminSession } from "../../../../../../lib/admin-auth";
import { getDb } from "../../../../../../db";
import { equipmentImages } from "../../../../../../db/schema";

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
    .from(equipmentImages)
    .where(eq(equipmentImages.equipmentId, parseInt(id, 10)))
    .orderBy(asc(equipmentImages.sortOrder), asc(equipmentImages.id));

  return Response.json({ images: rows });
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
    caption?: string;
    imageType?: string;
    sortOrder?: number;
  };

  const url = body.url?.trim() ?? "";
  if (!url) return Response.json({ error: "url обязателен" }, { status: 400 });

  const db = getDb();
  const [row] = await db
    .insert(equipmentImages)
    .values({
      equipmentId: parseInt(id, 10),
      url,
      caption: body.caption ?? "",
      imageType: body.imageType ?? "gallery",
      sortOrder: body.sortOrder ?? 0,
    })
    .returning();

  return Response.json({ image: row }, { status: 201 });
}

export async function DELETE(
  request: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const { id: _equipId } = await params;
  const body = (await request.json()) as { imageId?: number };
  if (!body.imageId) return Response.json({ error: "imageId обязателен" }, { status: 400 });

  const db = getDb();
  await db
    .delete(equipmentImages)
    .where(eq(equipmentImages.id, body.imageId));

  return Response.json({ ok: true });
}
