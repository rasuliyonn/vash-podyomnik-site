import { desc, eq } from "drizzle-orm";
import { getAdminSession } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { formSubmissions } from "../../../../db/schema";

async function guardAdmin() {
  const ok = await getAdminSession();
  if (!ok) return Response.json({ error: "Не авторизован" }, { status: 401 });
  return null;
}

export async function GET() {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const db = getDb();
    const rows = await db
      .select()
      .from(formSubmissions)
      .orderBy(desc(formSubmissions.createdAt), desc(formSubmissions.id))
      .limit(200);
    return Response.json({ submissions: rows });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as { id?: number; status?: string };
    if (!body.id || !body.status) {
      return Response.json({ error: "id и status обязательны" }, { status: 400 });
    }

    const db = getDb();
    const [row] = await db
      .update(formSubmissions)
      .set({ status: body.status })
      .where(eq(formSubmissions.id, body.id))
      .returning();

    return Response.json({ submission: row });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
