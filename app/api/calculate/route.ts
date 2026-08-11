import { getDb } from "../../../db";
import { formSubmissions } from "../../../db/schema";
import { getMaxBotSettings, sendMaxMessage, formatSubmissionMessage } from "../../../lib/max-bot";

export async function POST(request: Request) {
  try {
    const body = (await request.json()) as {
      name?: string;
      phone?: string;
      email?: string;
      liftType?: string;
      capacity?: string;
      height?: string;
      platformSize?: string;
      city?: string;
      stops?: string;
    };

    const name = body.name?.trim() ?? "";
    const phone = body.phone?.trim() ?? "";
    const email = body.email?.trim() ?? "";
    const liftType = body.liftType?.trim() ?? "";
    const capacity = body.capacity?.trim() ?? "";
    const height = body.height?.trim() ?? "";
    const platformSize = body.platformSize?.trim() ?? "";
    const city = body.city?.trim() ?? "";
    const stops = body.stops?.trim() ?? "";

    if (!phone) {
      return Response.json({ error: "Укажите телефон для связи" }, { status: 400 });
    }
    if (!liftType || !capacity || !stops) {
      return Response.json({ error: "Обязательные поля отсутствуют" }, { status: 400 });
    }

    const db = getDb();
    const [row] = await db
      .insert(formSubmissions)
      .values({ name, phone, email, liftType, capacity, height, platformSize, city, stops })
      .returning();

    // Forward the submission to the MAX messenger bot if configured.
    try {
      const settings = await getMaxBotSettings();
      if (settings.enabled) {
        const result = await sendMaxMessage(settings, formatSubmissionMessage(row));
        if (!result.ok) console.error("[max-bot]", result.error);
      }
    } catch (error) {
      console.error("[max-bot]", error);
    }

    return Response.json({ ok: true, id: row.id }, { status: 201 });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Ошибка сервера";
    return Response.json({ error: message }, { status: 500 });
  }
}
