import { getAdminSession } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { siteContent } from "../../../../db/schema";

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
    const rows = await db.select().from(siteContent);
    const result: Record<string, unknown> = {};
    for (const row of rows) {
      try {
        result[row.key] = JSON.parse(row.value);
      } catch {
        result[row.key] = row.value;
      }
    }
    return Response.json({ content: result });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

export async function PUT(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as { key: string; value: unknown };
    const { key, value } = body;

    if (!key) return Response.json({ error: "key обязателен" }, { status: 400 });

    const db = getDb();
    const valueStr = JSON.stringify(value);

    await db
      .insert(siteContent)
      .values({ key, value: valueStr, updatedAt: new Date().toISOString() })
      .onConflictDoUpdate({
        target: siteContent.key,
        set: { value: valueStr, updatedAt: new Date().toISOString() },
      });

    return Response.json({ ok: true });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
