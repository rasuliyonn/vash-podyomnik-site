"use client";

export function DeleteEquipmentButton({ equipmentId }: { equipmentId: number }) {
  async function handleDelete() {
    if (!confirm("Удалить этот тип оборудования? Это действие нельзя отменить.")) return;
    await fetch(`/api/admin/equipment/${equipmentId}`, { method: "DELETE" });
    window.location.href = "/admin/equipment";
  }

  return (
    <button type="button" className="admin-action-btn danger" onClick={handleDelete}>
      Удалить
    </button>
  );
}
