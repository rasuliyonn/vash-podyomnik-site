import { env } from "cloudflare:workers";
import { drizzle } from "drizzle-orm/d1";
import * as schema from "./schema";

export function getDb() {
  if (!env.DB) {
    throw new Error(
      "Cloudflare D1 binding `DB` не настроен. Запустите start.sh для локальной разработки или настройте D1 binding в wrangler.toml для деплоя."
    );
  }

  return drizzle(env.DB, { schema });
}
