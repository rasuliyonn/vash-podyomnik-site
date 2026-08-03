import { createRequire } from "node:module";
import { drizzle } from "drizzle-orm/libsql";
import type { LibSQLDatabase } from "drizzle-orm/libsql";
import * as schema from "./schema";

const _require = createRequire(import.meta.url);
let _db: LibSQLDatabase<typeof schema> | null = null;

function initDb(): LibSQLDatabase<typeof schema> {
  if (!_db) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const { createClient } = _require("@libsql/client/node") as any;
    const client = createClient({ url: "file:./db.sqlite" });
    _db = drizzle(client, { schema });
  }
  return _db;
}

export function getDb(): LibSQLDatabase<typeof schema> {
  return initDb();
}

// Proxy so `import { db }` works without top-level initialization
export const db: LibSQLDatabase<typeof schema> = new Proxy(
  {} as LibSQLDatabase<typeof schema>,
  {
    get(_target, prop) {
      return initDb()[prop as keyof LibSQLDatabase<typeof schema>];
    },
  }
);
