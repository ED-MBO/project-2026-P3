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
   FILTER RESERVERINGEN
   ========================================= */
const zoekInput    = document.getElementById('search');
const statusSelect = document.getElementById('statusFilter');
const countLine    = document.getElementById('countLine');
const emptyState   = document.getElementById('emptyState');

function filterReserveringen() {
  const zoek   = zoekInput.value.toLowerCase();
  const status = statusSelect.value;

  const rijen = document.querySelectorAll('#tabelBody tr');
  const cards = document.querySelectorAll('#cardContainer .res-card');

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

  countLine.textContent = `${zichtbaar} van ${totaal} reserveringen zichtbaar`;
  emptyState.style.display = zichtbaar === 0 ? 'block' : 'none';
}

zoekInput.addEventListener('input', filterReserveringen);
statusSelect.addEventListener('change', filterReserveringen);

/* =========================================
   MODAL: NIEUWE RESERVERING
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

// Heropen modal bij validatiefouten (waarde gezet vanuit PHP)
if (typeof modalOpenBijLaad !== 'undefined' && modalOpenBijLaad) {
  openModal();
}

/* =========================================
   MODAL: RESERVERING WIJZIGEN
   ========================================= */
const editModalBackdrop = document.getElementById('editModalBackdrop');
const sluitEditModal    = document.getElementById('sluitEditModal');
const annuleerEditModal = document.getElementById('annuleerEditModal');
const editResForm       = document.getElementById('editResForm');

function openEditModal(resData) {
  document.getElementById('editResId').value           = resData.id;
  document.getElementById('editVoornaam').value         = resData.voornaam || '';
  document.getElementById('editTussenvoegsel').value    = resData.tussenvoegsel || '';
  document.getElementById('editAchternaam').value       = resData.achternaam || '';
  document.getElementById('editNummer').value           = resData.nummer || '';
  document.getElementById('editDatum').value            = resData.datum || '';
  document.getElementById('editTijd').value             = resData.tijd || '';
  document.getElementById('editReserveringstatus').value = resData.status || 'Gereserveerd';

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
  const errorFields = ['editVoornaamError', 'editAchternaamError', 'editNummerError', 'editDatumError', 'editTijdError'];
  errorFields.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.style.display = 'none';
      el.textContent = '';
    }
  });
  // reset invalid classes
  ['editVoornaam', 'editAchternaam', 'editNummer', 'editDatum', 'editTijd'].forEach(id => {
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
if (editResForm) {
  editResForm.addEventListener('submit', (e) => {
    clearEditErrors();
    let heeftFouten = false;

    const voornaam    = document.getElementById('editVoornaam').value.trim();
    const achternaam  = document.getElementById('editAchternaam').value.trim();
    const nummer      = document.getElementById('editNummer').value.trim();
    const datum       = document.getElementById('editDatum').value.trim();
    const tijd        = document.getElementById('editTijd').value.trim();

    if (!voornaam) {
      showEditError('editVoornaam', 'editVoornaamError', 'Voornaam is verplicht.');
      heeftFouten = true;
    } else if (voornaam.length > 50) {
      showEditError('editVoornaam', 'editVoornaamError', 'Maximaal 50 tekens.');
      heeftFouten = true;
    }
    if (!achternaam) {
      showEditError('editAchternaam', 'editAchternaamError', 'Achternaam is verplicht.');
      heeftFouten = true;
    } else if (achternaam.length > 50) {
      showEditError('editAchternaam', 'editAchternaamError', 'Maximaal 50 tekens.');
      heeftFouten = true;
    }
    if (!nummer || isNaN(nummer) || parseInt(nummer) < 1) {
      showEditError('editNummer', 'editNummerError', 'Voer een geldig nummer in.');
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

/* =========================================
   MODAL: RESERVERING VERWIJDEREN
   ========================================= */
const deleteModalBackdrop  = document.getElementById('deleteModalBackdrop');
const sluitDeleteModal     = document.getElementById('sluitDeleteModal');
const annuleerDeleteModal  = document.getElementById('annuleerDeleteModal');
const bevestigDeleteBtn    = document.getElementById('bevestigDelete');
const deleteModalTekst     = document.getElementById('deleteModalTekst');
const confirmNaamInput     = document.getElementById('confirmNaam');
const deleteError          = document.getElementById('deleteError');

let currentDeleteId   = null;
let currentDeleteNaam = null;

function openDeleteModal(id, naam) {
  currentDeleteId   = id;
  currentDeleteNaam = naam;
  deleteModalTekst.innerHTML = `Bent u zeker dat u de reservering van <strong>${naam}</strong> wilt verwijderen? Dit kan niet ongedaan worden gemaakt.`;
  confirmNaamInput.value = '';
  deleteError.style.display = 'none';
  if (deleteModalBackdrop) deleteModalBackdrop.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
  if (deleteModalBackdrop) deleteModalBackdrop.classList.remove('open');
  document.body.style.overflow = '';
  currentDeleteId   = null;
  currentDeleteNaam = null;
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
    if (!currentDeleteId || !currentDeleteNaam) return;

    const invoer = confirmNaamInput.value.trim();

    if (invoer.toLowerCase() !== currentDeleteNaam.toLowerCase()) {
      deleteError.textContent = 'De ingevoerde naam komt niet overeen. De reservering is NIET verwijderd.';
      deleteError.style.display = 'block';
      return;
    }

    try {
      const res = await fetch('delete_reservering.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: currentDeleteId, naam: invoer })
      });

      const data = await res.json();

      if (data.success) {
        // Verwijder de rij en card uit de DOM
        const rij = document.querySelector(`#tabelBody tr[data-res-id="${currentDeleteId}"]`);
        if (rij) rij.remove();

        const card = document.querySelector(`#cardContainer .res-card[data-res-id="${currentDeleteId}"]`);
        if (card) card.remove();

        closeDeleteModal();

        // Toon succesbericht
        const jsAlert = document.getElementById('jsSuccessAlert');
        const jsMsg   = document.getElementById('jsSuccessMessage');
        if (jsAlert && jsMsg) {
          jsMsg.textContent = 'Reservering succesvol verwijderd!';
          jsAlert.style.display = 'flex';
          setTimeout(() => (jsAlert.style.display = 'none'), 3000);
        }

        // Update counter
        filterReserveringen();
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

/* =========================================
   EDIT & DELETE BUTTON LISTENERS
   ========================================= */
function getResDataFromElement(el) {
  return {
    id:             el.dataset.resId,
    voornaam:       el.dataset.resVoornaam,
    tussenvoegsel:  el.dataset.resTussenvoegsel,
    achternaam:     el.dataset.resAchternaam,
    nummer:         el.dataset.resNummer,
    datum:          el.dataset.resDatum,
    tijd:           el.dataset.resTijd,
    status:         el.dataset.resStatus,
    naam:           el.dataset.resNaam
  };
}

// Edit knoppen - tabel
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    if (row) openEditModal(getResDataFromElement(row));
  });
});

// Edit knoppen - cards
document.querySelectorAll('.btn-edit-card').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('.res-card');
    if (card) openEditModal(getResDataFromElement(card));
  });
});

// Delete knoppen - tabel
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', () => {
    const row = btn.closest('tr');
    if (row) openDeleteModal(row.dataset.resId, row.dataset.resNaam);
  });
});

// Delete knoppen - cards
document.querySelectorAll('.btn-delete-card').forEach(btn => {
  btn.addEventListener('click', () => {
    const card = btn.closest('.res-card');
    if (card) openDeleteModal(card.dataset.resId, card.dataset.resNaam);
  });
});

/* =========================================
   ESCAPE TOETS
   ========================================= */
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    sluitModal();
    closeEditModal();
    closeDeleteModal();
  }
});

/* =========================================
   FLASH BERICHTEN AUTO-HIDE
   ========================================= */
['successAlert', 'errorAlert'].forEach(id => {
  const el = document.getElementById(id);
  if (el) setTimeout(() => (el.style.display = 'none'), 3000);
});