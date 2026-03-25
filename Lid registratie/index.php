<?php
session_start();
require_once __DIR__ . '/../config.php';

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
$isAdmin = $rol === 'Administrator';
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator']);

$toonAccountBeheren = $isMedewerkerOfAdmin;
$toonMedewerkerBeheren = $isAdmin;
$toonLidBeheren = $isMedewerkerOfAdmin;
$toonLesBeheren = $isMedewerkerOfAdmin;
$toonReserveringBeheren = $isMedewerkerOfAdmin;
$toonDashboard = $isMedewerkerOfAdmin;

if (!$isMedewerkerOfAdmin) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Geen toegang — FitForFun</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,system-ui,sans-serif;background:#111318;color:#e6e8ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.container{text-align:center;max-width:420px}h1{font-size:24px;font-weight:600;margin-bottom:12px}p{font-size:14px;color:#8b90a7;margin-bottom:24px;line-height:1.5}a{display:inline-block;padding:10px 20px;background:#6b8cff;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:500}</style></head><body><div class="container"><h1>403 – Geen toegang</h1><p>U heeft geen rechten om leden te beheren.</p><a href="../Informatie/home.php">Terug naar home</a></div></body></html>';
    exit();
}
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
                    <?php if ($toonAccountBeheren): ?>
                    <li><a class="nav-link" href="../Account registratie/Account beheren/index.php">Account beheren</a></li>
                    <?php endif; ?>
                    <?php if ($toonMedewerkerBeheren): ?>
                    <li>
                        <a class="nav-link" href="../Medewerker registratie/Medewerker beheren/index.php">Medewerker
                            beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonLidBeheren): ?>
                    <li><a class="nav-link" href="index.php">Lid beheren</a></li>
                    <?php endif; ?>
                    <?php if ($toonLesBeheren): ?>
                    <li><a class="nav-link" href="../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                    <?php endif; ?>
                    <?php if ($toonReserveringBeheren): ?>
                    <li><a class="nav-link" href="../Reservering registratie/Reservering_Registratie.php">Reservering
                            beheren</a></li>
                    <?php endif; ?>
                    <?php if ($toonDashboard): ?>
                    <li><a class="nav-link" href="../Management Dashboard/Dashboard beheren/index.php">Dashboard
                            beheren</a></li>
                    <?php endif; ?>
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