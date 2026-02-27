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

/* Neppe data Medewerkers*/

const medewerkers = [
  {
    naam: "Lara van den Berg",
    functie: "Product Designer",
    afdeling: "Design",
    status: "Beschikbaar",
    email: "l.vandenberg@org.nl",
  },
  {
    naam: "Thomas Hendrikx",
    functie: "Senior Developer",
    afdeling: "Engineering",
    status: "Bezet",
    email: "t.hendrikx@org.nl",
  },
  {
    naam: "Priya Nair",
    functie: "Scrum Master",
    afdeling: "Engineering",
    status: "Beschikbaar",
    email: "p.nair@org.nl",
  },
  {
    naam: "Daan Smits",
    functie: "Marketing Manager",
    afdeling: "Marketing",
    status: "Afwezig",
    email: "d.smits@org.nl",
  },
  {
    naam: "Fatima El Amine",
    functie: "HR Adviseur",
    afdeling: "HR",
    status: "Beschikbaar",
    email: null,
  },
  {
    naam: "Joris Kuipers",
    functie: "Lead Platform Engineer",
    afdeling: "Product & Tech",
    status: "Op locatie",
    email: "j.kuipers@org.nl",
  },
  {
    naam: "Sara Meijer",
    functie: "UX Researcher",
    afdeling: "Design",
    status: "Beschikbaar",
    email: "s.meijer@org.nl",
  },
];

const zoekInput = document.getElementById("search");
const afdelingSelect = document.getElementById("afdeling");
const statusSelect = document.getElementById("status");
const tabelBody = document.getElementById("body");
const cardContainer = document.getElementById("cardContainer");
const emptyState = document.getElementById("emptyState");
const countLine = document.getElementById("countLine");

function vulAfdelingen() {
  const uniek = [...new Set(medewerkers.map((m) => m.afdeling))];
  uniek.forEach((a) => {
    const option = document.createElement("option");
    option.value = a;
    option.textContent = a;
    afdelingSelect.appendChild(option);
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
        <td>${m.email ? `<a href="mailto:${m.email}" class="email-link">${m.email}</a>` : `<span class="geen-email">geen e-mail</span>`}</td>
      `;

    tabelBody.appendChild(row);
  });
}

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

        <div class="card-row">
          <span class="card-label">Contact</span>
          <span>${m.email ? `<a href="mailto:${m.email}" class="email-link">${m.email}</a>` : `<span class="geen-email">geen e-mail</span>`}</span>
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

vulAfdelingen();
update();
