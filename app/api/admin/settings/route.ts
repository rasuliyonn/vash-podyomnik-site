import { getAdminSession, checkAdminPassword, hashPassword } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { siteContent } from "../../../../db/schema";

async function guardAdmin() {
  const ok = await getAdminSession();
  if (!ok) return Response.json({ error: "Не авторизован" }, { status: 401 });
  return null;
}

export async function PUT(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as { currentPassword?: string; newPassword?: string };
    const { currentPassword = "", newPassword = "" } = body;

    if (!newPassword || newPassword.length < 4) {
      return Response.json({ error: "Новый пароль: минимум 4 символа" }, { status: 400 });
    }

    const ok = await checkAdminPassword(currentPassword);
    if (!ok) {
      return Response.json({ error: "Неверный текущий пароль" }, { status: 401 });
    }

    const hash = await hashPassword(newPassword);
    const db = getDb();
    await db
      .insert(siteContent)
      .values({ key: "admin_password_hash", value: hash, updatedAt: new Date().toISOString() })
      .onConflictDoUpdate({
        target: siteContent.key,
        set: { value: hash, updatedAt: new Date().toISOString() },
      });

    return Response.json({ ok: true });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
