"use client";

export function LogoutButton() {
  async function handleLogout() {
    await fetch("/api/admin/logout", { method: "POST" });
    window.location.href = "/admin/login";
  }

  return (
    <button type="button" className="admin-logout-btn" onClick={handleLogout}>
      Выйти
    </button>
  );
}
