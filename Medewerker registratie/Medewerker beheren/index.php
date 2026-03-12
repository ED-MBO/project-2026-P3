<?php
session_start();
require_once __DIR__ . '/../../config.php';
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Flash berichten uitlezen én direct wissen zodat refresh ze niet toont
$flashSucces = $_SESSION['flash_succes'] ?? null;
$flashFout   = $_SESSION['flash_fout']   ?? null;
unset($_SESSION['flash_succes'], $_SESSION['flash_fout']);
?>
<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Medewerker Beheren</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="/Medewerker registratie/Medewerker beheren/medewerker-beheren.css" />
</head>

<body>
    <header class="header">
        <div class="navbar-container">
            <a href="../../Informatie/home.php" class="logo">FitForFun</a>
            <div class="hamburger"><i class="fa-solid fa-bars"></i></div>
            <nav class="navbar">
                <div class="close-menu"><i class="fa-solid fa-xmark"></i></div>
                <ul class="navbar-nav">
                    <li><a class="nav-link" href="../../Informatie/home.php">Home</a></li>
                    <li><a class="nav-link" href="../../Account registratie/Account beheren/index.php">Account
                            beheren</a></li>
                    <li><a class="nav-link" href="index.php">Medewerker beheren</a></li>
                    <li><a class="nav-link" href="../../Lid registratie/index.php">Lid beheren</a></li>
                    <li><a class="nav-link" href="../../Les registratie/Overzicht_lessen.php">Les beheren</a></li>
                    <li><a class="nav-link" href="../../Reservering registratie/Reservering_Registratie.php">Reservering
                            beheren</a></li>
                    <li><a class="nav-link" href="../../Management Dashboard/Dashboard beheren/index.php">Dashboard
                            beheren</a></li>
                </ul>
            </nav>
            <div class="overlay"></div>
        </div>
    </header>

    <div class="wrapper">
        <div class="heading-row">
            <div>
                <h1>Team</h1>
                <div class="sub" id="countLine"></div>
            </div>

            <button class="btn-primary" id="openModal">
                <i class="fa-solid fa-plus"></i> Nieuwe medewerker
            </button>
        </div>

        <?php if ($flashSucces): ?>
        <div class="alert-success" id="successAlert">
            <i class="fa-solid fa-circle-check"></i>
            <?= htmlspecialchars($flashSucces) ?>
        </div>
        <?php endif; ?>

        <?php if ($flashFout): ?>
        <div class="alert-error" id="errorAlert">
            <i class="fa-solid fa-circle-xmark"></i>
            <?= htmlspecialchars($flashFout) ?>
        </div>
        <?php endif; ?>

        <div class="topbar">
            <input id="search" placeholder="Zoek op naam..." />
            <select id="afdeling">
                <option value="">Alle afdelingen</option>
            </select>
            <select id="status">
                <option value="">Alle statussen</option>
                <option>Beschikbaar</option>
                <option>Bezet</option>
                <option>Afwezig</option>
                <option>Op locatie</option>
            </select>
        </div>

        <table id="table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Afdeling</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="body"></tbody>
        </table>

        <div id="cardContainer" class="card-container"></div>
        <div id="emptyState" class="empty" style="display: none">
            Geen resultaten. Probeer een andere zoekterm.
        </div>
    </div>

    <footer class="footer">© 2026 FitForFun — Alle rechten voorbehouden</footer>

    <div class="modal-backdrop" id="modalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitel">
            <div class="modal-header">
                <h2 id="modalTitel">Nieuwe medewerker</h2>
                <button class="modal-close" id="sluitModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="add_medewerker.php">
                <div class="form-group">
                    <label for="voornaam">Voornaam <span class="required">*</span></label>
                    <input type="text" id="voornaam" name="voornaam" placeholder="Bijv. Jan" required />
                </div>
                <div class="form-group">
                    <label for="tussenvoegsel">Tussenvoegsel</label>
                    <input type="text" id="tussenvoegsel" name="tussenvoegsel" placeholder="Bijv. van der" />
                </div>
                <div class="form-group">
                    <label for="achternaam">Achternaam <span class="required">*</span></label>
                    <input type="text" id="achternaam" name="achternaam" placeholder="Bijv. Smit" required />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Opslaan
                    </button>
                    <button type="button" class="btn-secondary" id="annuleerModal">
                        Annuleren
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/Medewerker registratie/Medewerker beheren/medewerker-beheren.js?v=<?= time() ?>"></script>
</body>

</html>