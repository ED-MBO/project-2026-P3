// Lokale cache van alle lessen opgehaald van de server
let lessen = [];

// Data ophalen

async function laadLessen() {
  try {
    const response = await fetch("get_lessen.php");
    if (!response.ok) throw new Error("Server fout");

    lessen = await response.json();
    update();
  } catch {
    const emptyState = document.getElementById("emptyState");
    emptyState.style.display = "block";
    emptyState.textContent = "Overzicht kon niet geladen worden.";
  }
}

// Filtert lessen op zoekterm en statusfilter
function filterLessen() {
  const zoek = document.getElementById("search").value.toLowerCase();
  const status = document.getElementById("statusFilter").value;

  return lessen.filter(
    (l) =>
      (!zoek || l.Naam.toLowerCase().includes(zoek)) &&
      (!status || l.Beschikbaarheid === status),
  );
}

// Zet een statuswaarde om naar een CSS class "Niet beschikbaar" = "status-niet-beschikbaar"
function statusClass(status) {
  return "status-" + status.toLowerCase().replace(/\s+/g, "-");
}

// Render functies
function renderTabel(lijst) {
  const body = document.getElementById("lessenBody");
  body.innerHTML = "";

  lijst.forEach((les) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${les.Naam}</td>
      <td>${les.Datum}</td>
      <td>${les.Tijd}</td>
      <td>€${les.Prijs}</td>
      <td><span class="status ${statusClass(les.Beschikbaarheid)}">${les.Beschikbaarheid}</span></td>
    `;
    body.appendChild(row);
  });
}

// Vult de kaartweergave met de gefilterde lessen mobiel versie
function renderCards(lijst) {
  const container = document.getElementById("cardContainer");
  container.innerHTML = "";

  lijst.forEach((les) => {
    const card = document.createElement("div");
    card.className = "les-card";
    card.innerHTML = `
      <div class="les-card-header">
        <div>
          <div class="les-card-title">${les.Naam}</div>
          <div class="les-card-sub">€${les.Prijs}</div>
        </div>
        <span class="status ${statusClass(les.Beschikbaarheid)}">${les.Beschikbaarheid}</span>
      </div>
      <div class="les-card-grid">
        <div class="les-card-field">
          <label>Datum</label>
          <span>${les.Datum}</span>
        </div>
        <div class="les-card-field">
          <label>Tijd</label>
          <span>${les.Tijd}</span>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

// Hoofd update
function update() {
  const filtered = filterLessen();

  document.getElementById("countLine").textContent =
    `${filtered.length} van ${lessen.length} lessen zichtbaar`;

  const emptyState = document.getElementById("emptyState");
  emptyState.style.display = filtered.length ? "none" : "block";

  renderTabel(filtered);
  renderCards(filtered);
}

// Event listeners
document.getElementById("search").addEventListener("input", update);
document.getElementById("statusFilter").addEventListener("change", update);

// Navigatiemenu openen en sluiten (hamburger)
const navbar = document.getElementById("navbar");
const overlay = document.getElementById("overlay");

function openMenu() {
  navbar.classList.add("active");
  overlay.style.display = "block";
}

function closeMenu() {
  navbar.classList.remove("active");
  overlay.style.display = "none";
}

document.getElementById("hamburger").addEventListener("click", openMenu);
document.getElementById("closeMenu").addEventListener("click", closeMenu);
overlay.addEventListener("click", closeMenu);

laadLessen();
