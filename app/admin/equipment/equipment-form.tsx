"use client";

import { FormEvent, useState } from "react";

type Spec = { name: string; value: string };
type ImageRow = { id: number; url: string; caption: string; imageType: string; sortOrder: number };
type DocRow = { id: number; url: string; name: string; docType: string; sizeKb: number | null };

type Props = {
  equipmentId?: number;
  initial?: {
    slug?: string;
    title?: string;
    description?: string;
    features?: string[];
    advantages?: string[];
    applications?: string[];
    techSpecs?: Spec[];
    videoUrl?: string;
    sortOrder?: number;
    visible?: boolean;
  };
  initialImages?: ImageRow[];
  initialDocuments?: DocRow[];
};

function listToArray(val: string): string[] {
  return val
    .split("\n")
    .map((s) => s.trim())
    .filter(Boolean);
}

function arrayToList(arr: string[]): string {
  return arr.join("\n");
}

const IMAGE_TYPES = [
  { value: "gallery", label: "Галерея" },
  { value: "installation", label: "Монтаж" },
  { value: "project", label: "Объект" },
];

const DOC_TYPES = [
  { value: "pdf", label: "PDF" },
  { value: "certificate", label: "Сертификат" },
  { value: "instruction", label: "Инструкция" },
  { value: "scheme", label: "Схема" },
  { value: "video", label: "Видео" },
];

export function EquipmentForm({ equipmentId, initial = {}, initialImages = [], initialDocuments = [] }: Props) {
  const isEdit = !!equipmentId;

  // ── main form state ──
  const [slug, setSlug] = useState(initial.slug ?? "");
  const [title, setTitle] = useState(initial.title ?? "");
  const [description, setDescription] = useState(initial.description ?? "");
  const [features, setFeatures] = useState(arrayToList(initial.features ?? []));
  const [advantages, setAdvantages] = useState(arrayToList(initial.advantages ?? []));
  const [applications, setApplications] = useState(arrayToList(initial.applications ?? []));
  const [techSpecs, setTechSpecs] = useState<Spec[]>(
    initial.techSpecs?.length ? initial.techSpecs : [{ name: "", value: "" }],
  );
  const [videoUrl, setVideoUrl] = useState(initial.videoUrl ?? "");
  const [sortOrder, setSortOrder] = useState(initial.sortOrder ?? 0);
  const [visible, setVisible] = useState(initial.visible ?? true);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState("");

  // ── image state ──
  const [images, setImages] = useState<ImageRow[]>(initialImages);
  const [newImageUrl, setNewImageUrl] = useState("");
  const [newImageCaption, setNewImageCaption] = useState("");
  const [newImageType, setNewImageType] = useState("gallery");
  const [addingImage, setAddingImage] = useState(false);

  // ── document state ──
  const [documents, setDocuments] = useState<DocRow[]>(initialDocuments);
  const [newDocUrl, setNewDocUrl] = useState("");
  const [newDocName, setNewDocName] = useState("");
  const [newDocType, setNewDocType] = useState("pdf");
  const [addingDoc, setAddingDoc] = useState(false);

  // ── tech spec helpers ──
  function addSpec() {
    setTechSpecs((prev) => [...prev, { name: "", value: "" }]);
  }
  function updateSpec(i: number, field: keyof Spec, val: string) {
    setTechSpecs((prev) => prev.map((s, idx) => (idx === i ? { ...s, [field]: val } : s)));
  }
  function removeSpec(i: number) {
    setTechSpecs((prev) => prev.filter((_, idx) => idx !== i));
  }

  // ── save main form ──
  async function handleSave(e: FormEvent) {
    e.preventDefault();
    setSaveError("");
    setSaving(true);
    try {
      const body = {
        slug: slug.trim(),
        title: title.trim(),
        description: description.trim(),
        featuresJson: JSON.stringify(listToArray(features)),
        advantagesJson: JSON.stringify(listToArray(advantages)),
        applicationsJson: JSON.stringify(listToArray(applications)),
        techSpecsJson: JSON.stringify(techSpecs.filter((s) => s.name && s.value)),
        videoUrl: videoUrl.trim() || null,
        sortOrder,
        visible,
      };

      const url = isEdit
        ? `/api/admin/equipment/${equipmentId}`
        : "/api/admin/equipment";
      const method = isEdit ? "PUT" : "POST";

      const res = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });

      if (!res.ok) {
        const data = (await res.json()) as { error?: string };
        setSaveError(data.error ?? "Ошибка сохранения");
        return;
      }

      if (!isEdit) {
        const data = (await res.json()) as { equipment?: { id: number } };
        window.location.href = `/admin/equipment/${data.equipment?.id}`;
      } else {
        window.location.reload();
      }
    } catch (err) {
      setSaveError(String(err));
    } finally {
      setSaving(false);
    }
  }

  // ── add image ──
  async function handleAddImage() {
    if (!newImageUrl.trim() || !equipmentId) return;
    setAddingImage(true);
    try {
      const res = await fetch(`/api/admin/equipment/${equipmentId}/images`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          url: newImageUrl.trim(),
          caption: newImageCaption.trim(),
          imageType: newImageType,
          sortOrder: images.length,
        }),
      });
      if (res.ok) {
        const data = (await res.json()) as { image: ImageRow };
        setImages((prev) => [...prev, data.image]);
        setNewImageUrl("");
        setNewImageCaption("");
        setNewImageType("gallery");
      }
    } finally {
      setAddingImage(false);
    }
  }

  // ── delete image ──
  async function handleDeleteImage(imageId: number) {
    if (!equipmentId) return;
    await fetch(`/api/admin/equipment/${equipmentId}/images`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ imageId }),
    });
    setImages((prev) => prev.filter((i) => i.id !== imageId));
  }

  // ── add document ──
  async function handleAddDoc() {
    if (!newDocUrl.trim() || !newDocName.trim() || !equipmentId) return;
    setAddingDoc(true);
    try {
      const res = await fetch(`/api/admin/equipment/${equipmentId}/documents`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          url: newDocUrl.trim(),
          name: newDocName.trim(),
          docType: newDocType,
        }),
      });
      if (res.ok) {
        const data = (await res.json()) as { document: DocRow };
        setDocuments((prev) => [...prev, data.document]);
        setNewDocUrl("");
        setNewDocName("");
        setNewDocType("pdf");
      }
    } finally {
      setAddingDoc(false);
    }
  }

  // ── client-side image resize/compress ──
  async function handleImageFile(file: File): Promise<string> {
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

  // ── delete document ──
  async function handleDeleteDoc(documentId: number) {
    if (!equipmentId) return;
    await fetch(`/api/admin/equipment/${equipmentId}/documents`, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ documentId }),
    });
    setDocuments((prev) => prev.filter((d) => d.id !== documentId));
  }

  return (
    <div className="admin-editor">
      {/* ── main form ── */}
      <form onSubmit={handleSave} className="admin-form">
        <h2>Основная информация</h2>

        <div className="admin-form-row">
          <label className="admin-label">
            <span>Slug (URL)</span>
            <input
              value={slug}
              onChange={(e) => setSlug(e.target.value)}
              placeholder="shaftnye"
              required
              pattern="[a-z0-9-]+"
              title="Только строчные латинские буквы, цифры и дефис"
              disabled={isEdit}
            />
            <small>Используется в URL: /equipment/slug</small>
          </label>
          <label className="admin-label">
            <span>Порядок сортировки</span>
            <input
              type="number"
              value={sortOrder}
              onChange={(e) => setSortOrder(parseInt(e.target.value, 10))}
            />
          </label>
        </div>

        <label className="admin-label">
          <span>Название</span>
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="Шахтные подъёмники"
            required
          />
        </label>

        <label className="admin-label">
          <span>Описание</span>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={4}
            placeholder="Текст описания оборудования…"
          />
        </label>

        <div className="admin-form-row">
          <label className="admin-label">
            <span>Особенности (по одной на строку)</span>
            <textarea
              value={features}
              onChange={(e) => setFeatures(e.target.value)}
              rows={5}
              placeholder="Особенность 1&#10;Особенность 2"
            />
          </label>
          <label className="admin-label">
            <span>Преимущества (по одной на строку)</span>
            <textarea
              value={advantages}
              onChange={(e) => setAdvantages(e.target.value)}
              rows={5}
              placeholder="Преимущество 1&#10;Преимущество 2"
            />
          </label>
        </div>

        <label className="admin-label">
          <span>Сферы применения (по одной на строку)</span>
          <textarea
            value={applications}
            onChange={(e) => setApplications(e.target.value)}
            rows={4}
            placeholder="Склады&#10;Производства"
          />
        </label>

        {/* tech specs */}
        <div className="admin-specs">
          <div className="admin-specs-header">
            <span>Технические характеристики</span>
            <button type="button" className="admin-action-btn" onClick={addSpec}>
              + Добавить
            </button>
          </div>
          {techSpecs.map((spec, i) => (
            <div key={i} className="admin-spec-row">
              <input
                value={spec.name}
                onChange={(e) => updateSpec(i, "name", e.target.value)}
                placeholder="Параметр"
              />
              <input
                value={spec.value}
                onChange={(e) => updateSpec(i, "value", e.target.value)}
                placeholder="Значение"
              />
              <button
                type="button"
                className="admin-action-btn danger"
                onClick={() => removeSpec(i)}
              >
                ×
              </button>
            </div>
          ))}
        </div>

        <label className="admin-label">
          <span>URL видео (YouTube embed или другой iframe src)</span>
          <input
            value={videoUrl}
            onChange={(e) => setVideoUrl(e.target.value)}
            placeholder="https://www.youtube.com/embed/…"
          />
        </label>

        <label className="admin-label admin-checkbox">
          <input
            type="checkbox"
            checked={visible}
            onChange={(e) => setVisible(e.target.checked)}
          />
          <span>Опубликован (виден на сайте)</span>
        </label>

        {saveError && <p className="admin-error">{saveError}</p>}

        <div className="admin-form-actions">
          <button type="submit" className="primary-button" disabled={saving}>
            {saving ? "Сохранение…" : isEdit ? "Сохранить" : "Создать"}
            <span aria-hidden="true">✓</span>
          </button>
          <a href="/admin/equipment" className="admin-action-btn secondary">
            Отмена
          </a>
        </div>
      </form>

      {/* ── images (only for existing equipment) ── */}
      {isEdit && (
        <section className="admin-media-section">
          <h2>Фотографии</h2>

          <div className="admin-image-grid">
            {images.map((img) => (
              <div key={img.id} className="admin-image-card">
                <img src={img.url} alt={img.caption} />
                <div className="admin-image-info">
                  <span className="admin-badge">{IMAGE_TYPES.find((t) => t.value === img.imageType)?.label ?? img.imageType}</span>
                  {img.caption && <p>{img.caption}</p>}
                </div>
                <button
                  type="button"
                  className="admin-image-delete"
                  onClick={() => handleDeleteImage(img.id)}
                  aria-label="Удалить фото"
                >
                  ×
                </button>
              </div>
            ))}
          </div>

          <div className="admin-add-media">
            <h3>Добавить фото</h3>
            <div className="admin-form-row">
              <label className="admin-label">
                <span>URL изображения</span>
                <input
                  value={newImageUrl}
                  onChange={(e) => setNewImageUrl(e.target.value)}
                  placeholder="https://…/photo.jpg"
                />
                <input
                  type="file"
                  accept="image/*"
                  style={{ display: "none" }}
                  id="img-file-input"
                  onChange={async (e) => {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    const dataUrl = await handleImageFile(file);
                    setNewImageUrl(dataUrl);
                    e.target.value = "";
                  }}
                />
                <button
                  type="button"
                  className="admin-action-btn"
                  onClick={() => document.getElementById("img-file-input")?.click()}
                >
                  📎 Загрузить фото
                </button>
              </label>
              <label className="admin-label">
                <span>Тип</span>
                <select value={newImageType} onChange={(e) => setNewImageType(e.target.value)}>
                  {IMAGE_TYPES.map((t) => (
                    <option key={t.value} value={t.value}>{t.label}</option>
                  ))}
                </select>
              </label>
            </div>
            <label className="admin-label">
              <span>Подпись (необязательно)</span>
              <input
                value={newImageCaption}
                onChange={(e) => setNewImageCaption(e.target.value)}
                placeholder="Описание фото"
              />
            </label>
            <button
              type="button"
              className="primary-button"
              onClick={handleAddImage}
              disabled={addingImage || !newImageUrl.trim()}
            >
              {addingImage ? "Добавляем…" : "Добавить фото"}
            </button>
          </div>
        </section>
      )}

      {/* ── documents (only for existing equipment) ── */}
      {isEdit && (
        <section className="admin-media-section">
          <h2>Документы и файлы</h2>

          {documents.length > 0 && (
            <ul className="admin-doc-list">
              {documents.map((doc) => (
                <li key={doc.id}>
                  <span>{DOC_TYPES.find((t) => t.value === doc.docType)?.label ?? doc.docType}</span>
                  <a href={doc.url} target="_blank" rel="noopener">{doc.name}</a>
                  {doc.sizeKb && <small>{doc.sizeKb} КБ</small>}
                  <button
                    type="button"
                    className="admin-action-btn danger"
                    onClick={() => handleDeleteDoc(doc.id)}
                  >
                    Удалить
                  </button>
                </li>
              ))}
            </ul>
          )}

          <div className="admin-add-media">
            <h3>Добавить документ</h3>
            <div className="admin-form-row">
              <label className="admin-label">
                <span>URL файла</span>
                <input
                  value={newDocUrl}
                  onChange={(e) => setNewDocUrl(e.target.value)}
                  placeholder="https://…/document.pdf"
                />
              </label>
              <label className="admin-label">
                <span>Тип</span>
                <select value={newDocType} onChange={(e) => setNewDocType(e.target.value)}>
                  {DOC_TYPES.map((t) => (
                    <option key={t.value} value={t.value}>{t.label}</option>
                  ))}
                </select>
              </label>
            </div>
            <label className="admin-label">
              <span>Название документа</span>
              <input
                value={newDocName}
                onChange={(e) => setNewDocName(e.target.value)}
                placeholder="Паспорт изделия"
              />
            </label>
            <button
              type="button"
              className="primary-button"
              onClick={handleAddDoc}
              disabled={addingDoc || !newDocUrl.trim() || !newDocName.trim()}
            >
              {addingDoc ? "Добавляем…" : "Добавить документ"}
            </button>
          </div>
        </section>
      )}
    </div>
  );
}
