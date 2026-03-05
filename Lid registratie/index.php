<?php
session_start();
require_once __DIR__ . '/../config.php';

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leden beheren — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="navbar-container">
        <a href="../Informatie/home.php" class="logo">FitForFun</a>
        <div class="hamburger" id="hamburger">
            <i class="fa-solid fa-bars"></i>
        </div>
        <nav class="navbar" id="navbar">
            <span class="close-menu" id="closeMenu">&times;</span>
            <ul class="navbar-nav">
                <li><a class="nav-link" href="../Informatie/home.php">Home</a></li>
                <li><a class="nav-link" href="../Account registratie/Account beheren/index.html">Account beheren</a></li>
                <li><a class="nav-link" href="../Medewerker registratie/Medewerker beheren/index.html">Medewerker beheren</a></li>
                <li><a class="nav-link" href="index.php">Lid beheren</a></li>
                <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">Reservering beheren</a></li>
                <li><a class="nav-link" href="../Management Dashboard/Dashboard beheren/index.html">Dashboard beheren</a></li>
                <li><a class="nav-link nav-link-uitloggen" href="../uitloggen.php">Uitloggen</a></li>
            </ul>
        </nav>
        <div class="overlay" id="overlay"></div>
    </div>
</header>

<main class="wrapper">
    <h1>Leden beheren</h1>
    <div class="sub" id="countLine">0 leden</div>
    <div class="topbar">
        <input id="search" type="text" placeholder="Zoek op naam of e-mail..." />
        <select id="statusFilter">
            <option value="">Alle statussen</option>
            <option value="Actief">Actief</option>
            <option value="Inactief">Inactief</option>
        </select>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Mobiel</th>
                    <th>E-mail</th>
                    <th>Lid sinds</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="ledenBody">
                <tr>
                    <td colspan="5" class="empty-cell">Nog geen leden toegevoegd.</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-container" id="cardContainer"></div>
    <div id="emptyState" class="empty empty-mobile">
        Nog geen leden toegevoegd.
    </div>
</main>

<footer class="footer">© 2026 FitForFun — Alle rechten voorbehouden</footer>

<script src="script.js"></script>
</body>
</html>
