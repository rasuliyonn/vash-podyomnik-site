import { getDb } from "../../../db";
import { siteContent } from "../../../db/schema";
import { eq } from "drizzle-orm";

export async function GET(request: Request) {
  const url = new URL(request.url);
  const key = url.searchParams.get("key") ?? "";

  if (!key.startsWith("img_")) {
    return new Response("Invalid key", { status: 400 });
  }

  try {
    const db = getDb();
    const rows = await db
      .select()
      .from(siteContent)
      .where(eq(siteContent.key, key))
      .limit(1);

    if (!rows.length || !rows[0].value) {
      return new Response("Not found", { status: 404 });
    }

    // Stored as JSON.stringify("data:image/jpeg;base64,...")
    const dataUrl = JSON.parse(rows[0].value) as string;
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
