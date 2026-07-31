import { checkAdminPassword, createSessionToken, SESSION_COOKIE } from "../../../../lib/admin-auth";

export async function POST(request: Request) {
  try {
    const body = (await request.json()) as { password?: string };
    const password = body.password?.trim() ?? "";

    if (!(await checkAdminPassword(password))) {
      return Response.json({ error: "Неверный пароль" }, { status: 401 });
    }

    const token = await createSessionToken();

    return new Response(JSON.stringify({ ok: true }), {
      status: 200,
      headers: {
        "Content-Type": "application/json",
        "Set-Cookie": `${SESSION_COOKIE}=${token}; Path=/; HttpOnly; SameSite=Lax`,
      },
    });
  } catch (error) {
    const message = error instanceof Error ? error.message : "Ошибка сервера";
    return Response.json({ error: message }, { status: 500 });
  }
}
