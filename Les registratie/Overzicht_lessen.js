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
const zoekOpSelect = document.getElementById('zoekOp');
const statusSelect = document.getElementById('statusFilter');
const countLine    = document.getElementById('countLine');
const emptyState   = document.getElementById('emptyState');

function filterLessen() {
  const zoek   = zoekInput.value.toLowerCase();
  const zoekOp = zoekOpSelect ? zoekOpSelect.value : 'alles';
  const status = statusSelect.value;

  const rijen = document.querySelectorAll('#tabelBody tr');
  const cards = document.querySelectorAll('#cardContainer .les-card');

  let zichtbaar = 0; 

  rijen.forEach((rij) => {
    const voornaam = (rij.dataset.voornaam || '').toLowerCase();
    const achternaam = (rij.dataset.achternaam || '').toLowerCase();
    const prijs = (rij.dataset.lesPrijs || '').toLowerCase();
    const datum = (rij.dataset.lesDatum || '').toLowerCase();

    let zoekOk = true;
    if (zoek) {
      switch (zoekOp) {
        case 'naam':
          zoekOk = voornaam.includes(zoek) || achternaam.includes(zoek);
          break;
        case 'prijs':
          zoekOk = prijs.includes(zoek);
          break;
        case 'datum':
          zoekOk = datum.includes(zoek);
          break;
        case 'alles':
        default:
          zoekOk =
            voornaam.includes(zoek) ||
            achternaam.includes(zoek) ||
            prijs.includes(zoek) ||
            datum.includes(zoek);
      }
    }

    const statusOk    = !status || rij.dataset.status === status;
    const toon = zoekOk && statusOk;
    rij.style.display = toon ? '' : 'none';
    if (toon) zichtbaar++;
  });

  cards.forEach((card) => {
    const voornaam = (card.dataset.voornaam || '').toLowerCase();
    const achternaam = (card.dataset.achternaam || '').toLowerCase();
    const prijs = (card.dataset.lesPrijs || '').toLowerCase();
    const datum = (card.dataset.lesDatum || '').toLowerCase();

    let zoekOk = true;
    if (zoek) {
      switch (zoekOp) {
        case 'naam':
          zoekOk = voornaam.includes(zoek) || achternaam.includes(zoek);
          break;
        case 'prijs':
          zoekOk = prijs.includes(zoek);
          break;
        case 'datum':
          zoekOk = datum.includes(zoek);
          break;
        case 'alles':
        default:
          zoekOk =
            voornaam.includes(zoek) ||
            achternaam.includes(zoek) ||
            prijs.includes(zoek) ||
            datum.includes(zoek);
      }
    }

    const statusOk    = !status || card.dataset.status === status;
    card.style.display = (zoekOk && statusOk) ? '' : 'none';
  });

  countLine.textContent = `${zichtbaar} van ${totaal} lessen zichtbaar`;
  emptyState.style.display = zichtbaar === 0 ? 'block' : 'none';
}

zoekInput.addEventListener('input', filterLessen);
if (zoekOpSelect) {
  zoekOpSelect.addEventListener('change', filterLessen);
}
statusSelect.addEventListener('change', filterLessen);

// ===================== MODAL: NIEUWE LES =====================
const modalBackdrop = document.getElementById('modalBackdrop');
const openLesModal = document.getElementById('openLesModal');
const sluitModal = document.getElementById('sluitModal');
const annuleerModal = document.getElementById('annuleerModal');

function openModal() {
  if (modalBackdrop) modalBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  if (modalBackdrop) modalBackdrop.classList.remove('open');
  document.body.style.overflow = '';
}

if (openLesModal) openLesModal.addEventListener('click', openModal);
if (sluitModal) sluitModal.addEventListener('click', closeModal);
if (annuleerModal) annuleerModal.addEventListener('click', closeModal);

if (modalBackdrop) {
  modalBackdrop.addEventListener('click', (e) => {
    if (e.target === modalBackdrop) closeModal();
  });
}

// ===================== MODAL: LES WIJZIGEN =====================
const editModalBackdrop = document.getElementById('editModalBackdrop');
const sluitEditModal = document.getElementById('sluitEditModal');
const annuleerEditModal = document.getElementById('annuleerEditModal');
const editLesForm = document.getElementById('editLesForm');

function openEditModal(lesData) {
  document.getElementById('editLesId').value = lesData.id;
  document.getElementById('editNaam').value = lesData.naam || '';
  document.getElementById('editPrijs').value = lesData.prijs || '';
  document.getElementById('editDatum').value = lesData.datum || '';
  document.getElementById('editTijd').value = lesData.tijd || '';
  document.getElementById('editMinPersonen').value = lesData.min || '3';
  document.getElementById('editMaxPersonen').value = lesData.max || '9';
  document.getElementById('editBeschikbaarheid').value = lesData.beschikbaarheid || 'Ingepland';

  // Reset alle validatiefouten
  clearEditErrors();

  if (editModalBackdrop) editModalBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeEditModal() {
  if (editModalBackdrop) editModalBackdrop.classList.remove('open');
  document.body.style.overflow = '';
}

function clearEditErrors() {
  const errorFields = ['editNaamError', 'editPrijsError', 'editDatumError', 'editTijdError', 'editMinError', 'editMaxError'];
  errorFields.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.style.display = 'none';
      el.textContent = '';
    }
  });
  // reset invalid classes
  ['editNaam', 'editPrijs', 'editDatum', 'editTijd', 'editMinPersonen', 'editMaxPersonen'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove('invalid');
  });
}

function showEditError(fieldId, errorId, message) {
  const field = document.getElementById(fieldId);
  const error = document.getElementById(errorId);
  if (field) field.classList.add('invalid');
  if (error) {
    error.textContent = message;
    error.style.display = 'block';
  }
}

// Client-side validatie voor het edit formulier
if (editLesForm) {
  editLesForm.addEventListener('submit', (e) => {
    clearEditErrors();
    let heeftFouten = false;

    const naam = document.getElementById('editNaam').value.trim();
    const prijs = document.getElementById('editPrijs').value.trim();
    const datum = document.getElementById('editDatum').value.trim();
    const tijd = document.getElementById('editTijd').value.trim();
    const minP = document.getElementById('editMinPersonen').value.trim();
    const maxP = document.getElementById('editMaxPersonen').value.trim();

    if (!naam) {
      showEditError('editNaam', 'editNaamError', 'Lesnaam is verplicht.');
      heeftFouten = true;
    }
    if (!prijs || isNaN(prijs) || parseFloat(prijs) < 0) {
      showEditError('editPrijs', 'editPrijsError', 'Een geldige prijs is verplicht.');
      heeftFouten = true;
    }
    if (!datum) {
      showEditError('editDatum', 'editDatumError', 'Datum is verplicht.');
      heeftFouten = true;
    }
    if (!tijd) {
      showEditError('editTijd', 'editTijdError', 'Tijd is verplicht.');
      heeftFouten = true;
    }
    if (!minP || isNaN(minP) || parseInt(minP) < 1) {
      showEditError('editMinPersonen', 'editMinError', 'Min. personen is verplicht (minimaal 1).');
      heeftFouten = true;
    }
    if (!maxP || isNaN(maxP) || parseInt(maxP) < 1) {
      showEditError('editMaxPersonen', 'editMaxError', 'Max. personen is verplicht (minimaal 1).');
      heeftFouten = true;
    }
    if (minP && maxP && !isNaN(minP) && !isNaN(maxP) && parseInt(minP) > parseInt(maxP)) {
      showEditError('editMinPersonen', 'editMinError', 'Minimum mag niet groter zijn dan maximum.');
      heeftFouten = true;
    }

    if (heeftFouten) {
      e.preventDefault();
    }
  });
}

if (sluitEditModal) sluitEditModal.addEventListener('click', closeEditModal);
if (annuleerEditModal) annuleerEditModal.addEventListener('click', closeEditModal);

if (editModalBackdrop) {
  editModalBackdrop.addEventListener('click', (e) => {
    if (e.target === editModalBackdrop) closeEditModal();
  });
}

// ===================== MODAL: LES VERWIJDEREN =====================
const deleteModalBackdrop = document.getElementById('deleteModalBackdrop');
const sluitDeleteModal = document.getElementById('sluitDeleteModal');
const annuleerDeleteModal = document.getElementById('annuleerDeleteModal');
const bevestigDeleteBtn = document.getElementById('bevestigDelete');
const deleteModalTekst = document.getElementById('deleteModalTekst');
const confirmAchternaamInput = document.getElementById('confirmAchternaam');
const deleteError = document.getElementById('deleteError');

let currentDeleteId = null;
let currentDeleteAchternaam = null;

function openDeleteModal(id, achternaam) {
  currentDeleteId = id;
  currentDeleteAchternaam = achternaam;
  deleteModalTekst.innerHTML = `Bent u zeker dat u deze les wilt verwijderen? Typ ter bevestiging de achternaam <strong>${achternaam}</strong>. Dit kan niet ongedaan worden gemaakt.`;
  confirmAchternaamInput.value = '';
  deleteError.style.display = 'none';
  if (deleteModalBackdrop) deleteModalBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
  if (deleteModalBackdrop) deleteModalBackdrop.classList.remove('open');
  document.body.style.overflow = '';
  currentDeleteId = null;
  currentDeleteAchternaam = null;
}

if (sluitDeleteModal) sluitDeleteModal.addEventListener('click', closeDeleteModal);
if (annuleerDeleteModal) annuleerDeleteModal.addEventListener('click', closeDeleteModal);

if (deleteModalBackdrop) {
  deleteModalBackdrop.addEventListener('click', (e) => {
    if (e.target === deleteModalBackdrop) closeDeleteModal();
  });
}

if (bevestigDeleteBtn) {
  bevestigDeleteBtn.addEventListener('click', async () => {
    if (!currentDeleteId || !currentDeleteAchternaam) return;

    const invoer = confirmAchternaamInput.value.trim();

    if (invoer.toLowerCase() !== currentDeleteAchternaam.toLowerCase()) {
      deleteError.textContent = 'De ingevoerde achternaam komt niet overeen. De les is NIET verwijderd.';
      deleteError.style.display = 'block';
      return;
    }

    try {
      const res = await fetch('delete_les.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentDeleteId, achternaam: invoer })
      });

      const data = await res.json();

      if (data.success) {
        // Verwijder de rij en card uit de DOM
        const rij = document.querySelector(`#tabelBody tr[data-les-id="${currentDeleteId}"]`);
        if (rij) rij.remove();

        const card = document.querySelector(`#cardContainer .les-card[data-les-id="${currentDeleteId}"]`);
        if (card) card.remove();

        closeDeleteModal();

        // Toon succesbericht
        const jsAlert = document.getElementById('jsSuccessAlert');
        const jsMsg = document.getElementById('jsSuccessMessage');
        if (jsAlert && jsMsg) {
          jsMsg.textContent = 'Les succesvol verwijderd!';
          jsAlert.style.display = 'flex';
          setTimeout(() => (jsAlert.style.display = 'none'), 3000);
        }

        // Update counter
        filterLessen();
      } else {
        deleteError.textContent = data.message || 'Er is een fout opgetreden.';
        deleteError.style.display = 'block';
      }
    } catch (err) {
      console.error(err);
      deleteError.textContent = 'Server fout bij verwijderen.';
      deleteError.style.display = 'block';
    }
  });
}

// ===================== EDIT & DELETE BUTTON LISTENERS =====================
function getLesDataFromElement(el) {
  return {
    id: el.dataset.lesId,
    naam: el.dataset.lesNaam,
    prijs: el.dataset.lesPrijs,
    datum: el.dataset.lesDatum,
    tijd: el.dataset.lesTijd,
    min: el.dataset.lesMin,
    max: el.dataset.lesMax,
    beschikbaarheid: el.dataset.lesBeschikbaarheid
  };
}

// Edit knoppen - tabel
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    if (row) openEditModal(getLesDataFromElement(row));
  });
});

// Edit knoppen - cards
document.querySelectorAll('.btn-edit-card').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('.les-card');
    if (card) openEditModal(getLesDataFromElement(card));
  });
});

// Delete knoppen - tabel
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    if (row) openDeleteModal(row.dataset.lesId, row.dataset.achternaam);
  });
});

// Delete knoppen - cards
document.querySelectorAll('.btn-delete-card').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('.les-card');
    if (card) openDeleteModal(card.dataset.lesId, card.dataset.achternaam);
  });
});

// ===================== FLASH BERICHTEN AUTO-HIDE =====================
['successAlert', 'errorAlert'].forEach(id => {
  const el = document.getElementById(id);
  if (el) setTimeout(() => (el.style.display = 'none'), 3000);
});

// ===================== LID ZOEKEN (voor nieuwe les modal) =====================
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