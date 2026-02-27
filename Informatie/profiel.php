<?php
require_once '../config.php';
session_start();

// Als de gebruiker niet is ingelogd, stuur door naar de loginpagina
if (!isset($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn profiel — FitForFun</title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- Header met navbar -->
<header class="header">
    <div class="navbar-container">

        <a href="home.php" class="logo">FitForFun</a>

        <div class="hamburger">
            <i class="fa-solid fa-bars"></i>
        </div>

        <nav class="navbar">
            <div class="close-menu">
                <i class="fa-solid fa-xmark"></i>
            </div>

            <ul class="navbar-nav">
                <li><a class="nav-link" href="home.php">Home</a></li>
                <li><a class="nav-link" href="profiel.php">Account beheren</a></li>
                <li><a class="nav-link" href="#">Medewerker beheren</a></li>
                <li><a class="nav-link" href="#">Lid beheren</a></li>
                <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">Reservering beheren</a></li>
                <li><a class="nav-link" href="home.php">Dashboard beheren</a></li>
            </ul>
        </nav>

        <div class="overlay"></div>
    </div>
</header>

<!-- Hoofdinhoud -->
<div class="inhoud">

    <div class="titel-blok">
        <h1>Mijn profiel</h1>
        <p>Je accountgegevens</p>
    </div>

    <div class="kaart profiel-kaart">
        <div class="kaart-icoon">
            <i class="fa-solid fa-user"></i>
        </div>
        <h2><?= htmlspecialchars($_SESSION['gebruiker_naam'] ?? 'Gebruiker') ?></h2>
        <p>Ingelogd als lid van FitForFun</p>
        <a href="home.php" class="kaart-link">← Terug naar home</a>
    </div>

</div>

<footer class="footer">
    © 2026 FitForFun — Alle rechten voorbehouden
</footer>

<script src="home.js"></script>
</body>
</html>
