console.log(data);

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
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/3",
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
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/6",
    {
      method: "PUT",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    }
  );

  const res = await grab.json();

  console.log(res);
}
update_teste();

const xdx = { id_user: 12, title: "99999999" };
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
      body: JSON.stringify(xdx),
    }
  );

  const res = await grab.json();

  console.log(res);
}
