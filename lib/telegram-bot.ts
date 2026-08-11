import { eq } from "drizzle-orm";
import { getDb } from "../db";
import { siteContent } from "../db/schema";

const TELEGRAM_BOT_SETTINGS_KEY = "telegram_bot_settings";
const TELEGRAM_API_BASE = "https://api.telegram.org";

export type TelegramBotSettings = {
  enabled: boolean;
  token: string;
  chatId: string;
};

const DEFAULT_SETTINGS: TelegramBotSettings = {
  enabled: false,
  token: "",
  chatId: "",
};

export async function getTelegramBotSettings(): Promise<TelegramBotSettings> {
  try {
    const db = getDb();
    const rows = await db
      .select()
      .from(siteContent)
      .where(eq(siteContent.key, TELEGRAM_BOT_SETTINGS_KEY))
      .limit(1);
    if (rows.length === 0) return { ...DEFAULT_SETTINGS };
    const parsed = JSON.parse(rows[0].value) as Partial<TelegramBotSettings>;
    return { ...DEFAULT_SETTINGS, ...parsed };
  } catch {
    return { ...DEFAULT_SETTINGS };
  }
}

export async function saveTelegramBotSettings(settings: TelegramBotSettings): Promise<void> {
  const db = getDb();
  await db
    .insert(siteContent)
    .values({
      key: TELEGRAM_BOT_SETTINGS_KEY,
      value: JSON.stringify(settings),
      updatedAt: new Date().toISOString(),
    })
    .onConflictDoUpdate({
      target: siteContent.key,
      set: { value: JSON.stringify(settings), updatedAt: new Date().toISOString() },
    });
}

/**
 * Sends a text message via the Telegram Bot API.
 *
 * Auth: the token (format `<bot_id>:<auth_hash>`) is embedded in the API URL.
 * Recipient is a `chat_id` — a numeric user/group id (e.g. `123456789`) or a
 * channel username (`@my_channel`).
 */
export async function sendTelegramMessage(
  settings: Pick<TelegramBotSettings, "token" | "chatId">,
  text: string,
): Promise<{ ok: boolean; error?: string }> {
  const token = settings.token.trim();
  const chatId = settings.chatId.trim();

  if (!token) return { ok: false, error: "Не задан токен бота" };
  if (!chatId) return { ok: false, error: "Не задан chat_id получателя" };

  try {
    const res = await fetch(`${TELEGRAM_API_BASE}/bot${token}/sendMessage`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ chat_id: chatId, text }),
      signal: AbortSignal.timeout(8000),
    });
    if (!res.ok) {
      const body = await res.text().catch(() => "");
      let detail = body.slice(0, 200);
      // Telegram returns a JSON body with a human-readable `description`
      try {
        const json = JSON.parse(body) as { description?: string };
        if (json.description) detail = json.description;
      } catch {
        // keep raw body
      }
      return { ok: false, error: `Telegram API ответил ${res.status}: ${detail}` };
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
