<?php
session_start();
require_once __DIR__ . '/../config.php';

$ingelogd = !empty($_SESSION['ingelogd']) && !empty($_SESSION['gebruiker_id']);
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
                    <li>
                        <a class="nav-link" href="home.php">Home</a>
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
                        <a class="nav-link" href="../../Reservering registratie/Reservering_Registratie.php">Reservering
                            beheren</a>
                    </li>
                    <li><a class="nav-link" href="../../Management Dashboard/Dashboard beheren/index.php">Dashboard
                            beheren</a></li>
                    <?php if ($ingelogd): ?>
                    <li><a class="nav-link nav-link-uitloggen" href="../uitloggen.php">Uitloggen</a></li>
                    <?php else: ?>
                    <li><a class="nav-link nav-link-uitloggen" href="../login.php">Inloggen</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="overlay"></div>
        </div>
    </header>

    <!-- Hoofdinhoud -->
    <main class="inhoud">

        <div class="titel-blok">
            <h1>Welkom bij FitForFun</h1>
            <p>Snel toegang tot workouts, challenges en je profiel</p>
        </div>

        <div class="home-kaarten">
            <a href="../Les registratie/Overzicht_lessen.php" class="home-kaart">
                <span class="home-kaart-icoon"><i class="fa-solid fa-dumbbell"></i></span>
                <h2>Workouts</h2>
                <p>Bekijk en beheer lessen en workouts</p>
            </a>
            <a href="#" class="home-kaart">
                <span class="home-kaart-icoon"><i class="fa-solid fa-trophy"></i></span>
                <h2>Challenges</h2>
                <p>Doe mee met challenges en behaal doelen</p>
            </a>
            <a href="../Account registratie/Account beheren/index.php" class="home-kaart">
                <span class="home-kaart-icoon"><i class="fa-solid fa-user"></i></span>
                <h2>Mijn profiel</h2>
                <p>Beheer je account en gegevens</p>
            </a>
        </div>

    </main>

    <footer class="footer">© 2026 FitForFun — Alle rechten voorbehouden</footer>

    <script src="home.js"></script>
</body>

</html>