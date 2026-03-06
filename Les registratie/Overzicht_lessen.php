<?php
session_start();
require_once '../config.php';

<<<<<<< HEAD
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT Naam, Datum, Tijd, MinAantalPersonen, MaxAantalPersonen, Beschikbaarheid, Prijs
=======
$sql = "SELECT Naam, Prijs, Datum, Tijd, Beschikbaarheid
>>>>>>> 61d7e87ac8244bcebf92044f513a32be7a62a6a0
        FROM les
        WHERE IsActief = 1
        ORDER BY Datum, Tijd";

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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link rel="stylesheet" href="Overzicht_lessen.css"/>
</head>
<body>

<header class="header">
  <div class="navbar-container">
    <a href="../Informatie/home.php" class="logo">FitForFun</a>
    <div class="hamburger"><i class="fa-solid fa-bars"></i></div>
    <nav class="navbar">
      <div class="close-menu"><i class="fa-solid fa-xmark"></i></div>
      <ul class="navbar-nav">
        <li><a class="nav-link" href="../Informatie/home.php">Home</a></li>
        <li><a class="nav-link" href="../Account registratie/Account beheren/index.html">Account beheren</a></li>
        <li><a class="nav-link" href="../Medewerker registratie/Medewerker beheren/index.html">Medewerker beheren</a></li>
        <li><a class="nav-link" href="../Lid registratie/index.php">Lid beheren</a></li>
        <li><a class="nav-link" href="Overzicht_lessen.php">Les beheren</a></li>
        <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">Reservering beheren</a></li>
        <li><a class="nav-link" href="../Management Dashboard/Dashboard beheren/index.html">Dashboard beheren</a></li>
      </ul>
    </nav>
    <div class="overlay"></div>
  </div>
</header>

<div class="wrapper">
  <h1>Lessen</h1>
  <p class="sub" id="countLine"><?= $aantalLessen ?> van <?= $aantalLessen ?> lessen zichtbaar</p>

  <div class="topbar">
    <input type="text" id="search" placeholder="Zoek op naam..."/>
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
        <th>Naam</th>
        <th>Prijs</th>
        <th>Datum</th>
        <th>Tijd</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody id="tabelBody">
      <?php foreach ($lessen as $les): ?>
        <?php
          $statusRaw   = $les['Beschikbaarheid'];
          $statusClass = 'status-' . strtolower(str_replace(' ', '', $statusRaw));
        ?>
        <tr data-naam="<?= htmlspecialchars(strtolower($les['Naam'])) ?>"
            data-status="<?= htmlspecialchars($statusRaw) ?>">
          <td><?= htmlspecialchars($les['Naam']) ?></td>
          <td>€<?= number_format($les['Prijs'], 2, ',', '.') ?></td>
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
        $statusRaw   = $les['Beschikbaarheid'];
        $statusClass = 'status-' . strtolower(str_replace(' ', '', $statusRaw));
      ?>
      <div class="les-card"
           data-naam="<?= htmlspecialchars(strtolower($les['Naam'])) ?>"
           data-status="<?= htmlspecialchars($statusRaw) ?>">
        <h3><?= htmlspecialchars($les['Naam']) ?></h3>
        <div class="prijs">€<?= number_format($les['Prijs'], 2, ',', '.') ?></div>
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

<script>
  const totaal = <?= $aantalLessen ?>;
</script>
<script src="Overzicht_lessen.js"></script>

</body>
</html>