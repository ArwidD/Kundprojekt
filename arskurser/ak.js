document.addEventListener("DOMContentLoaded", () => {
  const arskursId = document.body.dataset.arskurs;

  fetch("ak.php")
    .then(response => response.json())
    .then(data => {
      if (!data || data.length === 0) return;

      const filtered = data.filter(item => item.arskurs_id == arskursId);

      if (filtered.length === 0) {
        document.querySelectorAll("[id^='info-container-']").forEach(c => {
          if (c) c.innerHTML = "<li>Inga resultat för denna årskurs.</li>";
        });
        return;
      }

      filtered.forEach(item => {
        const container = document.getElementById(`info-container-${item.kategori}`);
        if (container) {   // 👈 Viktig check
          const li = document.createElement("li");
          li.textContent = item.information;
          container.appendChild(li);
        }
      });
    })
    .catch(error => console.error("Fel vid hämtning:", error));
});
