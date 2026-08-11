import { eq } from "drizzle-orm";
import { getDb } from "../db";
import { siteContent } from "../db/schema";

const MAX_BOT_SETTINGS_KEY = "max_bot_settings";
// Overridable so the API can be reached through a proxy/relay if needed.
const MAX_API_BASE = process.env.MAX_API_BASE ?? "https://platform-api2.max.ru";

export type MaxBotSettings = {
  enabled: boolean;
  token: string;
  userId: string;
  chatId: string;
};

const DEFAULT_SETTINGS: MaxBotSettings = {
  enabled: false,
  token: "",
  userId: "",
  chatId: "",
};

export async function getMaxBotSettings(): Promise<MaxBotSettings> {
  try {
    const db = getDb();
    const rows = await db
      .select()
      .from(siteContent)
      .where(eq(siteContent.key, MAX_BOT_SETTINGS_KEY))
      .limit(1);
    if (rows.length === 0) return { ...DEFAULT_SETTINGS };
    const parsed = JSON.parse(rows[0].value) as Partial<MaxBotSettings>;
    return { ...DEFAULT_SETTINGS, ...parsed };
  } catch {
    return { ...DEFAULT_SETTINGS };
  }
}

export async function saveMaxBotSettings(settings: MaxBotSettings): Promise<void> {
  const db = getDb();
  await db
    .insert(siteContent)
    .values({
      key: MAX_BOT_SETTINGS_KEY,
      value: JSON.stringify(settings),
      updatedAt: new Date().toISOString(),
    })
    .onConflictDoUpdate({
      target: siteContent.key,
      set: { value: JSON.stringify(settings), updatedAt: new Date().toISOString() },
    });
}

/**
 * Sends a text message to the MAX bot recipient.
 * Auth: token is passed in the `Authorization` header (no "Bearer" prefix),
 * recipient via `user_id` (personal dialog) or `chat_id` (group/channel).
 */
export async function sendMaxMessage(
  settings: Pick<MaxBotSettings, "token" | "userId" | "chatId">,
  text: string,
): Promise<{ ok: boolean; error?: string }> {
  const token = settings.token.trim();
  const userId = settings.userId.trim();
  const chatId = settings.chatId.trim();

  if (!token) return { ok: false, error: "Не задан токен бота" };

  const query = new URLSearchParams();
  if (userId) {
    query.set("user_id", userId);
  } else if (chatId) {
    query.set("chat_id", chatId);
  } else {
    return { ok: false, error: "Не задан user_id (личный диалог) или chat_id (чат/канал)" };
  }

  try {
    const res = await fetch(`${MAX_API_BASE}/messages?${query.toString()}`, {
      method: "POST",
      headers: {
        Authorization: token,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ text }),
      signal: AbortSignal.timeout(8000),
    });
    if (!res.ok) {
      const body = await res.text().catch(() => "");
      return { ok: false, error: `MAX API ответил ${res.status}: ${body.slice(0, 200)}` };
    }
    return { ok: true };
  } catch (e) {
    return { ok: false, error: e instanceof Error ? e.message : String(e) };
  }
}

/** Builds a readable notification text from a stored form submission. */
export function formatSubmissionMessage(row: {
  id: number;
  name: string;
  phone: string;
  email: string;
  liftType: string;
  capacity: string;
  height: string;
  platformSize: string;
  city: string;
  stops: string;
  createdAt: string;
}): string {
  const lines = [
    "🔔 Новая заявка с сайта",
    "",
    `Заявка №${row.id}`,
    ...(row.name ? [`Имя: ${row.name}`] : []),
    ...(row.phone ? [`Телефон: ${row.phone}`] : []),
    ...(row.email ? [`Email: ${row.email}`] : []),
    `Тип оборудования: ${row.liftType}`,
    `Грузоподъёмность: ${row.capacity}`,
    ...(row.height ? [`Высота подъёма: ${row.height}`] : []),
    ...(row.platformSize ? [`Платформа: ${row.platformSize}`] : []),
    `Остановки: ${row.stops}`,
    ...(row.city ? [`Город/объект: ${row.city}`] : []),
    "",
    `Время: ${row.createdAt.slice(0, 16).replace("T", " ")}`,
  ];
  return lines.join("\n");
}
