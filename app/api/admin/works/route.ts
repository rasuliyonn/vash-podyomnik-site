import { asc, eq } from "drizzle-orm";
import { getAdminSession } from "../../../../lib/admin-auth";
import { getDb } from "../../../../db";
import { worksImages } from "../../../../db/schema";

// Bulk-import static works files from public/works/
export async function PUT(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as { action?: string; total?: number };
    if (body.action !== "import-static") {
      return Response.json({ error: "Unknown action" }, { status: 400 });
    }

    const total = body.total ?? 71;
    const db = getDb();

    // Check how many already exist with static URLs
    const existing = await db.select({ url: worksImages.url }).from(worksImages);
    const existingUrls = new Set(existing.map((r) => r.url));

    const toInsert = [];
    for (let i = 1; i <= total; i++) {
      const url = `/works/work-${i}.jpg`;
      if (!existingUrls.has(url)) {
        toInsert.push({ url, caption: "", sortOrder: i - 1 });
      }
    }

    if (toInsert.length === 0) {
      return Response.json({ inserted: 0, message: "Все файлы уже импортированы" });
    }

    // D1 limit: 100 bound params per query. Each row = 3 params → max 30 rows/batch.
    const BATCH = 30;
    for (let i = 0; i < toInsert.length; i += BATCH) {
      await db.insert(worksImages).values(toInsert.slice(i, i + BATCH));
    }
    return Response.json({ inserted: toInsert.length });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

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
      .from(worksImages)
      .orderBy(asc(worksImages.sortOrder), asc(worksImages.id));
    return Response.json({ works: rows });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}

export async function POST(request: Request) {
  const denied = await guardAdmin();
  if (denied) return denied;

  try {
    const body = (await request.json()) as {
      url?: string;
      caption?: string;
      sortOrder?: number;
      imageData?: string;
    };
    const db = getDb();

    if (body.imageData) {
      // File upload: store base64 in DB, set url to /api/works-image/{id}
      const [row] = await db
        .insert(worksImages)
        .values({
          url: "pending",
          imageData: body.imageData,
          caption: body.caption?.trim() ?? "",
          sortOrder: body.sortOrder ?? 0,
        })
        .returning();

      const [updated] = await db
        .update(worksImages)
        .set({ url: `/api/works-image/${row.id}` })
        .where(eq(worksImages.id, row.id))
        .returning();

      return Response.json({ work: updated }, { status: 201 });
    }

    const url = body.url?.trim() ?? "";
    if (!url) return Response.json({ error: "url или imageData обязателен" }, { status: 400 });

    const [row] = await db
      .insert(worksImages)
      .values({
        url,
        caption: body.caption?.trim() ?? "",
        sortOrder: body.sortOrder ?? 0,
      })
      .returning();

    return Response.json({ work: row }, { status: 201 });
  } catch (error) {
    return Response.json({ error: String(error) }, { status: 500 });
  }
}
