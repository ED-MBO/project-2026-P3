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

function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str == null ? "" : String(str);
  return div.innerHTML;
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
      <td>${escapeHtml(a.naam)}</td>
      <td>${escapeHtml(a.gebruikersnaam)}</td>
      <td><span class="badge">${escapeHtml(a.rol)}</span></td>
      <td><span class="status ${statusClass}">${escapeHtml(a.status)}</span></td>
      <td><button type="button" class="btn-wijzig" data-actie="wijzig" data-id="${a.id}">Wijzigen</button></td>
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
      <h3>${escapeHtml(a.naam)}</h3>
      <div class="gebruikersnaam">${escapeHtml(a.gebruikersnaam)}</div>
      <div class="card-row">
        <span class="card-label">Rol</span>
        <span class="badge">${escapeHtml(a.rol)}</span>
      </div>
      <div class="card-row">
        <span class="card-label">Status</span>
        <span class="status ${statusClass}">${escapeHtml(a.status)}</span>
      </div>
      <button type="button" class="btn-wijzig btn-wijzig-card" data-actie="wijzig" data-id="${a.id}">Wijzigen</button>
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

/* Modal Account aanmaken */
const modalBackdrop = document.getElementById("modalBackdrop");
const openBtn = document.getElementById("openAccountModal");
const sluitModal = document.getElementById("sluitModal");
const annuleerModal = document.getElementById("annuleerModal");

if (openBtn) {
  openBtn.addEventListener("click", () => {
    if (modalBackdrop) modalBackdrop.classList.add("open");
  });
}
if (sluitModal) {
  sluitModal.addEventListener("click", () => {
    if (modalBackdrop) modalBackdrop.classList.remove("open");
  });
}
if (annuleerModal) {
  annuleerModal.addEventListener("click", () => {
    if (modalBackdrop) modalBackdrop.classList.remove("open");
  });
}
if (modalBackdrop) {
  modalBackdrop.addEventListener("click", (e) => {
    if (e.target === modalBackdrop) modalBackdrop.classList.remove("open");
  });
}

/* Modal Account wijzigen */
const cfg = window.accountBeheerConfig || { isAdministrator: false };
const editModalBackdrop = document.getElementById("editModalBackdrop");
const sluitEditModal = document.getElementById("sluitEditModal");
const annuleerEditModal = document.getElementById("annuleerEditModal");
const editRolGroepSelect = document.getElementById("editRolGroepSelect");
const editRolGroepReadonly = document.getElementById("editRolGroepReadonly");
const editRolSelect = document.getElementById("edit_rol");
const editRolReadonlyText = document.getElementById("edit_rol_readonly_text");

function sluitEditVenster() {
  if (editModalBackdrop) editModalBackdrop.classList.remove("open");
}

function openWijzigModal(account) {
  if (!editModalBackdrop || !account) return;

  document.getElementById("edit_gebruiker_id").value = account.id;
  document.getElementById("edit_voornaam").value = account.voornaam || "";
  document.getElementById("edit_tussenvoegsel").value =
    account.tussenvoegsel || "";
  document.getElementById("edit_achternaam").value = account.achternaam || "";
  document.getElementById("edit_gebruikersnaam").value =
    account.gebruikersnaam || "";
  document.getElementById("edit_wachtwoord").value = "";

  const staffRollen = ["Medewerker", "Administrator"];
  const isStaff = staffRollen.includes(account.rol);
  const alleenLidSelect = !cfg.isAdministrator;

  if (alleenLidSelect && isStaff) {
    editRolGroepSelect.style.display = "none";
    editRolSelect.disabled = true;
    editRolSelect.removeAttribute("required");
    editRolGroepReadonly.style.display = "";
    editRolReadonlyText.textContent =
      account.rol +
      " — alleen een administrator kan deze rol wijzigen. Overige gegevens kunt u wel aanpassen.";
  } else {
    editRolGroepSelect.style.display = "";
    editRolGroepReadonly.style.display = "none";
    editRolSelect.disabled = false;
    editRolSelect.setAttribute("required", "required");
    const allowed = cfg.isAdministrator
      ? ["Lid", "Medewerker", "Administrator"]
      : ["Lid"];
    const r = account.rol;
    if (allowed.includes(r)) {
      editRolSelect.value = r;
    } else {
      editRolSelect.value = "Lid";
    }
  }

  editModalBackdrop.classList.add("open");
}

function vindAccount(id) {
  return accounts.find((x) => String(x.id) === String(id));
}

document.body.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-actie=wijzig]");
  if (!btn) return;
  const id = btn.getAttribute("data-id");
  const acc = vindAccount(id);
  if (acc) openWijzigModal(acc);
});

if (sluitEditModal) {
  sluitEditModal.addEventListener("click", sluitEditVenster);
}
if (annuleerEditModal) {
  annuleerEditModal.addEventListener("click", sluitEditVenster);
}
if (editModalBackdrop) {
  editModalBackdrop.addEventListener("click", (e) => {
    if (e.target === editModalBackdrop) sluitEditVenster();
  });
}

laadAccounts();
