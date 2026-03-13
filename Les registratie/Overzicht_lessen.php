<?php
session_start();
require_once '../config.php';

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT r.Voornaam, r.Tussenvoegsel, r.Achternaam, r.Datum, r.Tijd, r.Reserveringstatus, l.Naam AS LesNaam, l.Prijs, l.Beschikbaarheid
        FROM reservering r
        LEFT JOIN les l ON l.Datum = r.Datum AND l.Tijd = r.Tijd AND l.IsActief = 1
        WHERE r.IsActief = 1
        ORDER BY r.Datum, r.Tijd";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$lessen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aantalLessen = count($lessen);
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Les Beheren</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="Overzicht_lessen.css" />
</head>

<body>

    <header class="header">
        <div class="navbar-container">
            <a href="../Informatie/index.html" class="logo">FitForFun</a>
            <div class="hamburger"><i class="fa-solid fa-bars"></i></div>
            <nav class="navbar">
                <div class="close-menu"><i class="fa-solid fa-xmark"></i></div>
                <ul class="navbar-nav">
                    <li>
                        <a class="nav-link" href="../Informatie/index.html">Home</a>
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
                        <a class="nav-link" href="Overzicht_lessen.php">Les beheren</a>
                    </li>
                    <li>
                        <a class="nav-link" href="../../Reservering registratie/Reservering_Registratie.php">Reservering
                            beheren</a>
                    </li>
                    <li><a class="nav-link" href="../../Management Dashboard/Dashboard beheren/index.php">Dashboard
                            beheren</a></li>
                    <li><a class="nav-link nav-link-uitloggen" href="../uitloggen.php">Uitloggen</a></li>
                </ul>
            </nav>
            <div class="overlay"></div>
        </div>
    </header>

    <div class="wrapper">
  <h1>Lessen</h1>
  <p class="sub" id="countLine"><?= $aantalLessen ?> van <?= $aantalLessen ?> lessen zichtbaar</p>

  <div class="topbar">
    <input type="text" id="search" placeholder="Zoek op achternaam..."/>
    <select id="statusFilter">
      <option value="">Alle statussen</option>
      <option value="Ingepland">Ingepland</option>
      <option value="Niet gestart">Niet gestart</option>
      <option value="Gestart">Gestart</option>
      <option value="Geannuleerd">Geannuleerd</option>
    </select>
  </div>

  <table>
    <thead>
      <tr>
        <th>Voornaam</th>
        <th>Achternaam</th>
        <th>Les</th>
        <th>Prijs</th>
        <th>Datum</th>
        <th>Tijd</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="tabelBody">
      <?php foreach ($lessen as $les): ?>
        <?php
          $statusRaw   = $les['Reserveringstatus'] ?? $les['Beschikbaarheid'] ?? '';
          $statusClass = 'status-' . strtolower(str_replace(' ', '', $statusRaw));
          $achternaam  = $les['Achternaam'] ?? '';
          $voornaam  = $les['Voornaam'] ?? '';
        ?>
        <tr data-achternaam="<?= htmlspecialchars(strtolower($achternaam)) ?>"
            data-status="<?= htmlspecialchars($statusRaw) ?>">
          <td><?= htmlspecialchars($voornaam) ?></td>
          <td><?= htmlspecialchars($achternaam) ?></td>
          <td><?= htmlspecialchars($les['LesNaam'] ?? '—') ?></td>
          <td>€<?= number_format((float)($les['Prijs'] ?? 0), 2, ',', '.') ?></td>
          <td><?= htmlspecialchars(date('d-m-Y', strtotime($les['Datum']))) ?></td>
          <td><?= htmlspecialchars(substr($les['Tijd'], 0, 5)) ?></td>
          <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($statusRaw) ?></span></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="card-container" id="cardContainer">
    <?php foreach ($lessen as $les): ?>
      <?php
        $statusRaw   = $les['Reserveringstatus'] ?? $les['Beschikbaarheid'] ?? '';
        $statusClass = 'status-' . strtolower(str_replace(' ', '', $statusRaw));
        $achternaam  = $les['Achternaam'] ?? '';
      ?>
      <div class="les-card"
           data-achternaam="<?= htmlspecialchars(strtolower($achternaam)) ?>"
           data-status="<?= htmlspecialchars($statusRaw) ?>">
        <h3><?= htmlspecialchars($achternaam) ?></h3>
        <div class="prijs"><?= htmlspecialchars($les['LesNaam'] ?? '—') ?> — €<?= number_format((float)($les['Prijs'] ?? 0), 2, ',', '.') ?></div>
        <div class="card-row">
          <span class="card-label">Datum</span>
          <span><?= htmlspecialchars(date('d-m-Y', strtotime($les['Datum']))) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Tijd</span>
          <span><?= htmlspecialchars(substr($les['Tijd'], 0, 5)) ?></span>
        </div>
        <div class="card-row">
          <span class="card-label">Status</span>
          <span class="status <?= $statusClass ?>"><?= htmlspecialchars($statusRaw) ?></span>
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
                <h2 id="modalTitel">Nieuwe les toevoegen</h2>
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

            <form method="POST" action="Overzicht_lessen.php" novalidate>
                <input type="hidden" name="nieuweLes" value="1" />

                <div class="form-group">
                    <label for="naam">Lesnaam <span class="required">*</span></label>
                    <input type="text" id="naam" name="naam" maxlength="50" placeholder="Bijv. Yoga, Spinning..."
                        value="<?= htmlspecialchars($_POST['naam'] ?? '') ?>"
                        class="<?= isset($modalFouten['naam']) ? 'invalid' : '' ?>" />
                    <?php if (isset($modalFouten['naam'])): ?>
                    <span class="field-error"><?= htmlspecialchars($modalFouten['naam']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="prijs">Prijs (€) <span class="required">*</span></label>
                    <input type="number" id="prijs" name="prijs" min="0" step="0.01" placeholder="Bijv. 12.50"
                        value="<?= htmlspecialchars($_POST['prijs'] ?? '') ?>"
                        class="<?= isset($modalFouten['prijs']) ? 'invalid' : '' ?>" />
                    <?php if (isset($modalFouten['prijs'])): ?>
                    <span class="field-error"><?= htmlspecialchars($modalFouten['prijs']) ?></span>
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="min_personen">Min. personen <span class="required">*</span></label>
                        <input type="number" id="min_personen" name="min_personen" min="1" max="127" placeholder="3"
                            value="<?= htmlspecialchars($_POST['min_personen'] ?? '3') ?>"
                            class="<?= isset($modalFouten['min_personen']) ? 'invalid' : '' ?>" />
                        <?php if (isset($modalFouten['min_personen'])): ?>
                        <span class="field-error"><?= htmlspecialchars($modalFouten['min_personen']) ?></span>
                        <?php endif; ?>
                    </div> 
                    <div class="form-group">
                        <label for="max_personen">Max. personen <span class="required">*</span></label>
                        <input type="number" id="max_personen" name="max_personen" min="1" max="127" placeholder="9"
                            value="<?= htmlspecialchars($_POST['max_personen'] ?? '9') ?>"
                            class="<?= isset($modalFouten['max_personen']) ? 'invalid' : '' ?>" />
                        <?php if (isset($modalFouten['max_personen'])): ?>
                        <span class="field-error"><?= htmlspecialchars($modalFouten['max_personen']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="beschikbaarheid">Status</label>
                    <select id="beschikbaarheid" name="beschikbaarheid">
                        <?php foreach (['Ingepland', 'Niet gestart', 'Gestart', 'Geannuleerd'] as $opt): ?>
                        <option value="<?= $opt ?>"
                            <?= (($_POST['beschikbaarheid'] ?? 'Ingepland') === $opt) ? 'selected' : '' ?>>
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

<script>
  const totaal = <?= $aantalLessen ?>;
</script>
<script src="Overzicht_lessen.js"></script>

</body>
</html>