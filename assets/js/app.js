console.log(data.nonce);

const xdx = { id_user: 12, title: "99999999" };
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
