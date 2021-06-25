import React from "react";
import Client from "./Client";

const lista = [
  { id: 1, name: "Aderaldo" },
  { id: 2, name: "Dindo" },
  { id: 3, name: "Fuba" },
  { id: 4, name: "Beca" },
  { id: 5, name: "Atila" },
  { id: 6, name: "Apolo" },
  { id: 7, name: "Fubicas" },
  { id: 8, name: "Rafinha" },
  { id: 9, name: "Gijo" },
];

export default function Clients() {
  return (
    <div className="clients-list-container">
      {lista.map((client) => (
        <Client {...client} />
      ))}
    </div>
  );
}
