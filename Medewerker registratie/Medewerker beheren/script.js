/* Hamburger menu */

const hamburgerButton = document.querySelector(".hamburger");
const navigatieMenu = document.querySelector(".navbar");
const sluitKnop = document.querySelector(".close-menu");
const overlayElement = document.querySelector(".overlay");

function openNavigatie() {
  navigatieMenu.classList.add("active");
  overlayElement.style.display = "block";
  document.body.style.overflow = "hidden";
}

function sluitNavigatie() {
  navigatieMenu.classList.remove("active");
  overlayElement.style.display = "none";
  document.body.style.overflow = "auto";
}

hamburgerButton.addEventListener("click", openNavigatie);
sluitKnop.addEventListener("click", sluitNavigatie);
overlayElement.addEventListener("click", sluitNavigatie);

/* Data uit de Database */
let medewerkers = [];

/* Haal data uit de php */
async function laadMedewerkers() {
  try {
    const response = await fetch("get_medewerkers.php");
    if (!response.ok) {
      throw new Error("Server gaf foutmelding");
    }
    medewerkers = await response.json();
    vulAfdelingen();
    update();
  } catch (error) {
    console.error("Fout bij laden medewerkers:", error);
    emptyState.style.display = "block";
    emptyState.textContent = "Database verbinding mislukt.";
  }
}

/* Elementen */

const zoekInput = document.getElementById("search");
const afdelingSelect = document.getElementById("afdeling");
const statusSelect = document.getElementById("status");
const tabelBody = document.getElementById("body");
const cardContainer = document.getElementById("cardContainer");
const emptyState = document.getElementById("emptyState");
const countLine = document.getElementById("countLine");

function vulAfdelingen() {
  afdelingSelect.innerHTML = '<option value="">Alle afdelingen</option>';
  const uniek = [...new Set(medewerkers.map((m) => m.afdeling))];
  uniek.forEach((a) => {
    const option = document.createElement("option");
    option.value = a;
    option.textContent = a;
    afdelingSelect.appendChild(option);
  });
}

/* Filteren */
function filterMedewerkers() {
  const zoek = zoekInput.value.toLowerCase();
  const afd = afdelingSelect.value;
  const stat = statusSelect.value;
  return medewerkers.filter(
    (m) =>
      (!zoek ||
        m.naam.toLowerCase().includes(zoek) ||
        m.functie.toLowerCase().includes(zoek)) &&
      (!afd || m.afdeling === afd) &&
      (!stat || m.status === stat),
  );
}

/* Tablen renderen */

function renderTabel(lijst) {
  tabelBody.innerHTML = "";

  if (!lijst.length) {
    emptyState.style.display = "block";
    return;
  }
  emptyState.style.display = "none";
  lijst.forEach((m) => {
    const row = document.createElement("tr");
    const statusClass = "status-" + m.status.toLowerCase().replace(/\s/g, "");
    row.innerHTML = `
      <td>${m.naam}</td>
      <td>${m.functie}</td>
      <td><span class="badge">${m.afdeling}</span></td>
      <td><span class="status ${statusClass}">${m.status}</span></td>
    `;
    tabelBody.appendChild(row);
  });
}

/* cards renderen */
function renderCards(lijst) {
  cardContainer.innerHTML = "";
  lijst.forEach((m) => {
    const card = document.createElement("div");
    card.classList.add("team-card");
    const statusClass = "status-" + m.status.toLowerCase().replace(/\s/g, "");
    card.innerHTML = `
      <h3>${m.naam}</h3>
      <div class="functie">${m.functie}</div>
      <div class="card-row">
        <span class="card-label">Afdeling</span>
        <span class="badge">${m.afdeling}</span>
      </div>
      <div class="card-row">
        <span class="card-label">Status</span>
        <span class="status ${statusClass}">${m.status}</span>
      </div>
    `;

    cardContainer.appendChild(card);
  });
}

// Update scherm
function update() {
  const filtered = filterMedewerkers();
  countLine.textContent = `${filtered.length} van ${medewerkers.length} collega's zichtbaar`;
  renderTabel(filtered);
  renderCards(filtered);
}

// Events
zoekInput.addEventListener("input", update);
afdelingSelect.addEventListener("change", update);
statusSelect.addEventListener("change", update);

laadMedewerkers();
