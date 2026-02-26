<?php
require_once '../config.php';

session_start();


if (!isset($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT Naam, Datum, Tijd, MinAantalPersonen, MaxAantalPersonen, Beschikbaarheid, Prijs
        FROM les
        WHERE Isactief = 1
        ORDER BY Datum, Tijd";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$lessen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aantalLessen = count($lessen);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesoverzicht</title>
    <link rel="stylesheet" href="Overzicht_lessen.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Overlay -->
<div class="overlay"></div>

<!-- Header met navbar -->
<header class="header">
    <div class="navbar-container">

        <a href="#" class="logo">FitForFun</a>

        <button class="hamburger">
            <i class="fa-solid fa-bars"></i>
        </button>

        <nav class="navbar">
            <button class="close-menu">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <ul class="navbar-nav">
                <li><a class="nav-link" href="#">📋 Lessen</a></li>
                <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">📅 Reserveringen</a></li>
                <li><a class="nav-link" href="../uitloggen.php">🚪 Uitloggen</a></li>
            </ul>
        </nav>

    </div>
</header>

<!-- Hoofdinhoud -->
<div class="inhoud">

    <!-- Titel -->
    <div class="titel-blok">
        <h1>Geplande Lessen</h1>
        <p>Overzicht van alle actieve lessen</p>
    </div>

    <!-- Kaartje aantal lessen -->
    <div class="stat-kaart">
        <div class="getal"><?= $aantalLessen ?></div>
        <div class="label">Actieve lessen</div>
    </div>

    <!-- Zoekbalk -->
    <div class="zoek-wrapper">
        <input type="text" id="zoekbalk" placeholder="🔍  Zoek op lesnaam..." onkeyup="zoekLes()">
    </div>

    <!-- Tabel -->
    <div class="table-wrapper">
    <table id="lessenTabel">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Min / Max</th>
                <th>Status</th>
                <th>Prijs</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($aantalLessen > 0): ?>
                <?php foreach ($lessen as $les): ?>
                <tr>
                    <td data-label="Naam"><?= htmlspecialchars($les['Naam']) ?></td>
                    <td data-label="Datum"><?= htmlspecialchars($les['Datum']) ?></td>
                    <td data-label="Tijd"><?= htmlspecialchars(substr($les['Tijd'], 0, 5)) ?></td>
                    <td data-label="Min / Max"><?= (int)$les['MinAantalPersonen'] ?> – <?= (int)$les['MaxAantalPersonen'] ?></td>
                    <td data-label="Status"><?= htmlspecialchars($les['Beschikbaarheid']) ?></td>
                    <td data-label="Prijs">€<?= number_format((float)$les['Prijs'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Geen lessen gevonden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>

<script src="Overzicht_lessen.js"></script>

</body>
</html>