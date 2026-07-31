import { getDb } from "../../../db";
import { formSubmissions } from "../../../db/schema";

export async function POST(request: Request) {
  try {
    const body = (await request.json()) as {
      liftType?: string;
      capacity?: string;
      height?: string;
      platformSize?: string;
      city?: string;
      stops?: string;
    };

    const liftType = body.liftType?.trim() ?? "";
    const capacity = body.capacity?.trim() ?? "";
    const height = body.height?.trim() ?? "";
    const platformSize = body.platformSize?.trim() ?? "";
    const city = body.city?.trim() ?? "";
    const stops = body.stops?.trim() ?? "";

    if (!liftType || !capacity || !stops) {
      return Response.json({ error: "Обязательные поля отсутствуют" }, { status: 400 });
    }

    const db = getDb();
    const [row] = await db
      .insert(formSubmissions)
      .values({ liftType, capacity, height, platformSize, city, stops })
      .returning();

    return Response.json({ ok: true, id: row.id }, { status: 201 });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Ошибка сервера";
    return Response.json({ error: message }, { status: 500 });
  }
}
