console.log(data);

const sample = { id_user: 12, title: "Caso mediano" };

//GET
async function get_teste() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/12",
    {
      method: "GET",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    }
  );

  const res = await grab.json();

  console.log(res);
}
//get_teste();

//DELETE
async function delete_teste() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/4",
    {
      method: "DELETE",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    }
  );

  const res = await grab.json();

  console.log(res);
}
//delete_teste();

//UPDATE
async function update_teste() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/1",
    {
      method: "PUT",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
      body: JSON.stringify(sample),
    }
  );

  const res = await grab.json();

  console.log(res);
}
//update_teste();

//POST
async function teste() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/12",
    {
      method: "POST",
      headers: {
        "X-WP-Nonce": data.nonce,
        "Content-type": "application/json",
      },
      body: JSON.stringify(sample),
    }
  );

  const res = await grab.json();

  console.log(res);
}
//teste();

//get_teste();

//POST
async function toggle() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases-status/1",
    {
      method: "POST",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    }
  );

  const res = await grab.json();

  console.log(res);
}
//toggle();
