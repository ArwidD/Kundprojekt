document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("info-container");

  // Läs vilken årskurs-ID som ska visas (från <body data-arskurs="1"> i HTML)
  const arskursId = document.body.dataset.arskurs;

  fetch("ak.php")
    .then(response => response.json())
    .then(data => {
      if (!data || data.length === 0) {
        container.innerHTML = "<p>Inga resultat.</p>";
        return;
      }

      // Filtrera ut endast poster som matchar årskursen
      const filtered = data.filter(item => item["arskurs ID"] == arskursId);

      if (filtered.length === 0) {
        container.innerHTML = "<p>Inga resultat för denna årskurs.</p>";
        return;
      }

      let html = "<ul>";
      filtered.forEach(item => {
        html += `<li>ID: ${item.ID} – Info: ${item.information}</li>`;
      });
      html += "</ul>";

      container.innerHTML = html;
    })
    .catch(error => {
      console.error("Fel vid hämtning:", error);
      container.innerHTML = "<p>Ett fel uppstod.</p>";
    });
});
