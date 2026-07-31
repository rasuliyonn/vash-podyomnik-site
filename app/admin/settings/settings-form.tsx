"use client";
import { FormEvent, useState } from "react";

export function SettingsForm() {
  const [current, setCurrent] = useState("");
  const [next, setNext] = useState("");
  const [confirm, setConfirm] = useState("");
  const [status, setStatus] = useState<"idle" | "saving" | "ok" | string>("idle");

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    if (next !== confirm) { setStatus("Пароли не совпадают"); return; }
    setStatus("saving");
    try {
      const res = await fetch("/api/admin/settings", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ currentPassword: current, newPassword: next }),
      });
      const data = (await res.json()) as { ok?: boolean; error?: string };
      if (data.ok) {
        setStatus("ok");
        setCurrent(""); setNext(""); setConfirm("");
      } else {
        setStatus(data.error ?? "Ошибка");
      }
    } catch (err) {
      setStatus(String(err));
    }
  }

  return (
    <form className="admin-form" onSubmit={handleSubmit} style={{ maxWidth: 480 }}>
      <h2>Смена пароля</h2>
      <label className="admin-label">
        <span>Текущий пароль</span>
        <input type="password" value={current} onChange={e => setCurrent(e.target.value)} required autoComplete="current-password" />
      </label>
      <label className="admin-label">
        <span>Новый пароль</span>
        <input type="password" value={next} onChange={e => setNext(e.target.value)} required minLength={4} autoComplete="new-password" />
      </label>
      <label className="admin-label">
        <span>Повторите новый пароль</span>
        <input type="password" value={confirm} onChange={e => setConfirm(e.target.value)} required autoComplete="new-password" />
      </label>
      {status !== "idle" && status !== "saving" && status !== "ok" && (
        <p className="admin-error">{status}</p>
      )}
      {status === "ok" && <p style={{ color: "green" }}>✓ Пароль изменён</p>}
      <div className="admin-form-actions">
        <button type="submit" className="primary-button" disabled={status === "saving"}>
          {status === "saving" ? "Сохранение…" : "Изменить пароль"} <span>✓</span>
        </button>
      </div>
      <p className="admin-hint" style={{ marginTop: 16 }}>
        По умолчанию пароль: <code>admin</code>. После смены хранится в базе данных.
      </p>
    </form>
  );
}
