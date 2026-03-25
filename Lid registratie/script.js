let leden = [];

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

function filterLeden() {
  const zoek = document.getElementById("search").value.toLowerCase().trim();
  const status = document.getElementById("statusFilter").value;
  return leden.filter(
    (l) =>
      (!zoek || (l.Naam && l.Naam.toLowerCase().includes(zoek)) || (l.Email && l.Email.toLowerCase().includes(zoek))) &&
      (!status || l.Status === status)
  );
}

function updateCount() {
  const countLine = document.getElementById("countLine");
  const filtered = filterLeden();
  if (leden.length === 0) {
    countLine.textContent = "0 leden";
  } else {
    countLine.textContent = `${filtered.length} van ${leden.length} leden zichtbaar`;
  }
}

function renderTabel() {
  const filtered = filterLeden();
  const body = document.getElementById("ledenBody");
  if (filtered.length === 0) {
    body.innerHTML = '<tr><td colspan="5" class="empty-cell">' +
      (leden.length === 0 ? "Nog geen leden toegevoegd." : "Geen leden gevonden.") +
      "</td></tr>";
    return;
  }
  body.innerHTML = filtered.map((lid) => {
    const statusClass = (lid.Status || "Actief").toLowerCase().replace(/\s+/g, "-");
    return `
      <tr>
        <td>${lid.Naam || ""}</td>
        <td>${lid.Mobiel || "—"}</td>
        <td>${lid.Email || ""}</td>
        <td>${lid.LidSinds || ""}</td>
        <td><span class="status status-${statusClass}">${lid.Status || "Actief"}</span></td>
      </tr>
    `;
  }).join("");
}

function renderCards() {
  const filtered = filterLeden();
  const container = document.getElementById("cardContainer");
  if (filtered.length === 0) {
    container.innerHTML = "";
    return;
  }
  container.innerHTML = filtered.map((lid) => {
    const statusClass = (lid.Status || "Actief").toLowerCase().replace(/\s+/g, "-");
    return `
      <div class="lid-card">
        <div class="lid-card-header">
          <div>
            <div class="lid-card-title">${lid.Naam || ""}</div>
            <div class="lid-card-sub">${lid.Email || ""}</div>
          </div>
          <span class="status status-${statusClass}">${lid.Status || "Actief"}</span>
        </div>
        <div class="lid-card-grid">
          <div class="lid-card-field">
            <label>Mobiel</label>
            <span>${lid.Mobiel || "—"}</span>
          </div>
          <div class="lid-card-field">
            <label>E-mail</label>
            <span>${lid.Email || "—"}</span>
          </div>
          <div class="lid-card-field">
            <label>Lid sinds</label>
            <span>${lid.LidSinds || "—"}</span>
          </div>
        </div>
      </div>
    `;
  }).join("");
}

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

/* Modal Nieuw lid */
const modalBackdrop = document.getElementById("modalBackdrop");
const openBtn = document.getElementById("openLidModal");
const sluitModal = document.getElementById("sluitModal");
const annuleerModal = document.getElementById("annuleerModal");

function openModal() {
  if (modalBackdrop) modalBackdrop.classList.add("open");
}

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

/* Flash meldingen auto-hide na 3 seconden */
["successAlert", "errorAlert"].forEach((id) => {
  const el = document.getElementById(id);
  if (el) setTimeout(() => (el.style.display = "none"), 3000);
});

laadLeden();
