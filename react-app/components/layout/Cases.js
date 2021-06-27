import React from "react";
import Case from "./Case";

//exemplo
const cases = [
  {
    id: 1,
    id_user: 12,
    status: "open",
    created_at: 1232,
    title: "caso complexo",
  },
  {
    id: 2,
    id_user: 12,
    status: "close",
    created_at: 1235,
    title: "caso mediano",
  },
  { id: 3, id_user: 12, status: "open", created_at: 1236, title: "caso fácil" },
  {
    id: 4,
    id_user: 12,
    status: "open",
    created_at: 12310,
    title: "caso impossível",
  },
];

export default function Cases() {
  return (
    <div className="cases-container">
      {cases.map((item) => (
        <Case {...item} />
      ))}
    </div>
  );
}
