"use client";

import { FormEvent, useEffect, useState } from "react";

type Settings = { enabled: boolean; token: string; chatId: string };

const EMPTY: Settings = { enabled: false, token: "", chatId: "" };

function StatusText({ status, okText }: { status: string; okText: string }) {
  if (status === "idle" || status === "saving" || status === "sending") return null;
  if (status === "ok") return <p style={{ color: "green", margin: 0 }}>{okText}</p>;
  return <p className="admin-error" style={{ margin: 0 }}>{status}</p>;
}

export function TelegramBotSettingsForm() {
  const [settings, setSettings] = useState<Settings>(EMPTY);
  const [loaded, setLoaded] = useState(false);
  const [saveStatus, setSaveStatus] = useState<"idle" | "saving" | "ok" | string>("idle");
  const [testStatus, setTestStatus] = useState<"idle" | "sending" | "ok" | string>("idle");

  useEffect(() => {
    fetch("/api/admin/telegram-bot")
      .then((res) => (res.ok ? res.json() : null))
      .then((data) => {
        if (data?.settings) setSettings({ ...EMPTY, ...data.settings });
      })
      .catch(() => {})
      .finally(() => setLoaded(true));
  }, []);

  function set<K extends keyof Settings>(key: K, value: Settings[K]) {
    setSettings((prev) => ({ ...prev, [key]: value }));
  }

  async function handleSave(e: FormEvent) {
    e.preventDefault();
    setSaveStatus("saving");
    try {
      const res = await fetch("/api/admin/telegram-bot", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(settings),
      });
      const data = (await res.json()) as { ok?: boolean; error?: string };
      setSaveStatus(data.ok ? "ok" : (data.error ?? "Ошибка"));
      if (data.ok) setTimeout(() => setSaveStatus("idle"), 3000);
    } catch (err) {
      setSaveStatus(String(err));
    }
  }

  async function handleTest() {
    setTestStatus("sending");
    try {
      const res = await fetch("/api/admin/telegram-bot", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ settings }),
      });
      const data = (await res.json()) as { ok?: boolean; error?: string };
      setTestStatus(data.ok ? "ok" : (data.error ?? "Ошибка"));
      if (data.ok) setTimeout(() => setTestStatus("idle"), 5000);
    } catch (err) {
      setTestStatus(String(err));
    }
  }

  return (
    <form className="admin-form" onSubmit={handleSave} style={{ maxWidth: 480, marginTop: 36 }}>
      <h2>Уведомления в Telegram</h2>
      <p className="admin-hint" style={{ margin: 0 }}>
        Новые заявки с формы расчёта будут автоматически отправляться в ваш чат с ботом.
      </p>

      <label className="admin-label admin-checkbox">
        <input
          type="checkbox"
          checked={settings.enabled}
          onChange={(e) => set("enabled", e.target.checked)}
        />
        <span>Отправлять заявки в Telegram</span>
      </label>

      <label className="admin-label">
        <span>Токен бота (от @BotFather)</span>
        <input
          type="password"
          value={settings.token}
          onChange={(e) => set("token", e.target.value)}
          autoComplete="off"
          placeholder="1234567890:AAExampleToken"
        />
        <small>Создайте бота у @BotFather в Telegram и скопируйте его токен сюда</small>
      </label>

      <label className="admin-label">
        <span>chat_id — кому отправлять заявки</span>
        <input
          type="text"
          value={settings.chatId}
          onChange={(e) => set("chatId", e.target.value)}
          placeholder="Числовой ID или @username канала"
        />
        <small>
          Напишите боту любое сообщение, затем откройте https://api.telegram.org/bot&lt;токен&gt;/getUpdates —
          ID чата будет в поле chat.id. Либо укажите @username вашего канала.
        </small>
      </label>

      <StatusText status={saveStatus} okText="✓ Настройки сохранены" />
      <StatusText status={testStatus} okText="✓ Тестовое сообщение отправлено — проверьте чат с ботом" />

      <div className="admin-form-actions">
        <button type="submit" className="primary-button" disabled={!loaded || saveStatus === "saving"}>
          {saveStatus === "saving" ? "Сохранение…" : "Сохранить настройки"} <span>✓</span>
        </button>
        <button
          type="button"
          className="admin-action-btn"
          onClick={handleTest}
          disabled={testStatus === "sending"}
        >
          {testStatus === "sending" ? "Отправка…" : "Отправить тестовое сообщение"}
        </button>
      </div>

      <p className="admin-hint" style={{ margin: 0 }}>
        Совет: отправьте тестовое сообщение сразу после сохранения, чтобы проверить, что бот и
        получатель настроены верно.
      </p>
    </form>
  );
}
