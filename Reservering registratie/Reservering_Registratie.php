<?php
session_start();
require_once '../config.php';

<<<<<<< HEAD
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

// Haal alle actieve reserveringen op
=======
>>>>>>> 61d7e87ac8244bcebf92044f513a32be7a62a6a0
$sql = "SELECT Voornaam, Tussenvoegsel, Achternaam, Datum, Tijd, Reserveringstatus
        FROM reservering
        WHERE IsActief = 1
        ORDER BY Datum, Tijd";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reserveringen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aantalReserveringen = count($reserveringen);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reservering Beheren</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link rel="stylesheet" href="Reservering_Registratie.css"/>
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
        <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
        <li><a class="nav-link" href="Reservering_Registratie.php">Reservering beheren</a></li>
        <li><a class="nav-link" href="../Management Dashboard/Dashboard beheren/index.html">Dashboard beheren</a></li>
      </ul>
    </nav>
    <div class="overlay"></div>
  </div>
</header>

<div class="wrapper">
  <h1>Reserveringen</h1>
  <p class="sub" id="countLine"><?= $aantalReserveringen ?> van <?= $aantalReserveringen ?> reserveringen zichtbaar</p>

  <div class="topbar">
    <input type="text" id="search" placeholder="Zoek op naam..."/>
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
      <div class="res-card"
           data-naam="<?= htmlspecialchars(strtolower($naam)) ?>"
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

<script>
  const totaal = <?= $aantalReserveringen ?>;
</script>
<script src="Reservering_Registratie.js"></script>

</body>
</html>