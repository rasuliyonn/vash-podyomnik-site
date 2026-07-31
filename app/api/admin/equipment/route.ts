import { asc } from "drizzle-orm";
import { getAdminSession } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { equipmentTypes } from "../../../../db/schema";

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
      .from(equipmentTypes)
      .orderBy(asc(equipmentTypes.sortOrder), asc(equipmentTypes.id));
    return Response.json({ equipment: rows });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

export async function POST(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as {
      slug?: string;
      title?: string;
      description?: string;
      featuresJson?: string;
      advantagesJson?: string;
      applicationsJson?: string;
      techSpecsJson?: string;
      videoUrl?: string;
      sortOrder?: number;
      visible?: boolean;
    };

    const slug = body.slug?.trim() ?? "";
    const title = body.title?.trim() ?? "";
    if (!slug || !title) {
      return Response.json({ error: "slug и title обязательны" }, { status: 400 });
    }

    const db = getDb();
    const [row] = await db
      .insert(equipmentTypes)
      .values({
        slug,
        title,
        description: body.description ?? "",
        featuresJson: body.featuresJson ?? "[]",
        advantagesJson: body.advantagesJson ?? "[]",
        applicationsJson: body.applicationsJson ?? "[]",
        techSpecsJson: body.techSpecsJson ?? "[]",
        videoUrl: body.videoUrl ?? null,
        sortOrder: body.sortOrder ?? 0,
        visible: body.visible ?? true,
      })
      .returning();

    return Response.json({ equipment: row }, { status: 201 });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
