let lessen = [];

// Lessen laden
async function laadLessen() {
  try {
    const res = await fetch("get_lessen.php");
    if (!res.ok) throw new Error("Server fout");

    lessen = await res.json();
    update();
  } catch {
    const empty = document.getElementById("emptyState");
    if (empty) {
      empty.style.display = "block";
      empty.textContent = "Overzicht kon niet geladen worden.";
    }
  }
}

// Filter functie
const filterLessen = () => {
  const zoek = document.getElementById("search")?.value.toLowerCase() || "";
  const status = document.getElementById("statusFilter")?.value || "";

  return lessen.filter(
    (l) =>
      (!zoek || l.Naam.toLowerCase().includes(zoek)) &&
      (!status || l.Beschikbaarheid === status),
  );
};

// Status class
const statusClass = (status) =>
  "status-" + status.toLowerCase().replace(/\s+/g, "-");

// Render functies
const renderTabel = (lijst) => {
  const body = document.getElementById("lessenBody");
  if (!body) return;
  body.innerHTML = "";
  lijst.forEach((l) => {
    body.innerHTML += `
      <tr>
        <td>${l.Naam}</td>
        <td>${l.Datum}</td>
        <td>${l.Tijd}</td>
        <td>€${l.Prijs}</td>
        <td><span class="status ${statusClass(l.Beschikbaarheid)}">${l.Beschikbaarheid}</span></td>
      </tr>
    `;
  });
};

const renderCards = (lijst) => {
  const container = document.getElementById("cardContainer");
  if (!container) return;
  container.innerHTML = "";
  lijst.forEach((l) => {
    container.innerHTML += `
      <div class="les-card">
        <div class="les-card-header">
          <div>
            <div class="les-card-title">${l.Naam}</div>
            <div class="les-card-sub">€${l.Prijs}</div>
          </div>
          <span class="status ${statusClass(l.Beschikbaarheid)}">${l.Beschikbaarheid}</span>
        </div>
        <div class="les-card-grid">
          <div class="les-card-field"><label>Datum</label><span>${l.Datum}</span></div>
          <div class="les-card-field"><label>Tijd</label><span>${l.Tijd}</span></div>
        </div>
      </div>
    `;
  });
};

// Update alles
const update = () => {
  const filtered = filterLessen();
  document.getElementById("countLine")?.textContent =
    `${filtered.length} van ${lessen.length} lessen zichtbaar`;
  const empty = document.getElementById("emptyState");
  if (empty) empty.style.display = filtered.length ? "none" : "block";

  renderTabel(filtered);
  renderCards(filtered);
};

// Event listeners
["search", "statusFilter"].forEach((id) => {
  document
    .getElementById(id)
    ?.addEventListener(id === "search" ? "input" : "change", update);
});

// Menu
const toggleMenu = (show) => {
  document.getElementById("navbar")?.classList.toggle("active", show);
  document.getElementById("overlay") &&
    (document.getElementById("overlay").style.display = show
      ? "block"
      : "none");
};

document
  .getElementById("hamburger")
  ?.addEventListener("click", () => toggleMenu(true));
["closeMenu", "overlay"].forEach((id) =>
  document
    .getElementById(id)
    ?.addEventListener("click", () => toggleMenu(false)),
);

// Init
laadLessen();
