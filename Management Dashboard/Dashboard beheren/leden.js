let ledenChart = null;
let jarenGeladen = false;

(() => {
  const select = document.getElementById("jaarSelect");
  if (!select) return;
  const huidigJaar = new Date().getFullYear();
  const option = document.createElement("option");
  option.value = huidigJaar;
  option.textContent = huidigJaar;
  select.appendChild(option);
})();

function toonFout(msg) {
  const el = document.getElementById("alertEl");
  const tekst = document.getElementById("alertMsg");
  if (!el || !tekst) return;
  tekst.textContent = msg;
  el.style.display = "flex";
}

function verbergFout() {
  const el = document.getElementById("alertEl");
  if (el) el.style.display = "none";
}

function vulKaarten(data) {
  const totaalNu = data.totaal.at(-1) || 0;
  const totaalVorig = data.totaal.at(-2) || totaalNu;
  const groei = totaalNu - totaalVorig;

  document.getElementById("sTotaal").textContent = totaalNu;
  document.getElementById("sNieuw").textContent = data.nieuw.at(-1) || 0;
  document.getElementById("sActief").textContent = data.actief;
  document.getElementById("sInactief").textContent = data.inactief;

  let groeiTekst = "Gelijk aan vorige periode";
  if (groei > 0) groeiTekst = `+${groei} t.o.v. vorige periode`;
  else if (groei < 0) groeiTekst = `${groei} t.o.v. vorige periode`;

  document.getElementById("cTotaal").textContent = groeiTekst;
  document.getElementById("cNieuw").textContent = "Nieuw deze periode";
}

function maakGroeiBadge(delta, percentage) {
  const badge = document.createElement("span");
  badge.style.cssText = `
    display:inline-flex;align-items:center;gap:4px;
    font-size:11px;padding:2px 8px;border-radius:4px;
  `;
  const icon = document.createElement("i");
  const waarde = document.createElement("span");

  if (delta > 0) {
    badge.style.background = "#1e3328";
    badge.style.color = "#4ade80";
    icon.className = "fa-solid fa-arrow-up";
    waarde.textContent = percentage !== null ? `${percentage}%` : "";
  } else if (delta < 0) {
    badge.style.background = "#331e22";
    badge.style.color = "#f87171";
    icon.className = "fa-solid fa-arrow-down";
    waarde.textContent = percentage !== null ? `${percentage}%` : "";
  } else {
    badge.style.background = "#232734";
    badge.style.color = "#8b90a7";
    icon.className = "fa-solid fa-minus";
    waarde.textContent = "—";
  }
  badge.append(icon, waarde);
  return badge;
}

function vulTabel(labels, totaal, nieuw) {
  const body = document.getElementById("tabelBody");
  if (!body) return;
  body.innerHTML = "";

  labels.forEach((label, i) => {
    const vorig = i > 0 ? totaal[i - 1] : totaal[i] - nieuw[i];
    const delta = totaal[i] - vorig;
    const percentage = vorig ? ((delta / vorig) * 100).toFixed(1) : null;

    const tr = document.createElement("tr");
    const tdLabel = document.createElement("td");
    tdLabel.textContent = label;
    const tdTot = document.createElement("td");
    tdTot.textContent = totaal[i];
    const tdNieuw = document.createElement("td");
    tdNieuw.textContent = nieuw[i];
    const tdGroei = document.createElement("td");
    tdGroei.appendChild(maakGroeiBadge(delta, percentage));

    tr.append(tdLabel, tdTot, tdNieuw, tdGroei);
    body.appendChild(tr);
  });
}

function tekenGrafiek(labels, totaal) {
  const canvas = document.getElementById("ledenChart");
  if (!canvas) return;

  canvas.width = canvas.parentElement.clientWidth;
  const ctx = canvas.getContext("2d");

  const gradient = ctx.createLinearGradient(0, 0, 0, 260);
  gradient.addColorStop(0, "rgba(107,140,255,.35)");
  gradient.addColorStop(1, "rgba(107,140,255,0)");

  if (ledenChart) ledenChart.destroy();

  ledenChart = new Chart(ctx, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          label: "Totaal leden",
          data: totaal,
          borderColor: "#6b8cff",
          backgroundColor: gradient,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: "#6b8cff",
          pointBorderColor: "#111318",
          pointBorderWidth: 2,
          tension: 0.35,
          fill: true,
        },
      ],
    },
    options: {
      responsive: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#1a1d26",
          borderColor: "#262a36",
          borderWidth: 1,
          titleColor: "#e6e8ef",
          bodyColor: "#8b90a7",
          padding: 10,
        },
      },
      scales: {
        x: {
          ticks: { color: "#8b90a7", font: { size: 12 } },
          grid: { color: "#262a36" },
        },
        y: {
          ticks: { color: "#8b90a7", font: { size: 12 } },
          grid: { color: "#262a36" },
          beginAtZero: false,
        },
      },
    },
  });
}

function vulJarenDropdown(jaren) {
  const select = document.getElementById("jaarSelect");
  if (!select) return;
  const huidig = String(new Date().getFullYear());
  select.innerHTML = "";
  jaren.forEach((jaar) => {
    const option = document.createElement("option");
    option.value = jaar;
    option.textContent = jaar;
    if (String(jaar) === huidig) option.selected = true;
    select.appendChild(option);
  });
  jarenGeladen = true;
}

async function laadLedenData() {
  verbergFout();

  const typeEl = document.getElementById("periodeType");
  const jaarEl = document.getElementById("jaarSelect");
  if (!typeEl || !jaarEl) return;

  const type = typeEl.value;
  const jaar = jaarEl.value || new Date().getFullYear();
  jaarEl.disabled = type === "jaar";

  try {
    const res = await fetch(
      `get_leden_per_periode.php?type=${type}&jaar=${jaar}`,
    );
    if (!res.ok) throw new Error();

    const data = await res.json();
    if (data.error) throw new Error(data.error);

    if (!jarenGeladen && data.jaren?.length) vulJarenDropdown(data.jaren);

    vulKaarten(data);
    tekenGrafiek(data.labels, data.totaal);
    vulTabel(data.labels, data.totaal, data.nieuw);

    const titel = document.getElementById("chartTitle");
    if (titel)
      titel.textContent =
        type === "jaar"
          ? "Ledenaantal per jaar"
          : `Ledenaantal per maand - ${jaar}`;
  } catch {
    toonFout("Het overzicht kon niet geladen worden door een technische fout.");
  }
}

// Opstarten
laadLedenData();
document
  .getElementById("periodeType")
  ?.addEventListener("change", laadLedenData);
document
  .getElementById("jaarSelect")
  ?.addEventListener("change", laadLedenData);

// Resize met debounce
let resizeTimer;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(laadLedenData, 300);
});
