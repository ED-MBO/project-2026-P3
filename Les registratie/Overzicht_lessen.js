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
    const achternaamOk = !zoek || (rij.dataset.achternaam && rij.dataset.achternaam.includes(zoek));
    const statusOk    = !status || rij.dataset.status === status;
    const toon = achternaamOk && statusOk;
    rij.style.display = toon ? '' : 'none';
    if (toon) zichtbaar++;
  });

  cards.forEach((card) => {
    const achternaamOk = !zoek || (card.dataset.achternaam && card.dataset.achternaam.includes(zoek));
    const statusOk    = !status || card.dataset.status === status;
    card.style.display = (achternaamOk && statusOk) ? '' : 'none';
  });

  countLine.textContent = `${zichtbaar} van ${totaal} lessen zichtbaar`;
  emptyState.style.display = zichtbaar === 0 ? 'block' : 'none';
}

zoekInput.addEventListener('input', filterLessen);
statusSelect.addEventListener('change', filterLessen);

// Les-modal openen/sluiten
const modalBackdrop = document.getElementById('modalBackdrop');
const openLesModal = document.getElementById('openLesModal');
const sluitModal = document.getElementById('sluitModal');
const annuleerModal = document.getElementById('annuleerModal');

function openModal() {
  if (modalBackdrop) modalBackdrop.classList.add('open');
}

function closeModal() {
  if (modalBackdrop) modalBackdrop.classList.remove('open');
}

if (openLesModal) openLesModal.addEventListener('click', openModal);
if (sluitModal) sluitModal.addEventListener('click', closeModal);
if (annuleerModal) annuleerModal.addEventListener('click', closeModal);

if (modalBackdrop) {
  modalBackdrop.addEventListener('click', (e) => {
    if (e.target === modalBackdrop) closeModal();
  });
}

// Lid zoeken met eigen dropdown
const lidZoekInput = document.getElementById('lid_zoek');
const lidIdInput = document.getElementById('lid_id');
const lidSuggesties = document.getElementById('lidSuggesties');
const lidDropdownToggle = document.getElementById('lidDropdownToggle');
const lesForm = document.querySelector('#modalBackdrop form');
const leden = Array.isArray(window.bestaandeLeden) ? window.bestaandeLeden : [];

function normalize(value) {
  return (value || '').trim().toLowerCase();
}

function renderSuggesties(query) {
  if (!lidSuggesties) return;
  const zoek = normalize(query);
  const resultaat = zoek
    ? leden.filter((lid) => normalize(lid.naam).includes(zoek))
    : leden.slice(0, 10);

  if (resultaat.length === 0) {
    lidSuggesties.innerHTML = '<div class="lid-optie leeg">Geen leden gevonden</div>';
  } else {
    lidSuggesties.innerHTML = resultaat
      .slice(0, 8)
      .map(
        (lid) =>
          `<button type="button" class="lid-optie" data-lid-id="${lid.id}" data-lid-naam="${String(
            lid.naam || ''
          ).replace(/"/g, '&quot;')}">${lid.naam}</button>`
      )
      .join('');
  }
  lidSuggesties.classList.add('open');
}

function selecteerLid(id, naam) {
  if (!lidIdInput || !lidZoekInput) return;
  lidIdInput.value = String(id || '');
  lidZoekInput.value = naam || '';
  lidZoekInput.classList.remove('invalid');
  if (lidSuggesties) lidSuggesties.classList.remove('open');
}

if (lidZoekInput) {
  lidZoekInput.addEventListener('input', () => {
    lidIdInput.value = '';
    renderSuggesties(lidZoekInput.value);
  });
  lidZoekInput.addEventListener('focus', () => renderSuggesties(lidZoekInput.value));
}

if (lidDropdownToggle && lidZoekInput) {
  lidDropdownToggle.addEventListener('click', () => {
    renderSuggesties(lidZoekInput.value);
    lidZoekInput.focus();
  });
}

if (lidSuggesties) {
  lidSuggesties.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return;
    const button = target.closest('.lid-optie');
    if (!button || button.classList.contains('leeg')) return;
    selecteerLid(button.dataset.lidId, button.dataset.lidNaam);
  });
}

document.addEventListener('click', (event) => {
  const target = event.target;
  if (!(target instanceof HTMLElement)) return;
  if (target.closest('.lid-dropdown')) return;
  if (lidSuggesties) lidSuggesties.classList.remove('open');
});

if (lesForm && lidIdInput && lidZoekInput) {
  lesForm.addEventListener('submit', (event) => {
    if (lidIdInput.value) return;
    event.preventDefault();
    lidZoekInput.classList.add('invalid');
    lidZoekInput.focus();
  });
}