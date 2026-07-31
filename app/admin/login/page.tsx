"use client";

import { FormEvent, useState } from "react";

export default function AdminLogin() {
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);
    try {
      const res = await fetch("/api/admin/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ password }),
      });
      if (res.ok) {
        window.location.href = "/admin";
      } else {
        const data = (await res.json()) as { error?: string };
        setError(data.error ?? "Ошибка входа");
      }
    } catch {
      setError("Ошибка сети");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="admin-login-page">
      <div className="admin-login-box">
        <div className="brand admin-login-brand">
          <img
            className="brand-logo"
            src="/vash-podyomnik-logo.jpg"
            alt="Логотип"
          />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Административная панель</small>
          </span>
        </div>

        <form onSubmit={handleSubmit} className="admin-login-form">
          <label>
            <span>Пароль</span>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Введите пароль"
              autoFocus
              required
            />
          </label>
          {error && <p className="admin-error">{error}</p>}
          <button
            type="submit"
            className="primary-button form-button"
            disabled={loading}
          >
            {loading ? "Вход…" : "Войти"}
            <span aria-hidden="true">→</span>
          </button>
        </form>
      </div>
    </div>
  );
}
