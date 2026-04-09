<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}
if (empty($_SESSION['rol'])) {
    $stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
    $stmtRol->execute([$_SESSION['gebruiker_id']]);
    $_SESSION['rol'] = $stmtRol->fetchColumn() ?: 'Lid';
}
$rol = $_SESSION['rol'] ?? 'Lid';
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator']);

if (!$isMedewerkerOfAdmin) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Geen toegang — FitForFun</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,system-ui,sans-serif;background:#111318;color:#e6e8ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.container{text-align:center;max-width:420px}h1{font-size:24px;font-weight:600;margin-bottom:12px}p{font-size:14px;color:#8b90a7;margin-bottom:24px;line-height:1.5}a{display:inline-block;padding:10px 20px;background:#6b8cff;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:500}</style></head><body><div class="container"><h1>403 – Geen toegang</h1><p>U heeft geen rechten om reserveringen te beheren.</p><a href="../Informatie/home.php">Terug naar home</a></div></body></html>';
    exit();
}

// Flash berichten uitlezen en direct wissen
$flashSucces = $_SESSION['flash_succes'] ?? null;
$flashFout   = $_SESSION['flash_fout']   ?? null;
unset($_SESSION['flash_succes'], $_SESSION['flash_fout']);

// --- Verwerk formulier POST ---
$modalFouten = [];
$modalSucces = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nieuweReservering'])) {
  $voornaam   = trim($_POST['voornaam']         ?? '');
  $tussen     = trim($_POST['tussenvoegsel']    ?? '');
  $achternaam = trim($_POST['achternaam']       ?? '');
  $nummer     = trim($_POST['nummer']           ?? '');
  $datum      = trim($_POST['datum']            ?? '');
  $tijd       = trim($_POST['tijd']             ?? '');
  $status     = $_POST['reserveringstatus']     ?? 'Gereserveerd';

  if ($voornaam === '')                                          $modalFouten['voornaam']   = 'Voornaam is verplicht.';
  elseif (strlen($voornaam) > 50)                               $modalFouten['voornaam']   = 'Maximaal 50 tekens.';
  if ($achternaam === '')                                        $modalFouten['achternaam'] = 'Achternaam is verplicht.';
  elseif (strlen($achternaam) > 50)                             $modalFouten['achternaam'] = 'Maximaal 50 tekens.';
  if ($nummer === '' || !ctype_digit($nummer) || (int)$nummer < 1)
                                                                $modalFouten['nummer']     = 'Voer een geldig nummer in.';
  if ($datum === '')                                             $modalFouten['datum']      = 'Datum is verplicht.';
  if ($tijd === '')                                              $modalFouten['tijd']       = 'Tijd is verplicht.';
  if (!in_array($status, ['Gereserveerd', 'Vrij']))             $modalFouten['status']     = 'Ongeldige status.';

  if (empty($modalFouten)) {
    try {
      $stmt = $pdo->prepare(
        "INSERT INTO reservering (Voornaam, Tussenvoegsel, Achternaam, Nummer, Datum, Tijd, Reserveringstatus, IsActief)
         VALUES (:voornaam, :tussen, :achternaam, :nummer, :datum, :tijd, :status, 1)"
      );
      $stmt->execute([
        ':voornaam'   => $voornaam,
        ':tussen'     => $tussen,
        ':achternaam' => $achternaam,
        ':nummer'     => (int)$nummer,
        ':datum'      => $datum,
        ':tijd'       => $tijd,
        ':status'     => $status,
      ]);
      $modalSucces = true;
    } catch (PDOException $e) {
      $modalFouten['db'] = 'Databasefout. Probeer opnieuw.';
    }
  }
}

// --- Haal reserveringen op (opnieuw zodat nieuwe direct zichtbaar is) ---
$sql = "SELECT Id, Voornaam, Tussenvoegsel, Achternaam, Nummer, Datum, Tijd, Reserveringstatus
        FROM reservering
        WHERE IsActief = 1
        ORDER BY Datum, Tijd";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$reserveringen       = $stmt->fetchAll(PDO::FETCH_ASSOC);
$aantalReserveringen = count($reserveringen);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reservering Beheren</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="Reservering_Registratie.css" />
</head>

<body>

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="wrapper">

        <div class="heading-row">
            <div>
                <h1>Reserveringen</h1>
                <p class="sub" id="countLine"><?= $aantalReserveringen ?> van <?= $aantalReserveringen ?> reserveringen
                    zichtbaar</p>
            </div>
            <button class="btn-primary" id="openModal">
                <i class="fa-solid fa-plus"></i> Nieuwe reservering
            </button>
        </div>

        <!-- Flash berichten -->
        <div class="alert-success" id="jsSuccessAlert" style="display: none; margin-top: 16px;">
            <i class="fa-solid fa-circle-check"></i>
            <span id="jsSuccessMessage"></span>
        </div>

        <?php if ($flashSucces || $modalSucces): ?>
        <div class="alert-success" id="successAlert">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($flashSucces ?: 'Reservering is succesvol aangemaakt en toegevoegd aan de tabel.') ?>
        </div>
        <?php endif; ?>

        <?php if ($flashFout): ?>
        <div class="alert-error" id="errorAlert">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($flashFout) ?>
        </div>
        <?php endif; ?>

        <div class="topbar">
            <input type="text" id="search" placeholder="Zoek op naam..." />
            <select id="statusFilter">
                <option value="">Alle statussen</option>
                <option value="Gereserveerd">Gereserveerd</option>
                <option value="Vrij">Vrij</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Naam lid</th>
                    <th>Nummer</th>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Status</th>
                    <th>Wijzigen</th>
                    <th>Verwijderen</th>
                </tr>
            </thead>
            <tbody id="tabelBody">
                <?php foreach ($reserveringen as $res):
        $naam = $res['Voornaam'];
        if (!empty(trim($res['Tussenvoegsel']))) {
          $naam .= ' ' . $res['Tussenvoegsel'];
        }
        $naam .= ' ' . $res['Achternaam'];

        $status      = $res['Reserveringstatus'];
        $statusClass = 'status-' . strtolower(str_replace(' ', '', $status));
      ?>
                <tr data-naam="<?= htmlspecialchars(strtolower($naam)) ?>"
                    data-status="<?= htmlspecialchars($status) ?>"
                    data-res-id="<?= (int)($res['Id'] ?? 0) ?>"
                    data-res-voornaam="<?= htmlspecialchars($res['Voornaam'] ?? '') ?>"
                    data-res-tussenvoegsel="<?= htmlspecialchars($res['Tussenvoegsel'] ?? '') ?>"
                    data-res-achternaam="<?= htmlspecialchars($res['Achternaam'] ?? '') ?>"
                    data-res-nummer="<?= htmlspecialchars($res['Nummer'] ?? '') ?>"
                    data-res-datum="<?= htmlspecialchars($res['Datum'] ?? '') ?>"
                    data-res-tijd="<?= htmlspecialchars($res['Tijd'] ?? '') ?>"
                    data-res-status="<?= htmlspecialchars($status) ?>"
                    data-res-naam="<?= htmlspecialchars($naam) ?>">
                    <td><?= htmlspecialchars($naam) ?></td>
                    <td><?= htmlspecialchars($res['Nummer'] ?? '') ?></td>
                    <td><?= htmlspecialchars(date('d-m-Y', strtotime($res['Datum']))) ?></td>
                    <td><?= htmlspecialchars(substr($res['Tijd'], 0, 5)) ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
                    <td>
                        <button class="btn-action-white btn-edit"
                                data-id="<?= (int)($res['Id'] ?? 0) ?>">Wijzigen</button>
                    </td>
                    <td>
                        <button class="btn-action-white btn-delete"
                                data-id="<?= (int)($res['Id'] ?? 0) ?>">Verwijderen</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="cardContainer">
            <?php foreach ($reserveringen as $res):
      $naam = $res['Voornaam'];
      if (!empty(trim($res['Tussenvoegsel']))) {
        $naam .= ' ' . $res['Tussenvoegsel'];
      }
      $naam .= ' ' . $res['Achternaam'];

      $status      = $res['Reserveringstatus'];
      $statusClass = 'status-' . strtolower(str_replace(' ', '', $status));
    ?>
            <div class="res-card" data-naam="<?= htmlspecialchars(strtolower($naam)) ?>"
                data-status="<?= htmlspecialchars($status) ?>"
                data-res-id="<?= (int)($res['Id'] ?? 0) ?>"
                data-res-voornaam="<?= htmlspecialchars($res['Voornaam'] ?? '') ?>"
                data-res-tussenvoegsel="<?= htmlspecialchars($res['Tussenvoegsel'] ?? '') ?>"
                data-res-achternaam="<?= htmlspecialchars($res['Achternaam'] ?? '') ?>"
                data-res-nummer="<?= htmlspecialchars($res['Nummer'] ?? '') ?>"
                data-res-datum="<?= htmlspecialchars($res['Datum'] ?? '') ?>"
                data-res-tijd="<?= htmlspecialchars($res['Tijd'] ?? '') ?>"
                data-res-status="<?= htmlspecialchars($status) ?>"
                data-res-naam="<?= htmlspecialchars($naam) ?>">
                <h3><?= htmlspecialchars($naam) ?>
                  <div style="float: right;">
                    <button class="btn-edit-card" data-id="<?= (int)($res['Id'] ?? 0) ?>" title="Reservering wijzigen" style="border: none; background: transparent; cursor: pointer; color: #6b8cff; font-size: 16px; margin-right: 4px;">
                      <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn-delete-card" data-id="<?= (int)($res['Id'] ?? 0) ?>" title="Reservering verwijderen" style="border: none; background: transparent; cursor: pointer; color: #ff6b6b; font-size: 16px;">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                </h3>
                <div class="card-row">
                    <span class="card-label">Nummer</span>
                    <span><?= htmlspecialchars($res['Nummer'] ?? '') ?></span>
                </div>
                <div class="card-row">
                    <span class="card-label">Datum</span>
                    <span><?= htmlspecialchars(date('d-m-Y', strtotime($res['Datum']))) ?></span>
                </div>
                <div class="card-row">
                    <span class="card-label">Tijd</span>
                    <span><?= htmlspecialchars(substr($res['Tijd'], 0, 5)) ?></span>
                </div>
                <div class="card-row">
                    <span class="card-label">Status</span>
                    <span class="status <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div id="emptyState" class="empty" style="display:none">
            Geen resultaten. Probeer een andere zoekterm.
        </div>
    </div>

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

    <!-- ===================== MODAL: NIEUWE RESERVERING ===================== -->
    <div class="modal-backdrop <?= !empty($modalFouten) ? 'open' : '' ?>" id="modalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitel">

            <div class="modal-header">
                <h2 id="modalTitel">Nieuwe reservering toevoegen</h2>
                <button class="modal-close" id="sluitModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <?php if (!empty($modalFouten['db'])): ?>
            <div class="modal-db-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?= htmlspecialchars($modalFouten['db']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="Reservering_Registratie.php" novalidate>
                <input type="hidden" name="nieuweReservering" value="1" />

                <div class="form-row">
                    <div class="form-group">
                        <label for="voornaam">Voornaam <span class="required">*</span></label>
                        <input type="text" id="voornaam" name="voornaam" maxlength="50" placeholder="Bijv. Laura"
                            value="<?= htmlspecialchars($_POST['voornaam'] ?? '') ?>"
                            class="<?= isset($modalFouten['voornaam']) ? 'invalid' : '' ?>" />
                        <?php if (isset($modalFouten['voornaam'])): ?>
                        <span class="field-error"><?= htmlspecialchars($modalFouten['voornaam']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="tussenvoegsel">Tussenvoegsel</label>
                        <input type="text" id="tussenvoegsel" name="tussenvoegsel" maxlength="10" placeholder="Bijv. de"
                            value="<?= htmlspecialchars($_POST['tussenvoegsel'] ?? '') ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="achternaam">Achternaam <span class="required">*</span></label>
                    <input type="text" id="achternaam" name="achternaam" maxlength="50" placeholder="Bijv. Klein"
                        value="<?= htmlspecialchars($_POST['achternaam'] ?? '') ?>"
                        class="<?= isset($modalFouten['achternaam']) ? 'invalid' : '' ?>" />
                    <?php if (isset($modalFouten['achternaam'])): ?>
                    <span class="field-error"><?= htmlspecialchars($modalFouten['achternaam']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nummer">Nummer <span class="required">*</span></label>
                    <input type="number" id="nummer" name="nummer" min="1" placeholder="Bijv. 201"
                        value="<?= htmlspecialchars($_POST['nummer'] ?? '') ?>"
                        class="<?= isset($modalFouten['nummer']) ? 'invalid' : '' ?>" />
                    <?php if (isset($modalFouten['nummer'])): ?>
                    <span class="field-error"><?= htmlspecialchars($modalFouten['nummer']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="datum">Datum <span class="required">*</span></label>
                        <input type="date" id="datum" name="datum"
                            value="<?= htmlspecialchars($_POST['datum'] ?? '') ?>"
                            class="<?= isset($modalFouten['datum']) ? 'invalid' : '' ?>" />
                        <?php if (isset($modalFouten['datum'])): ?>
                        <span class="field-error"><?= htmlspecialchars($modalFouten['datum']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="tijd">Tijd <span class="required">*</span></label>
                        <input type="time" id="tijd" name="tijd" value="<?= htmlspecialchars($_POST['tijd'] ?? '') ?>"
                            class="<?= isset($modalFouten['tijd']) ? 'invalid' : '' ?>" />
                        <?php if (isset($modalFouten['tijd'])): ?>
                        <span class="field-error"><?= htmlspecialchars($modalFouten['tijd']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reserveringstatus">Status</label>
                    <select id="reserveringstatus" name="reserveringstatus">
                        <?php foreach (['Gereserveerd', 'Vrij'] as $opt): ?>
                        <option value="<?= $opt ?>"
                            <?= (($_POST['reserveringstatus'] ?? 'Gereserveerd') === $opt) ? 'selected' : '' ?>>
                            <?= $opt ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Opslaan
                    </button>
                    <button type="button" class="btn-secondary" id="annuleerModal">Annuleren</button>
                </div>
            </form>

        </div>
    </div>

    <!-- ===================== MODAL: RESERVERING WIJZIGEN ===================== -->
    <div class="modal-backdrop" id="editModalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitel">
            <div class="modal-header">
                <h2 id="editModalTitel">Reservering wijzigen</h2>
                <button class="modal-close" id="sluitEditModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="edit_reservering.php" id="editResForm" novalidate>
                <input type="hidden" id="editResId" name="reserveringId" />

                <div class="form-row">
                    <div class="form-group">
                        <label for="editVoornaam">Voornaam <span class="required">*</span></label>
                        <input type="text" id="editVoornaam" name="voornaam" maxlength="50" placeholder="Bijv. Laura" required />
                        <span class="field-error" id="editVoornaamError" style="display:none"></span>
                    </div>
                    <div class="form-group">
                        <label for="editTussenvoegsel">Tussenvoegsel</label>
                        <input type="text" id="editTussenvoegsel" name="tussenvoegsel" maxlength="10" placeholder="Bijv. de" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="editAchternaam">Achternaam <span class="required">*</span></label>
                    <input type="text" id="editAchternaam" name="achternaam" maxlength="50" placeholder="Bijv. Klein" required />
                    <span class="field-error" id="editAchternaamError" style="display:none"></span>
                </div>

                <div class="form-group">
                    <label for="editNummer">Nummer <span class="required">*</span></label>
                    <input type="number" id="editNummer" name="nummer" min="1" placeholder="Bijv. 201" required />
                    <span class="field-error" id="editNummerError" style="display:none"></span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editDatum">Datum <span class="required">*</span></label>
                        <input type="date" id="editDatum" name="datum" required />
                        <span class="field-error" id="editDatumError" style="display:none"></span>
                    </div>
                    <div class="form-group">
                        <label for="editTijd">Tijd <span class="required">*</span></label>
                        <input type="time" id="editTijd" name="tijd" required />
                        <span class="field-error" id="editTijdError" style="display:none"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editReserveringstatus">Status</label>
                    <select id="editReserveringstatus" name="reserveringstatus">
                        <option value="Gereserveerd">Gereserveerd</option>
                        <option value="Vrij">Vrij</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Wijzigingen opslaan
                    </button>
                    <button type="button" class="btn-secondary" id="annuleerEditModal">Annuleren</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== MODAL: RESERVERING VERWIJDEREN ===================== -->
    <div class="modal-backdrop" id="deleteModalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitel">
            <div class="modal-header">
                <h2 id="deleteModalTitel">Reservering verwijderen</h2>
                <button class="modal-close" id="sluitDeleteModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteModalTekst" style="font-size: 14px; margin-bottom: 20px; color: var(--color-text-primary);"></p>
                <div class="form-group">
                    <label for="confirmAchternaam">Typ de achternaam ter bevestiging <span class="required">*</span></label>
                    <input type="text" id="confirmAchternaam" placeholder="Achternaam invullen..." required />
                    <div id="deleteError" style="color: #f87171; font-size: 12px; margin-top: 5px; display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary btn-danger" id="bevestigDelete">
                    <i class="fa-solid fa-trash-can"></i> Definitief verwijderen
                </button>
                <button type="button" class="btn-secondary" id="annuleerDeleteModal">
                    Annuleren
                </button>
            </div>
        </div>
    </div>

    <script>
    const totaal = <?= $aantalReserveringen ?>;
    const modalOpenBijLaad = <?= !empty($modalFouten) ? 'true' : 'false' ?>;
    </script>
    <script src="Reservering_Registratie.js?v=<?= time() ?>"></script>

</body>

</html>