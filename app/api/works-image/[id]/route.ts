import { eq } from "drizzle-orm";
import { getDb } from "../../../../db";
import { worksImages } from "../../../../db/schema";

export async function GET(_req: Request, { params }: { params: { id: string } }) {
  const id = parseInt(params.id, 10);
  if (isNaN(id)) return new Response("Invalid id", { status: 400 });

  try {
    const db = getDb();
    const rows = await db
      .select({ imageData: worksImages.imageData })
      .from(worksImages)
      .where(eq(worksImages.id, id))
      .limit(1);

    if (!rows.length || !rows[0].imageData) return new Response("Not found", { status: 404 });

    const dataUrl = rows[0].imageData;
    const commaIdx = dataUrl.indexOf(",");
    if (commaIdx === -1) return new Response("Invalid image data", { status: 500 });

    const header = dataUrl.slice(0, commaIdx);
    const b64 = dataUrl.slice(commaIdx + 1);
    const mimeMatch = header.match(/data:([^;]+)/);
    const mime = mimeMatch?.[1] ?? "image/jpeg";
    const bytes = Uint8Array.from(atob(b64), (c) => c.charCodeAt(0));

    return new Response(bytes, {
      headers: {
        "Content-Type": mime,
        "Cache-Control": "public, max-age=3600",
      },
    });
  } catch (error) {
    return new Response("Error: " + String(error), { status: 500 });
  }
}
