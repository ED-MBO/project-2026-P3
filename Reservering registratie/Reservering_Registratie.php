<?php
require_once '../config.php';
session_start();

// Als de medewerker niet is ingelogd, stuur door naar de loginpagina
if (!isset($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

// Haal alle actieve reserveringen op
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserveringen Overzicht</title>
    <link rel="stylesheet" href="Reservering_Registratie.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Header met navbar -->
<header class="header">
    <div class="navbar-container">

        <a href="../Informatie/home.php" class="logo">FitForFun</a>

        <div class="hamburger">
            <i class="fa-solid fa-bars"></i>
        </div>

        <nav class="navbar">
            <div class="close-menu">
                <i class="fa-solid fa-xmark"></i>
            </div>

            <ul class="navbar-nav">
                <li><a class="nav-link" href="../Informatie/home.php">Home</a></li>
                <li><a class="nav-link" href="../Informatie/profiel.php">Account beheren</a></li>
                <li><a class="nav-link" href="#">Medewerker beheren</a></li>
                <li><a class="nav-link" href="#">Lid beheren</a></li>
                <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                <li><a class="nav-link" href="Reservering_Registratie.php">Reservering beheren</a></li>
                <li><a class="nav-link" href="../Informatie/home.php">Dashboard beheren</a></li>
            </ul>
        </nav>

        <div class="overlay"></div>
    </div>
</header>

<!-- Hoofdinhoud -->
<div class="inhoud">

    <div class="titel-blok">
        <h1>Reserveringen</h1>
        <p>Overzicht van alle reserveringen</p>
    </div>

    <div class="stat-kaart">
        <div class="getal"><?= $aantalReserveringen ?></div>
        <div class="label">Reserveringen</div>
    </div>

    <div class="zoek-wrapper">
        <input type="text" id="zoekbalk" placeholder="🔍  Zoek op naam..." onkeyup="zoekReservering()">
    </div>

    <div class="table-wrapper">
    <table id="reserveringenTabel">
        <thead>
            <tr>
                <th>Naam lid</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($aantalReserveringen > 0): ?>
                <?php foreach ($reserveringen as $res):
                    // Naam samenstellen met tussenvoegsel
                    $naam = $res['Voornaam'];
                    if (!empty(trim($res['Tussenvoegsel']))) {
                        $naam .= ' ' . $res['Tussenvoegsel'];
                    }
                    $naam .= ' ' . $res['Achternaam'];
                ?>
                <tr>
                    <td data-label="Naam lid"><?= htmlspecialchars($naam) ?></td>
                    <td data-label="Datum"><?= htmlspecialchars($res['Datum']) ?></td>
                    <td data-label="Tijd"><?= htmlspecialchars(substr($res['Tijd'], 0, 5)) ?></td>
                    <td data-label="Status">
                        <?php
                        $status = $res['Reserveringstatus'];
                        if ($status === 'Gereserveerd') {
                            echo '<span class="badge groen">Gereserveerd</span>';
                        } elseif ($status === 'Vrij') {
                            echo '<span class="badge grijs">Vrij</span>';
                        } else {
                            echo '<span class="badge grijs">' . htmlspecialchars($status) . '</span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="leeg">Er zijn momenteel geen reserveringen.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>

<footer class="footer">
    © 2026 FitForFun — Alle rechten voorbehouden
</footer>

<script src="Reservering_Registratie.js"></script>
</body>
</html>