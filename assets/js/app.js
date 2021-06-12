console.log(data.nonce);

(async function test() {
  const grab = await fetch(
    "https://aulasplugin.local/wp-json/service-tracker/v1/cases/12",
    {
      method: "POST",
      headers: {
        "X-WP-Nonce": data.nonce,
      },
    }
  );

  const res = await grab.json();

  console.log(res);
})();
