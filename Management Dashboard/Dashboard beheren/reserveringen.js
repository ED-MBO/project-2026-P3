let reserveringenChart = null;
let resJarenGeladen = false;

/**
 * Toon foutmelding voor het reserveringenoverzicht
 * @param {string} msg 
 */
function toonFoutRes(msg) {
  const el = document.getElementById("alertReserveringen");
  const tekst = document.getElementById("alertMsgReserveringen");
  const overzicht = document.getElementById("reserveringenOverzicht");
  if (!el || !tekst) return;
  tekst.textContent = msg;
  el.style.display = "flex";
  if (overzicht) overzicht.style.display = "none";
}

/**
 * Verberg foutmelding
 */
function verbergFoutRes() {
  const el = document.getElementById("alertReserveringen");
  const overzicht = document.getElementById("reserveringenOverzicht");
  if (el) el.style.display = "none";
  if (overzicht) overzicht.style.display = "block";
}

/**
 * Vul de tabel met data
 */
function vulTabelRes(labels, aantal) {
  const body = document.getElementById("tabelBodyRes");
  if (!body) return;
  body.innerHTML = "";

  labels.forEach((label, i) => {
    const tr = document.createElement("tr");
    const tdLabel = document.createElement("td");
    tdLabel.textContent = label;
    const tdAantal = document.createElement("td");
    tdAantal.textContent = aantal[i];

    tr.append(tdLabel, tdAantal);
    body.appendChild(tr);
  });
}

/**
 * Teken de grafiek met Chart.js
 */
function tekenGrafiekRes(labels, aantal) {
  const canvas = document.getElementById("reserveringenChart");
  if (!canvas) return;

  canvas.width = canvas.parentElement.clientWidth;
  const ctx = canvas.getContext("2d");

  // Mooie groene gradient voor reserveringen
  const gradient = ctx.createLinearGradient(0, 0, 0, 260);
  gradient.addColorStop(0, "rgba(74,222,128,.35)");
  gradient.addColorStop(1, "rgba(74,222,128,0)");

  if (reserveringenChart) reserveringenChart.destroy();

  reserveringenChart = new Chart(ctx, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          label: "Aantal reserveringen",
          data: aantal,
          borderColor: "#4ade80",
          backgroundColor: gradient,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: "#4ade80",
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
          beginAtZero: true,
        },
      },
    },
  });
}

/**
 * Vul de jaren dropdown met beschikbare jaren
 */
function vulJarenDropdownRes(jaren) {
  const select = document.getElementById("jaarSelectRes");
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
  resJarenGeladen = true;
}

/**
 * Hoofdfunctie om data te laden
 */
async function laadReserveringen() {
  verbergFoutRes();

  const typeEl = document.getElementById("periodeTypeRes");
  const jaarEl = document.getElementById("jaarSelectRes");
  const jaarLabel = document.getElementById("jaarLabelRes");
  if (!typeEl || !jaarEl) return;

  const type = typeEl.value;
  const jaar = jaarEl.value || new Date().getFullYear();
  
  // Toon jaarselectie alleen bij maand-weergave
  if (type === "jaar") {
    jaarEl.style.display = "none";
    if (jaarLabel) jaarLabel.style.display = "none";
  } else {
    jaarEl.style.display = "inline-block";
    if (jaarLabel) jaarLabel.style.display = "inline-block";
  }

  try {
    const res = await fetch(
      `get_reserveringen_per_periode.php?type=${type}&jaar=${jaar}`,
    );
    if (!res.ok) throw new Error();

    const data = await res.json();
    if (data.error) throw new Error(data.error);

    // Vul jarenlijst alleen de eerste keer
    if (!resJarenGeladen && data.jaren?.length) vulJarenDropdownRes(data.jaren);

    tekenGrafiekRes(data.labels, data.aantal);
    vulTabelRes(data.labels, data.aantal);

    const titel = document.getElementById("chartTitleRes");
    if (titel)
      titel.textContent =
        type === "jaar"
          ? "Aantal reserveringen per jaar"
          : `Aantal reserveringen per maand - ${jaar}`;
  } catch {
    toonFoutRes("Het overzicht kon niet geladen worden door een technische fout.");
  }
}

// Event Listeners
document
  .getElementById("periodeTypeRes")
  ?.addEventListener("change", laadReserveringen);
document
  .getElementById("jaarSelectRes")
  ?.addEventListener("change", laadReserveringen);

// Resize handling met timer voor performance
let resTimer;
window.addEventListener("resize", () => {
    clearTimeout(resTimer);
    resTimer = setTimeout(() => {
        if (reserveringenChart) {
            const canvas = document.getElementById("reserveringenChart");
            if (canvas) {
                canvas.width = canvas.parentElement.clientWidth;
                reserveringenChart.resize();
            }
        }
    }, 300);
});

// Eerste keer laden
document.addEventListener("DOMContentLoaded", laadReserveringen);
// Ook direct aanroepen voor het geval DOMContentLoaded al geweest is
if (document.readyState === "complete" || document.readyState === "interactive") {
    laadReserveringen();
}
