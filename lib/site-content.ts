import { getDb } from "../db";
import { siteContent } from "../db/schema";
import { defaultContent, SiteContent } from "./default-content";

export async function getSiteContent(): Promise<SiteContent> {
  try {
    const db = getDb();
    const rows = await db.select().from(siteContent);
    const result: SiteContent = JSON.parse(JSON.stringify(defaultContent));
    for (const row of rows) {
      // Image data stored separately — just set a boolean flag, skip loading large base64
      if (row.key.startsWith("img_")) {
        (result as Record<string, unknown>)[row.key] = true;
        continue;
      }
      try {
        const dbVal = JSON.parse(row.value);
        const def = (result as Record<string, unknown>)[row.key];
        // Objects: merge DB over defaults so missing fields fall back to default
        // Arrays and primitives: replace entirely
        if (dbVal && typeof dbVal === "object" && !Array.isArray(dbVal) &&
            def && typeof def === "object" && !Array.isArray(def)) {
          (result as Record<string, unknown>)[row.key] = { ...(def as object), ...dbVal };
        } else {
          (result as Record<string, unknown>)[row.key] = dbVal;
        }
      } catch {
        // skip bad JSON
      }
    }
    return result;
  } catch {
    return defaultContent;
  }
}
