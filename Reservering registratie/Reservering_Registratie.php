<?php
session_start();
require_once '../config.php';
 
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
$sql = "SELECT Voornaam, Tussenvoegsel, Achternaam, Datum, Tijd, Reserveringstatus
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

    <header class="header">
        <div class="navbar-container">
            <a href="../Informatie/home.php" class="logo">FitForFun</a>
            <div class="hamburger"><i class="fa-solid fa-bars"></i></div>
            <nav class="navbar">
                <div class="close-menu"><i class="fa-solid fa-xmark"></i></div>
                <ul class="navbar-nav">
                    <li>
                        <a class="nav-link" href="../../Informatie/home.php">Home</a>
                    </li>
                    <li>
                        <a class="nav-link" href="../../Account registratie/Account beheren/index.php">Account
                            beheren</a>
                    </li>
                    <li><a class="nav-link" href="../Medewerker registratie/Medewerker beheren/index.php">Medewerker
                            beheren</a>
                    </li>
                    <li>
                        <a class="nav-link" href="../../Lid registratie/index.php">Lid beheren</a>
                    </li>
                    <li>
                        <a class="nav-link" href="../../Les registratie/Overzicht_lessen.php">Les beheren</a>
                    </li>
                    <li>
                        <a class="nav-link" href="Reservering_Registratie.php">Reservering
                            beheren</a>
                    </li>
                    <li><a class="nav-link" href="../../Management Dashboard/Dashboard beheren/index.php">Dashboard
                            beheren</a></li>
                </ul>
            </nav>
            <div class="overlay"></div>
        </div>
    </header>

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

        <?php if ($modalSucces): ?>
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i>
            Reservering is succesvol aangemaakt en toegevoegd aan de tabel.
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
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Status</th>
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
                    data-status="<?= htmlspecialchars($status) ?>">
                    <td><?= htmlspecialchars($naam) ?></td>
                    <td><?= htmlspecialchars(date('d-m-Y', strtotime($res['Datum']))) ?></td>
                    <td><?= htmlspecialchars(substr($res['Tijd'], 0, 5)) ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span></td>
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
                data-status="<?= htmlspecialchars($status) ?>">
                <h3><?= htmlspecialchars($naam) ?></h3>
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

    <footer class="footer">© 2026 FitForFun — Alle rechten voorbehouden</footer>

    <!-- ===================== MODAL ===================== -->
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
    <!-- ================================================= -->

    <script>
    const totaal = <?= $aantalReserveringen ?>;
    const modalOpenBijLaad = <?= !empty($modalFouten) ? 'true' : 'false' ?>;
    </script>
    <script src="Reservering_Registratie.js"></script>

</body>

</html>