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
    echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Geen toegang — FitForFun</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,system-ui,sans-serif;background:#111318;color:#e6e8ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.container{text-align:center;max-width:420px}h1{font-size:24px;font-weight:600;margin-bottom:12px}p{font-size:14px;color:#8b90a7;margin-bottom:24px;line-height:1.5}a{display:inline-block;padding:10px 20px;background:#6b8cff;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:500}</style></head><body><div class="container"><h1>403 – Geen toegang</h1><p>U heeft geen rechten om lessen te beheren.</p><a href="../Informatie/home.php">Terug naar home</a></div></body></html>';
    exit();
}

$modalFouten = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nieuweLes'])) {
    $naam = trim($_POST['naam'] ?? '');
    $prijs = trim($_POST['prijs'] ?? '');
    $datum = trim($_POST['datum'] ?? '');
    $tijd = trim($_POST['tijd'] ?? '');
    $min_personen = trim($_POST['min_personen'] ?? '');
    $max_personen = trim($_POST['max_personen'] ?? '');
    $beschikbaarheid = trim($_POST['beschikbaarheid'] ?? 'Ingepland');

    if (empty($naam)) $modalFouten['naam'] = "Lesnaam is verplicht.";
    if ($prijs === '' || !is_numeric($prijs)) $modalFouten['prijs'] = "Geldige prijs is verplicht.";
    if (empty($datum)) $modalFouten['datum'] = "Datum is verplicht.";
    if (empty($tijd)) $modalFouten['tijd'] = "Tijd is verplicht.";
    if (empty($min_personen) || !is_numeric($min_personen)) $modalFouten['min_personen'] = "Min. personen verplicht.";
    if (empty($max_personen) || !is_numeric($max_personen)) $modalFouten['max_personen'] = "Max. personen verplicht.";
    if (!empty($min_personen) && !empty($max_personen) && $min_personen > $max_personen) {
        $modalFouten['min_personen'] = "Minimum mag niet groter zijn dan maximum.";
    }

    if (empty($modalFouten)) {
        try {
            $insert = $pdo->prepare("INSERT INTO les (Naam, Prijs, Datum, Tijd, MinAantalPersonen, MaxAantalPersonen, Beschikbaarheid, IsActief) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $insert->execute([$naam, $prijs, $datum, $tijd, $min_personen, $max_personen, $beschikbaarheid]);
            // Redirect om een dubbele stuur on refresh te voorkomen
            header('Location: Overzicht_lessen.php');
            exit();
        } catch (PDOException $e) {
            $modalFouten['db'] = "Database fout: " . $e->getMessage();
        }
    }
}

$sql = "SELECT l.Naam AS LesNaam, l.Prijs, l.Beschikbaarheid, l.Datum, l.Tijd, 
               r.Voornaam, r.Tussenvoegsel, r.Achternaam, r.Reserveringstatus
        FROM les l
        LEFT JOIN reservering r ON l.Datum = r.Datum AND l.Tijd = r.Tijd AND r.IsActief = 1
        WHERE l.IsActief = 1
        ORDER BY l.Datum, l.Tijd";

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

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

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

    <?php require_once __DIR__ . '/../includes/footer.php'; ?>

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