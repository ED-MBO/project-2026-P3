// Hamburger menu
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

// Filteren
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
    const toon = naamOk && statusOk;
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