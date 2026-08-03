import { eq } from "drizzle-orm";
import { getAdminSession } from "../../../../../lib/admin-auth";
import { getDb } from "../../../../../db";
import { worksImages } from "../../../../../db/schema";

async function guardAdmin() {
  const ok = await getAdminSession();
  if (!ok) return Response.json({ error: "Не авторизован" }, { status: 401 });
  return null;
}

export async function PATCH(request: Request, { params }: { params: { id: string } }) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const id = parseInt(params.id, 10);
  if (isNaN(id)) return Response.json({ error: "Неверный id" }, { status: 400 });

  try {
    const body = (await request.json()) as { url?: string; caption?: string; sortOrder?: number };
    const db = getDb();
    const [row] = await db
      .update(worksImages)
      .set({
        ...(body.url !== undefined ? { url: body.url.trim() } : {}),
        ...(body.caption !== undefined ? { caption: body.caption.trim() } : {}),
        ...(body.sortOrder !== undefined ? { sortOrder: body.sortOrder } : {}),
      })
      .where(eq(worksImages.id, id))
      .returning();

    if (!row) return Response.json({ error: "Не найдено" }, { status: 404 });
    return Response.json({ work: row });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

export async function DELETE(_request: Request, { params }: { params: { id: string } }) {
  const denied = await guardAdmin();
  if (denied) return denied;

  const id = parseInt(params.id, 10);
  if (isNaN(id)) return Response.json({ error: "Неверный id" }, { status: 400 });

  try {
    const db = getDb();
    await db.delete(worksImages).where(eq(worksImages.id, id));
    return Response.json({ ok: true });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
