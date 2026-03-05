<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
                <li><a class="nav-link" href="../Account registratie/Account beheren/index.html">Account beheren</a></li>
                <li><a class="nav-link" href="../Medewerker registratie/Medewerker beheren/index.html">Medewerker beheren</a></li>
                <li><a class="nav-link" href="../Lid registratie/">Lid beheren</a></li>
                <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">Reservering beheren</a></li>
                <li><a class="nav-link" href="../Management Dashboard/Dashboard beheren/index.html">Dashboard beheren</a></li>
            </ul>
        </nav>

        <div class="overlay"></div>
    </div>
</header>

<!-- Hoofdinhoud -->
<div class="inhoud">

    <div class="titel-blok">
        <h1>Welkom bij FitForFun!</h1>
        <p>Kies waar je naartoe wilt — snel toegang tot je workouts, challenges en profiel</p>
    </div>

    <div class="kaarten-grid">
        <a href="../Les registratie/Overzicht_lessen.php" class="kaart kaart-workouts">
            <div class="kaart-icoon">
                <i class="fa-solid fa-dumbbell"></i>
            </div>
            <h2>Workouts</h2>
            <p>Bekijk en reserveer groepslessen zoals Yoga, Spinning en Zumba</p>
            <span class="kaart-link">Ga naar workouts <i class="fa-solid fa-arrow-right"></i></span>
        </a>

        <a href="#" class="kaart kaart-challenges">
            <div class="kaart-icoon">
                <i class="fa-solid fa-trophy"></i>
            </div>
            <h2>Challenges</h2>
            <p>Doe mee met uitdagingen en behaal je doelen</p>
            <span class="kaart-link">Binnenkort beschikbaar <i class="fa-solid fa-clock"></i></span>
        </a>

        <a href="../Account registratie/Account beheren/index.html" class="kaart kaart-profiel">
            <div class="kaart-icoon">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2>Accounten overzicht</h2>
            <p>Bekijk het overzicht van alle accounts</p>
            <span class="kaart-link">Ga naar accounten overzicht <i class="fa-solid fa-arrow-right"></i></span>
        </a>
    </div>

</div>

    <footer class="footer">© 2026 FitForFun — Alle rechten voorbehouden</footer>

<script src="home.js"></script>
</body>
</html>
