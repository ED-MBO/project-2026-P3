/* Hamburger */
const hamburger = document.querySelector(".hamburger");
const nav = document.querySelector(".navbar");
const sluitNav = document.querySelector(".close-menu");
const overlay = document.querySelector(".overlay");
 
hamburger.addEventListener("click", () => {
  nav.classList.add("active");
  overlay.style.display = "block";
  document.body.style.overflow = "hidden";
});

function closeNav() {
  nav.classList.remove("active");
  overlay.style.display = "none";
  document.body.style.overflow = "";
}

sluitNav.addEventListener("click", closeNav);
overlay.addEventListener("click", closeNav);

/* Modal */
const modal = document.getElementById("modalBackdrop");

function openModal() {
  modal.classList.add("open");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  modal.classList.remove("open");
  document.body.style.overflow = "";
}

document.getElementById("openModal").addEventListener("click", openModal);
document.getElementById("sluitModal").addEventListener("click", closeModal);
document.getElementById("annuleerModal").addEventListener("click", closeModal);
modal.addEventListener("click", (e) => {
  if (e.target === modal) closeModal();
});

/* Success melding */
const params = new URLSearchParams(window.location.search);
if (params.get("succes")) {
  document.getElementById("successAlert").style.display = "flex";
}

/* Data */
let medewerkers = [];

const zoekInput = document.getElementById("search");
const afdelingSelect = document.getElementById("afdeling");
const statusSelect = document.getElementById("status");
const tabelBody = document.getElementById("body");
const cardContainer = document.getElementById("cardContainer");
const emptyState = document.getElementById("emptyState");
const countLine = document.getElementById("countLine");

async function laadMedewerkers() {
  try {
    const res = await fetch("get_medewerkers.php");
    if (!res.ok) throw new Error("Server fout");
    medewerkers = await res.json();
    vulAfdelingen();
    update();
  } catch (err) {
    console.error(err);
    emptyState.style.display = "block";
    emptyState.textContent = "Database verbinding mislukt.";
  }
}

function vulAfdelingen() {
  afdelingSelect.innerHTML = '<option value="">Alle afdelingen</option>';
  [...new Set(medewerkers.map((m) => m.afdeling))].forEach((a) => {
    const opt = document.createElement("option");
    opt.value = opt.textContent = a;
    afdelingSelect.appendChild(opt);
  });
}

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

function statusClass(s) {
  return "status-" + s.toLowerCase().replace(/\s/g, "");
}

function renderTabel(lijst) {
  tabelBody.innerHTML = "";
  if (!lijst.length) {
    emptyState.style.display = "block";
    return;
  }
  emptyState.style.display = "none";
  lijst.forEach((m) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
            <td>${m.naam}</td>
            <td>${m.functie}</td>
            <td><span class="badge">${m.afdeling}</span></td>
            <td><span class="status ${statusClass(m.status)}">${m.status}</span></td>
          `;
    tabelBody.appendChild(tr);
  });
}

function renderCards(lijst) {
  cardContainer.innerHTML = "";
  lijst.forEach((m) => {
    const card = document.createElement("div");
    card.className = "team-card";
    card.innerHTML = `
            <h3>${m.naam}</h3>
            <div class="functie">${m.functie}</div>
            <div class="card-row">
              <span class="card-label">Afdeling</span>
              <span class="badge">${m.afdeling}</span>
            </div>
            <div class="card-row">
              <span class="card-label">Status</span>
              <span class="status ${statusClass(m.status)}">${m.status}</span>
            </div>
          `;
    cardContainer.appendChild(card);
  });
}

function update() {
  const filtered = filterMedewerkers();
  countLine.textContent = `${filtered.length} van ${medewerkers.length} collega's zichtbaar`;
  renderTabel(filtered);
  renderCards(filtered);
}

zoekInput.addEventListener("input", update);
afdelingSelect.addEventListener("change", update);
statusSelect.addEventListener("change", update);

laadMedewerkers();
