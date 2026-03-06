/* =========================================
   HAMBURGER MENU
   ========================================= */
const hamburger = document.querySelector('.hamburger');
const navbar    = document.querySelector('.navbar');
const sluit     = document.querySelector('.close-menu');
const overlay   = document.querySelector('.overlay');

hamburger.addEventListener('click', () => {
  navbar.classList.add('active');
  overlay.style.display = 'block';
  document.body.style.overflow = 'hidden';
});

function sluitMenu() {
  navbar.classList.remove('active');
  overlay.style.display = 'none';
  document.body.style.overflow = 'auto';
}

sluit.addEventListener('click', sluitMenu);
overlay.addEventListener('click', sluitMenu);

/* =========================================
   FILTER LESSEN
   ========================================= */
const zoekInput    = document.getElementById('search');
const statusSelect = document.getElementById('statusFilter');
const countLine    = document.getElementById('countLine');
const emptyState   = document.getElementById('emptyState');

function filterLessen() {
  const zoek   = zoekInput.value.toLowerCase();
  const status = statusSelect.value;

  const rijen = document.querySelectorAll('#tabelBody tr');
  const cards = document.querySelectorAll('#cardContainer .les-card');

  let zichtbaar = 0;

  rijen.forEach((rij) => {
    const naamOk   = !zoek   || rij.dataset.naam.includes(zoek);
    const statusOk = !status || rij.dataset.status === status;
    const toon     = naamOk && statusOk;
    rij.style.display = toon ? '' : 'none';
    if (toon) zichtbaar++;
  });

  cards.forEach((card) => {
    const naamOk   = !zoek   || card.dataset.naam.includes(zoek);
    const statusOk = !status || card.dataset.status === status;
    card.style.display = (naamOk && statusOk) ? '' : 'none';
  });

  countLine.textContent = `${zichtbaar} van ${totaal} lessen zichtbaar`;
  emptyState.style.display = zichtbaar === 0 ? 'block' : 'none';
}

zoekInput.addEventListener('input', filterLessen);
statusSelect.addEventListener('change', filterLessen);

/* =========================================
   MODAL
   ========================================= */
const backdrop    = document.getElementById('modalBackdrop');
const openBtn     = document.getElementById('openModal');
const sluitBtn    = document.getElementById('sluitModal');
const annuleerBtn = document.getElementById('annuleerModal');

function openModal() {
  backdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function sluitModal() {
  backdrop.classList.remove('open');
  document.body.style.overflow = '';
}

openBtn.addEventListener('click', openModal);
sluitBtn.addEventListener('click', sluitModal);
annuleerBtn.addEventListener('click', sluitModal);

// Klik buiten modal = sluiten
backdrop.addEventListener('click', (e) => {
  if (e.target === backdrop) sluitModal();
});

// Escape-toets = sluiten
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') sluitModal();
});

// Heropen modal bij validatiefouten (waarde gezet vanuit PHP)
if (typeof modalOpenBijLaad !== 'undefined' && modalOpenBijLaad) {
  openModal();
}