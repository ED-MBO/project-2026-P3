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

/* Data uit de database */

let accounts = [];

async function laadAccounts() {
  try {
    const response = await fetch("get_accounts.php");
    if (!response.ok) {
      throw new Error("Server gaf foutmelding");
    }
    accounts = await response.json();
    vulRollen();
    update();
  } catch (error) {
    console.error("Fout bij laden accounts:", error);
    emptyState.style.display = "block";
    emptyState.textContent = "Database verbinding mislukt.";
  }
}

const zoekInput = document.getElementById("search");
const rolFilter = document.getElementById("rolFilter");
const statusFilter = document.getElementById("statusFilter");
const tabelBody = document.getElementById("body");
const cardContainer = document.getElementById("cardContainer");
const emptyState = document.getElementById("emptyState");
const countLine = document.getElementById("countLine");

function vulRollen() {
  rolFilter.innerHTML = '<option value="">Alle rollen</option>';
  const uniek = [...new Set(accounts.map((a) => a.rol).filter(Boolean))].sort();
  uniek.forEach((r) => {
    const option = document.createElement("option");
    option.value = r;
    option.textContent = r;
    rolFilter.appendChild(option);
  });
}

function filterAccounts() {
  const zoek = zoekInput.value.toLowerCase();
  const rol = rolFilter.value;
  const status = statusFilter.value;
  return accounts.filter(
    (a) =>
      (!zoek ||
        a.naam.toLowerCase().includes(zoek) ||
        a.gebruikersnaam.toLowerCase().includes(zoek)) &&
      (!rol || a.rol === rol) &&
      (!status || a.status === status)
  );
}

function renderTabel(lijst) {
  tabelBody.innerHTML = "";

  if (!lijst.length) {
    emptyState.style.display = "block";
    return;
  }
  emptyState.style.display = "none";
  lijst.forEach((a) => {
    const row = document.createElement("tr");
    const statusClass =
      "status-" + a.status.toLowerCase().replace(/\s/g, "");
    row.innerHTML = `
      <td>${a.naam}</td>
      <td>${a.gebruikersnaam}</td>
      <td><span class="badge">${a.rol}</span></td>
      <td><span class="status ${statusClass}">${a.status}</span></td>
    `;
    tabelBody.appendChild(row);
  });
}

function renderCards(lijst) {
  cardContainer.innerHTML = "";
  lijst.forEach((a) => {
    const card = document.createElement("div");
    card.classList.add("account-card");
    const statusClass =
      "status-" + a.status.toLowerCase().replace(/\s/g, "");
    card.innerHTML = `
      <h3>${a.naam}</h3>
      <div class="gebruikersnaam">${a.gebruikersnaam}</div>
      <div class="card-row">
        <span class="card-label">Rol</span>
        <span class="badge">${a.rol}</span>
      </div>
      <div class="card-row">
        <span class="card-label">Status</span>
        <span class="status ${statusClass}">${a.status}</span>
      </div>
    `;
    cardContainer.appendChild(card);
  });
}

function update() {
  const filtered = filterAccounts();
  countLine.textContent = `${filtered.length} van ${accounts.length} account(s) zichtbaar`;
  renderTabel(filtered);
  renderCards(filtered);
}

zoekInput.addEventListener("input", update);
rolFilter.addEventListener("change", update);
statusFilter.addEventListener("change", update);

laadAccounts();
