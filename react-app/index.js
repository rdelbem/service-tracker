import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const getUsers = async () => {
  const url = "https://aulasplugin.local/wp-json/wp/v2/users?roles=client";
  const grab = await fetch(url, {
    method: "GET",
    headers: {
      "X-WP-Nonce": data.nonce,
    },
  });

  const res = await grab.json();

  console.log(res);
};

document.addEventListener("DOMContentLoaded", () => {
  const element = document.getElementById("root");
  if (typeof element !== "undefined" && element !== null) {
    ReactDOM.render(<App />, document.getElementById("root"));
  }
});
