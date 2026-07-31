"use client";

import { useState } from "react";

type Row = {
  id: number;
  liftType: string;
  capacity: string;
  height: string;
  platformSize: string;
  city: string;
  stops: string;
  status: string;
  createdAt: string;
};

const STATUS_LABELS: Record<string, string> = {
  new: "Новая",
  viewed: "Просмотрена",
  done: "Обработана",
};

const STATUS_COLORS: Record<string, string> = {
  new: "red",
  viewed: "yellow",
  done: "green",
};

export function SubmissionsClient({ initialRows }: { initialRows: Row[] }) {
  const [rows, setRows] = useState(initialRows);

  async function changeStatus(id: number, status: string) {
    const res = await fetch("/api/admin/submissions", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id, status }),
    });
    if (res.ok) {
      setRows((prev) => prev.map((r) => (r.id === id ? { ...r, status } : r)));
    }
  }

  return (
    <div className="admin-table-wrap">
      <table className="admin-table submissions-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Дата</th>
            <th>Тип</th>
            <th>Груз</th>
            <th>Высота</th>
            <th>Платформа</th>
            <th>Остановки</th>
            <th>Город/объект</th>
            <th>Статус</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.id} className={`status-${row.status}`}>
              <td>{row.id}</td>
              <td>
                <time>{row.createdAt.slice(0, 16).replace("T", " ")}</time>
              </td>
              <td>{row.liftType}</td>
              <td>{row.capacity}</td>
              <td>{row.height || "—"}</td>
              <td>{row.platformSize || "—"}</td>
              <td>{row.stops}</td>
              <td>{row.city || "—"}</td>
              <td>
                <select
                  value={row.status}
                  onChange={(e) => changeStatus(row.id, e.target.value)}
                  className={`status-select ${STATUS_COLORS[row.status] ?? ""}`}
                >
                  {Object.entries(STATUS_LABELS).map(([val, label]) => (
                    <option key={val} value={val}>{label}</option>
                  ))}
                </select>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
