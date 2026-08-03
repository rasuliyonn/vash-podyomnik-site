"use client";

import { useState, useEffect, useRef } from "react";

interface WorkImage {
  id: number;
  url: string;
  caption: string;
  sortOrder: number;
}

async function resizeImage(file: File): Promise<string> {
  return new Promise((resolve) => {
    const img = new Image();
    const objectUrl = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(objectUrl);
      const maxW = 1200;
      const scale = img.width > maxW ? maxW / img.width : 1;
      const canvas = document.createElement("canvas");
      canvas.width = Math.round(img.width * scale);
      canvas.height = Math.round(img.height * scale);
      const ctx = canvas.getContext("2d")!;
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      resolve(canvas.toDataURL("image/jpeg", 0.8));
    };
    img.src = objectUrl;
  });
}

export default function AdminWorksPage() {
  const [works, setWorks] = useState<WorkImage[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // URL-based add
  const [newUrl, setNewUrl] = useState("");
  const [newCaption, setNewCaption] = useState("");
  const [adding, setAdding] = useState(false);
  const [addError, setAddError] = useState("");

  // Static import
  const [importing, setImporting] = useState(false);
  const [importMsg, setImportMsg] = useState("");

  // File upload add
  const [uploadPreview, setUploadPreview] = useState<string | null>(null);
  const [uploadCaption, setUploadCaption] = useState("");
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState("");
  const fileInputRef = useRef<HTMLInputElement>(null);

  async function load() {
    setLoading(true);
    setError("");
    try {
      const res = await fetch("/api/admin/works");
      if (!res.ok) throw new Error(await res.text());
      const data = (await res.json()) as { works: WorkImage[] };
      setWorks(data.works);
    } catch (e) {
      setError(String(e));
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  async function importStatic() {
    if (!confirm("Импортировать 71 статическое фото из public/works/ в базу данных? Уже добавленные будут пропущены.")) return;
    setImporting(true);
    setImportMsg("");
    try {
      const res = await fetch("/api/admin/works", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "import-static", total: 71 }),
      });
      const data = (await res.json()) as { inserted?: number; message?: string; error?: string };
      if (!res.ok) throw new Error(data.error ?? "Ошибка");
      setImportMsg(
        data.inserted === 0
          ? (data.message ?? "Все уже импортированы")
          : `Импортировано ${data.inserted} фото`
      );
      await load();
    } catch (e) {
      setImportMsg("Ошибка: " + String(e));
    } finally {
      setImporting(false);
    }
  }

  async function handleFileSelect(file: File) {
    const dataUrl = await resizeImage(file);
    setUploadPreview(dataUrl);
    setUploadError("");
  }

  async function uploadFile() {
    if (!uploadPreview) return;
    setUploading(true);
    setUploadError("");
    try {
      const res = await fetch("/api/admin/works", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          imageData: uploadPreview,
          caption: uploadCaption.trim(),
          sortOrder: works.length,
        }),
      });
      if (!res.ok) {
        const d = (await res.json()) as { error: string };
        throw new Error(d.error);
      }
      setUploadPreview(null);
      setUploadCaption("");
      if (fileInputRef.current) fileInputRef.current.value = "";
      await load();
    } catch (e) {
      setUploadError(String(e));
    } finally {
      setUploading(false);
    }
  }

  async function addWork() {
    if (!newUrl.trim()) {
      setAddError("URL обязателен");
      return;
    }
    setAdding(true);
    setAddError("");
    try {
      const res = await fetch("/api/admin/works", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          url: newUrl.trim(),
          caption: newCaption.trim(),
          sortOrder: works.length,
        }),
      });
      if (!res.ok) {
        const d = (await res.json()) as { error: string };
        throw new Error(d.error);
      }
      setNewUrl("");
      setNewCaption("");
      await load();
    } catch (e) {
      setAddError(String(e));
    } finally {
      setAdding(false);
    }
  }

  async function deleteWork(id: number) {
    if (!confirm("Удалить изображение?")) return;
    try {
      await fetch(`/api/admin/works/${id}`, { method: "DELETE" });
      await load();
    } catch (e) {
      setError(String(e));
    }
  }

  async function updateOrder(id: number, direction: -1 | 1) {
    const idx = works.findIndex((w) => w.id === id);
    if (idx === -1) return;
    const swapIdx = idx + direction;
    if (swapIdx < 0 || swapIdx >= works.length) return;

    const a = works[idx];
    const b = works[swapIdx];

    try {
      await Promise.all([
        fetch(`/api/admin/works/${a.id}`, {
          method: "PATCH",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ sortOrder: b.sortOrder }),
        }),
        fetch(`/api/admin/works/${b.id}`, {
          method: "PATCH",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ sortOrder: a.sortOrder }),
        }),
      ]);
      await load();
    } catch (e) {
      setError(String(e));
    }
  }

  return (
    <div className="admin-page">
      <header className="admin-header">
        <div className="brand">
          <img className="brand-logo" src="/vash-podyomnik-logo.jpg" alt="Логотип" />
          <span>
            <b>ВАШ ПОДЪЁМНИК</b>
            <small>Административная панель</small>
          </span>
        </div>
        <a href="/admin/logout" className="admin-logout-btn">Выйти</a>
      </header>

      <nav className="admin-nav">
        <a href="/admin">← Панель</a>
        <a href="/admin/equipment">Оборудование</a>
        <a href="/admin/works" className="active">Наши работы</a>
        <a href="/admin/submissions">Заявки</a>
        <a href="/admin/content">Контент</a>
      </nav>

      <main className="admin-main">
        <div className="admin-list-header">
          <h1>Наши работы — галерея</h1>
          <span className="admin-badge gray">{works.length} фото</span>
        </div>

        {error && (
          <div className="admin-error-box">
            <b>Ошибка:</b> {error}
          </div>
        )}

        {/* ── Импорт статики ── */}
        <div className="admin-section" style={{ background: "var(--surface-alt, #f5f5f5)", borderRadius: 8, padding: 16, marginBottom: 24 }}>
          <h2>Импорт существующих фото</h2>
          <p className="admin-hint">
            71 фото из <code>public/works/</code> можно импортировать в базу данных одной кнопкой.
            После этого ими можно управлять: удалять, менять порядок, добавлять новые.
          </p>
          <div style={{ display: "flex", gap: 12, alignItems: "center", flexWrap: "wrap" }}>
            <button
              className="primary-button"
              onClick={importStatic}
              disabled={importing}
            >
              {importing ? "Импортирую…" : "📥 Импортировать 71 фото из public/works/"} <span>→</span>
            </button>
            {importMsg && (
              <span style={{ color: importMsg.startsWith("Ошибка") ? "red" : "green" }}>
                {importMsg}
              </span>
            )}
          </div>
        </div>

        {/* ── Загрузка файла ── */}
        <div className="admin-section">
          <h2>Загрузить фото с устройства</h2>
          <p className="admin-hint">Фото сжимается до 1200px и сохраняется в базу данных.</p>

          <input
            ref={fileInputRef}
            type="file"
            accept="image/*"
            style={{ display: "none" }}
            onChange={async (e) => {
              const file = e.target.files?.[0];
              if (file) await handleFileSelect(file);
              e.target.value = "";
            }}
          />

          {uploadPreview ? (
            <div style={{ marginBottom: 12 }}>
              <img
                src={uploadPreview}
                alt="Превью"
                style={{ display: "block", maxWidth: 320, maxHeight: 220, objectFit: "cover", borderRadius: 6, marginBottom: 8 }}
              />
              <div className="admin-form-row" style={{ marginBottom: 8 }}>
                <input
                  type="text"
                  placeholder="Подпись (необязательно)"
                  value={uploadCaption}
                  onChange={(e) => setUploadCaption(e.target.value)}
                  className="admin-input"
                />
              </div>
              <div style={{ display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" }}>
                <button
                  className="primary-button"
                  onClick={uploadFile}
                  disabled={uploading}
                >
                  {uploading ? "Сохранение…" : "Сохранить в БД"} <span>✓</span>
                </button>
                <button
                  className="admin-action-btn"
                  onClick={() => {
                    setUploadPreview(null);
                    setUploadCaption("");
                    setUploadError("");
                  }}
                >
                  Отмена
                </button>
              </div>
              {uploadError && <p className="admin-field-error">{uploadError}</p>}
            </div>
          ) : (
            <button
              className="admin-action-btn"
              onClick={() => fileInputRef.current?.click()}
            >
              📎 Выбрать файл
            </button>
          )}
        </div>

        {/* ── Добавить по URL ── */}
        <div className="admin-section">
          <h2>Добавить по URL</h2>
          <p className="admin-hint">
            Загрузите файл в <code>public/works/</code> и укажите путь вида <code>/works/work-72.jpg</code>,
            или используйте внешний URL.
          </p>
          <div className="admin-form-row">
            <input
              type="text"
              placeholder="URL изображения (напр. /works/work-72.jpg)"
              value={newUrl}
              onChange={(e) => setNewUrl(e.target.value)}
              className="admin-input"
              style={{ flex: 2 }}
            />
            <input
              type="text"
              placeholder="Подпись (необязательно)"
              value={newCaption}
              onChange={(e) => setNewCaption(e.target.value)}
              className="admin-input"
              style={{ flex: 1 }}
            />
            <button className="primary-button" onClick={addWork} disabled={adding}>
              {adding ? "Добавляю..." : "+ Добавить"}
            </button>
          </div>
          {addError && <p className="admin-field-error">{addError}</p>}
        </div>

        {/* ── Список ── */}
        {loading ? (
          <p className="admin-empty">Загрузка...</p>
        ) : works.length === 0 ? (
          <div className="admin-empty">
            <p>Нет фото в базе данных.</p>
            <p>
              Пока база пуста — на сайте показываются статические файлы из{" "}
              <code>public/works/</code> (71 фото). Добавьте хотя бы одно фото сюда,
              чтобы галерея перешла на управление через базу данных.
            </p>
          </div>
        ) : (
          <div className="admin-works-grid">
            {works.map((w, idx) => (
              <div key={w.id} className="admin-work-card">
                <img src={w.url} alt={w.caption || `Работа ${idx + 1}`} />
                <div className="admin-work-meta">
                  <span className="admin-work-url" title={w.url}>
                    {w.url.startsWith("/api/works-image/") ? `📁 БД #${w.id}` : w.url}
                  </span>
                  {w.caption && <span className="admin-work-caption">{w.caption}</span>}
                </div>
                <div className="admin-work-actions">
                  <button
                    className="admin-action-btn"
                    onClick={() => updateOrder(w.id, -1)}
                    disabled={idx === 0}
                    title="Выше"
                  >
                    ↑
                  </button>
                  <button
                    className="admin-action-btn"
                    onClick={() => updateOrder(w.id, 1)}
                    disabled={idx === works.length - 1}
                    title="Ниже"
                  >
                    ↓
                  </button>
                  <button
                    className="admin-action-btn danger"
                    onClick={() => deleteWork(w.id)}
                  >
                    Удалить
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
