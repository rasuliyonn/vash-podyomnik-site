import { getAdminSession } from "../../../../lib/admin-auth";
import {
  getTelegramBotSettings,
  saveTelegramBotSettings,
  sendTelegramMessage,
  type TelegramBotSettings,
} from "../../../../lib/telegram-bot";

async function guardAdmin() {
  const ok = await getAdminSession();
  if (!ok) return Response.json({ error: "Не авторизован" }, { status: 401 });
  return null;
}

function mergeSettings(
  body: Partial<TelegramBotSettings>,
  current: TelegramBotSettings,
): TelegramBotSettings {
  return {
    enabled: typeof body.enabled === "boolean" ? body.enabled : current.enabled,
    token: (body.token ?? current.token).trim(),
    chatId: (body.chatId ?? current.chatId).trim(),
  };
}

export async function GET() {
  const denied = await guardAdmin();
  if (denied) return denied;

  const settings = await getTelegramBotSettings();
  return Response.json({ settings });
}

export async function PUT(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as Partial<TelegramBotSettings>;
    const current = await getTelegramBotSettings();
    const next = mergeSettings(body, current);
    await saveTelegramBotSettings(next);
    return Response.json({ ok: true, settings: next });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

/** Sends a test message using the provided (or already saved) settings. */
export async function POST(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as { settings?: Partial<TelegramBotSettings> };
    const current = await getTelegramBotSettings();
    const settings = mergeSettings(body.settings ?? {}, current);
    const result = await sendTelegramMessage(
      settings,
      "✅ Тестовое сообщение. Сайт успешно подключён к Telegram-боту.",
    );
    if (!result.ok) return Response.json({ error: result.error }, { status: 400 });
    return Response.json({ ok: true });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
