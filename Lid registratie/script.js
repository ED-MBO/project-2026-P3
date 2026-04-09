let leden = [];

// Laadt leden uit de backend en start de eerste render.
async function laadLeden() {
  try {
    const response = await fetch("get_leden.php");
    if (!response.ok) throw new Error("Server fout");
    leden = await response.json();
    update(); 
  } catch {
    const emptyState = document.getElementById("emptyState");
    emptyState.classList.add("is-visible");
    emptyState.textContent = "Overzicht kon niet geladen worden.";
    document.getElementById("countLine").textContent = "0 leden";
  }
}

// Filtert leden op zoekterm en status.
function filterLeden() {
  const zoek = document.getElementById("search").value.toLowerCase().trim();
  const status = document.getElementById("statusFilter").value;
  return leden.filter(
    (l) =>
      (!zoek || (l.Naam && l.Naam.toLowerCase().includes(zoek)) || (l.Email && l.Email.toLowerCase().includes(zoek))) &&
      (!status || l.Status === status)
  );
}

// Escapet tekst veilig voor HTML-output.
function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str == null ? "" : String(str);
  return div.innerHTML;
}

// Werkt de teller bovenin bij op basis van huidig filter.
function updateCount() {
  const countLine = document.getElementById("countLine");
  const filtered = filterLeden();
  if (leden.length === 0) {
    countLine.textContent = "0 leden";
  } else {
    countLine.textContent = `${filtered.length} van ${leden.length} leden zichtbaar`;
  }
}

// Rendert de tabelweergave van leden.
function renderTabel() {
  const filtered = filterLeden();
  const body = document.getElementById("ledenBody");
  if (filtered.length === 0) {
    body.innerHTML = '<tr><td colspan="6" class="empty-cell">' +
      (leden.length === 0 ? "Nog geen leden toegevoegd." : "Geen leden gevonden.") +
      "</td></tr>";
    return;
  }
  body.innerHTML = filtered.map((lid) => {
    const statusClass = (lid.Status || "Actief").toLowerCase().replace(/\s+/g, "-");
    const wijzigBtn = `<button type="button" class="btn-wijzig" data-actie="wijzig" data-id="${lid.Id}">Wijzigen</button>`;
    const verwijderBtn = lid.Status === "Actief"
      ? `<button type="button" class="btn-verwijder" data-actie="verwijder" data-id="${lid.Id}">Verwijderen</button>`
      : "";
    return `
      <tr>
        <td>${escapeHtml(lid.Naam || "")}</td>
        <td>${escapeHtml(lid.Mobiel || "—")}</td>
        <td>${escapeHtml(lid.Email || "")}</td>
        <td>${escapeHtml(lid.LidSinds || "")}</td>
        <td><span class="status status-${statusClass}">${escapeHtml(lid.Status || "Actief")}</span></td>
        <td>${wijzigBtn}</td>
        <td>${verwijderBtn}</td>
      </tr>
    `;
  }).join("");
}

// Rendert de mobiele kaartweergave van leden.
function renderCards() {
  const filtered = filterLeden();
  const container = document.getElementById("cardContainer");
  if (filtered.length === 0) {
    container.innerHTML = "";
    return;
  }
  container.innerHTML = filtered.map((lid) => {
    const statusClass = (lid.Status || "Actief").toLowerCase().replace(/\s+/g, "-");
    const wijzigBtn = `<button type="button" class="btn-wijzig" data-actie="wijzig" data-id="${lid.Id}">Wijzigen</button>`;
    const verwijderBtn = lid.Status === "Actief"
      ? `<button type="button" class="btn-verwijder" data-actie="verwijder" data-id="${lid.Id}">Verwijderen</button>`
      : "";
    return `
      <div class="lid-card">
        <div class="lid-card-header">
          <div>
            <div class="lid-card-title">${escapeHtml(lid.Naam || "")}</div>
            <div class="lid-card-sub">${escapeHtml(lid.Email || "")}</div>
          </div>
          <span class="status status-${statusClass}">${escapeHtml(lid.Status || "Actief")}</span>
        </div>
        <div class="lid-card-grid">
          <div class="lid-card-field">
            <label>Mobiel</label>
            <span>${escapeHtml(lid.Mobiel || "—")}</span>
          </div>
          <div class="lid-card-field">
            <label>E-mail</label>
            <span>${escapeHtml(lid.Email || "—")}</span>
          </div>
          <div class="lid-card-field">
            <label>Lid sinds</label>
            <span>${escapeHtml(lid.LidSinds || "—")}</span>
          </div>
        </div>
        <div class="card-acties">${wijzigBtn}${verwijderBtn}</div>
      </div>
    `;
  }).join("");
}

// Past filters toe en ververst alle weergaven.
function update() {
  const filtered = filterLeden();
  const emptyState = document.getElementById("emptyState");
  emptyState.classList.toggle("is-visible", filtered.length === 0);
  emptyState.textContent = leden.length === 0 ? "Nog geen leden toegevoegd." : "Geen leden gevonden.";
  updateCount();
  renderTabel();
  renderCards();
}

document.getElementById("search").addEventListener("input", update);
document.getElementById("statusFilter").addEventListener("change", update);

const navbar = document.getElementById("navbar");
const overlay = document.getElementById("overlay");

// Opent het mobiele navigatiemenu.
function openMenu() {
  navbar.classList.add("active");
  overlay.style.display = "block";
}

// Sluit het mobiele navigatiemenu.
function closeMenu() {
  navbar.classList.remove("active");
  overlay.style.display = "none";
}

document.getElementById("hamburger").addEventListener("click", openMenu);
document.getElementById("closeMenu").addEventListener("click", closeMenu);
overlay.addEventListener("click", closeMenu);

/* Modal Nieuw lid */
const modalBackdrop = document.getElementById("modalBackdrop");
const openBtn = document.getElementById("openLidModal");
const sluitModal = document.getElementById("sluitModal");
const annuleerModal = document.getElementById("annuleerModal");

// Opent de modal voor nieuw lid.
function openModal() {
  if (modalBackdrop) modalBackdrop.classList.add("open");
}

// Sluit de modal voor nieuw lid.
function closeModal() {
  if (modalBackdrop) modalBackdrop.classList.remove("open");
}

if (openBtn) openBtn.addEventListener("click", openModal);
if (sluitModal) sluitModal.addEventListener("click", closeModal);
if (annuleerModal) annuleerModal.addEventListener("click", closeModal);

if (modalBackdrop) {
  modalBackdrop.addEventListener("click", (e) => {
    if (e.target === modalBackdrop) closeModal();
  });
}

/* Lid wijzigen */
const editModalBackdrop = document.getElementById("editModalBackdrop");
const sluitEditModal = document.getElementById("sluitEditModal");
const annuleerEditModal = document.getElementById("annuleerEditModal");

// Zoekt een lid in de geladen lijst op id.
function vindLid(id) {
  return leden.find((l) => String(l.Id) === String(id));
}

// Opent de wijzig-modal en vult velden met liddata.
function openWijzigModal(lid) {
  if (!editModalBackdrop || !lid) return;
  document.getElementById("edit_lid_id").value = lid.Id;
  document.getElementById("edit_voornaam").value = lid.Voornaam || "";
  document.getElementById("edit_tussenvoegsel").value = lid.Tussenvoegsel || "";
  document.getElementById("edit_achternaam").value = lid.Achternaam || "";
  document.getElementById("edit_relatienummer").value = lid.Relatienummer || "";
  document.getElementById("edit_mobiel").value = lid.Mobiel || "";
  document.getElementById("edit_email").value = lid.Email || "";
  document.getElementById("edit_opmerking").value = lid.Opmerking || "";
  editModalBackdrop.classList.add("open");
}

// Sluit de wijzig-modal van leden.
function closeEditModal() {
  if (editModalBackdrop) editModalBackdrop.classList.remove("open");
}

if (sluitEditModal) sluitEditModal.addEventListener("click", closeEditModal);
if (annuleerEditModal) annuleerEditModal.addEventListener("click", closeEditModal);
if (editModalBackdrop) {
  editModalBackdrop.addEventListener("click", (e) => {
    if (e.target === editModalBackdrop) closeEditModal();
  });
}

/* Lid verwijderen */
const deleteModalBackdrop = document.getElementById("deleteModalBackdrop");
const deleteModalBericht = document.getElementById("deleteModalBericht");
const deleteModalFout = document.getElementById("deleteModalFout");
const deleteAchternaamCheck = document.getElementById("delete_achternaam_check");
const deleteLidId = document.getElementById("delete_lid_id");
const deleteBevestigAchternaam = document.getElementById("delete_bevestig_achternaam");
const deleteLidForm = document.getElementById("deleteLidForm");
const sluitDeleteModal = document.getElementById("sluitDeleteModal");
const annuleerVerwijder = document.getElementById("annuleerVerwijder");
const bevestigVerwijder = document.getElementById("bevestigVerwijder");
let deleteDoelAchternaam = "";

// Opent de verwijder-modal met achternaambevestiging.
function openVerwijderModal(lid) {
  if (!deleteModalBackdrop || !deleteModalBericht || !lid) return;
  deleteDoelAchternaam = String(lid.Achternaam || "");
  deleteModalBericht.innerHTML =
    `Weet u zeker dat u lid <strong>${escapeHtml(lid.Naam || "")}</strong> wilt verwijderen?<br>` +
    `Vul ter bevestiging exact deze achternaam in: <strong>${escapeHtml(deleteDoelAchternaam || "onbekend")}</strong>.`;
  if (deleteLidId) deleteLidId.value = lid.Id;
  if (deleteAchternaamCheck) deleteAchternaamCheck.value = "";
  if (deleteModalFout) {
    deleteModalFout.style.display = "none";
    deleteModalFout.textContent = "";
  }
  deleteModalBackdrop.classList.add("open");
  if (deleteAchternaamCheck) deleteAchternaamCheck.focus();
}

// Sluit de verwijder-modal en reset tijdelijke invoer.
function closeDeleteModal() {
  if (deleteModalBackdrop) deleteModalBackdrop.classList.remove("open");
  if (deleteLidId) deleteLidId.value = "";
  if (deleteBevestigAchternaam) deleteBevestigAchternaam.value = "";
  if (deleteAchternaamCheck) deleteAchternaamCheck.value = "";
  deleteDoelAchternaam = "";
}

if (sluitDeleteModal) sluitDeleteModal.addEventListener("click", closeDeleteModal);
if (annuleerVerwijder) annuleerVerwijder.addEventListener("click", closeDeleteModal);
if (deleteModalBackdrop) {
  deleteModalBackdrop.addEventListener("click", (e) => {
    if (e.target === deleteModalBackdrop) closeDeleteModal();
  });
}
if (bevestigVerwijder && deleteLidForm) {
  bevestigVerwijder.addEventListener("click", () => {
    const ingevuld = (deleteAchternaamCheck?.value || "").trim();
    if (ingevuld.toLowerCase() !== deleteDoelAchternaam.trim().toLowerCase()) {
      if (deleteModalFout) {
        deleteModalFout.textContent = "Achternaam komt niet overeen. Verwijderen is geannuleerd.";
        deleteModalFout.style.display = "block";
      }
      return;
    }
    if (deleteBevestigAchternaam) deleteBevestigAchternaam.value = ingevuld;
    deleteLidForm.submit();
  });
}

document.body.addEventListener("click", (e) => {
  const wijzigBtn = e.target.closest("[data-actie=wijzig]");
  if (wijzigBtn) {
    const lid = vindLid(wijzigBtn.getAttribute("data-id"));
    if (lid) openWijzigModal(lid);
    return;
  }
  const verwijderBtn = e.target.closest("[data-actie=verwijder]");
  if (verwijderBtn) {
    const lid = vindLid(verwijderBtn.getAttribute("data-id"));
    if (lid) openVerwijderModal(lid);
  }
});

/* Flash meldingen auto-hide na 3 seconden */
["successAlert", "errorAlert"].forEach((id) => {
  const el = document.getElementById(id);
  if (el) setTimeout(() => (el.style.display = "none"), 3000);
});

laadLeden();
