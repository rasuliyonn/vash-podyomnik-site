import { cookies } from "next/headers";

const COOKIE_NAME = "admin_session";
const SESSION_TTL_MS = 7 * 24 * 60 * 60 * 1000; // 7 days

function getSecret(): string {
  return process.env.ADMIN_SECRET ?? "dev-secret-change-me-in-prod";
}

async function hmac(message: string, secret: string): Promise<string> {
  const enc = new TextEncoder();
  const key = await crypto.subtle.importKey(
    "raw",
    enc.encode(secret),
    { name: "HMAC", hash: "SHA-256" },
    false,
    ["sign"],
  );
  const sig = await crypto.subtle.sign("HMAC", key, enc.encode(message));
  return btoa(String.fromCharCode(...new Uint8Array(sig)));
}

export async function hashPassword(password: string): Promise<string> {
  const enc = new TextEncoder();
  const buf = await crypto.subtle.digest("SHA-256", enc.encode(password));
  return btoa(String.fromCharCode(...new Uint8Array(buf)));
}

export async function checkAdminPassword(password: string): Promise<boolean> {
  try {
    const { getDb } = await import("../db");
    const { siteContent } = await import("../db/schema");
    const { eq } = await import("drizzle-orm");
    const db = getDb();
    const rows = await db
      .select()
      .from(siteContent)
      .where(eq(siteContent.key, "admin_password_hash"))
      .limit(1);
    if (rows.length > 0 && rows[0].value) {
      const hash = await hashPassword(password);
      return hash === rows[0].value;
    }
  } catch {
    // DB not available — fall through to env check
  }
  // Fall back to env ADMIN_PASSWORD or default "admin"
  const stored = process.env.ADMIN_PASSWORD ?? "admin";
  return password === stored;
}

export async function createSessionToken(): Promise<string> {
  const ts = Date.now().toString();
  const sig = await hmac(ts, getSecret());
  return `${ts}.${sig}`;
}

export async function verifySessionToken(token: string): Promise<boolean> {
  try {
    const dot = token.indexOf(".");
    if (dot === -1) return false;
    const ts = token.slice(0, dot);
    const sig = token.slice(dot + 1);
    if (Date.now() - parseInt(ts, 10) > SESSION_TTL_MS) return false;
    const expected = await hmac(ts, getSecret());
    return expected === sig;
  } catch {
    return false;
  }
}

export async function getAdminSession(): Promise<boolean> {
  const store = await cookies();
  const token = store.get(COOKIE_NAME)?.value;
  if (!token) return false;
  return verifySessionToken(token);
}

export const SESSION_COOKIE = COOKIE_NAME;

/** Call at top of any admin server page. Redirects to login if not authenticated. */
export async function requireAdmin(): Promise<void> {
  const { redirect } = await import("next/navigation");
  const ok = await getAdminSession();
  if (!ok) redirect("/admin/login");
}
