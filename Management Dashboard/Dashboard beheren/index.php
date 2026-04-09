<?php
session_start();
require_once __DIR__ . '/../../config.php';
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
if (empty($_SESSION['rol'])) {
    $stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
    $stmtRol->execute([$_SESSION['gebruiker_id']]);
    $_SESSION['rol'] = $stmtRol->fetchColumn() ?: 'Lid';
}
$rol = $_SESSION['rol'] ?? 'Lid';
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator']);

if (!$isMedewerkerOfAdmin) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Geen toegang — FitForFun</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,system-ui,sans-serif;background:#111318;color:#e6e8ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.container{text-align:center;max-width:420px}h1{font-size:24px;font-weight:600;margin-bottom:12px}p{font-size:14px;color:#8b90a7;margin-bottom:24px;line-height:1.5}a{display:inline-block;padding:10px 20px;background:#6b8cff;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:500}</style></head><body><div class="container"><h1>403 – Geen toegang</h1><p>U heeft geen rechten om het dashboard te bekijken.</p><a href="../../Informatie/home.php">Terug naar home</a></div></body></html>';
    exit();
}
?> 
<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="aan-leden-per-periode.css" />
</head>

<body>
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <main class="wrapper">
        <!-- lessen -->
        <h1>Geplande Lessen</h1>
        <div class="sub" id="countLine"></div>

        <div class="topbar">
            <input id="search" type="text" placeholder="Zoek op lesnaam..." />
            <select id="statusFilter">
                <option value="">Alle statussen</option>
                <option value="Ingepland">Ingepland</option>
                <option value="Niet gestart">Niet gestart</option>
                <option value="Gestart">Gestart</option>
                <option value="Geannuleerd">Geannuleerd</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Les</th>
                        <th>Datum</th>
                        <th>Tijd</th>
                        <th>Prijs</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="lessenBody"></tbody>
            </table>
        </div>

        <div class="card-container" id="cardContainer"></div>
        <div id="emptyState" class="empty" style="display: none">
            Geen lessen gevonden.
        </div>

        <hr class="divider" />

        <!-- leden -->
        <h1>Aantal Leden per Periode</h1>
        <p class="sub">
            Inzicht in de ontwikkeling van het ledenaantal over tijd.
        </p>

        <div id="alertEl" class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span id="alertMsg">Het overzicht kon niet geladen worden.</span>
        </div>

        <div id="ledenOverzicht">
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-title">Totaal leden</div>
                    <div class="stat-number" id="sTotaal">—</div>
                    <div class="stat-sub blue" id="cTotaal">Laden...</div>
                </div>
                <div class="stat-card green">
                    <div class="stat-title">Nieuwe leden</div>
                    <div class="stat-number" id="sNieuw">—</div>
                    <div class="stat-sub green" id="cNieuw">Laden...</div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-title">Actieve leden</div>
                    <div class="stat-number" id="sActief">—</div>
                    <div class="stat-sub yellow">Met reservering</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-title">Inactieve leden</div>
                    <div class="stat-number" id="sInactief">—</div>
                    <div class="stat-sub red">Geen reservering</div>
                </div>
            </div>

            <div class="controls">
                <label for="periodeType">Periode:</label>
                <select id="periodeType">
                    <option value="maand">Per maand</option>
                    <option value="jaar">Per jaar</option>
                </select>
                <label for="jaarSelect">Jaar:</label>
                <select id="jaarSelect"></select>
            </div>

            <div class="chart-card">
                <div class="chart-title" id="chartTitle">Ledenaantal per maand</div>
                <div class="chart-sub">Totaal actieve leden per periode</div>
                <div class="chart-wrapper">
                    <canvas id="ledenChart" height="260"></canvas>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Totaal leden</th>
                            <th>Nieuw</th>
                            <th>Groei</th>
                        </tr>
                    </thead>
                    <tbody id="tabelBody"></tbody>
                </table>
            </div>
        </div>

        <hr class="divider" />

        <!-- reserveringen -->
        <h1>Aantal Reserveringen per Periode</h1>
        <p class="sub">
            Inzicht in het verloop van reserveringen over tijd.
        </p>

        <div id="alertReserveringen" class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span id="alertMsgReserveringen">Het overzicht kon niet geladen worden.</span>
        </div>

        <div id="reserveringenOverzicht">
            <div class="controls">
                <label for="periodeTypeRes">Periode:</label>
                <select id="periodeTypeRes">
                    <option value="maand">Per maand</option>
                    <option value="jaar">Per jaar</option>
                </select>
                <label id="jaarLabelRes" for="jaarSelectRes">Jaar:</label>
                <select id="jaarSelectRes"></select>
            </div>

            <div class="chart-card">
                <div class="chart-title" id="chartTitleRes">Aantal reserveringen per maand</div>
                <div class="chart-sub">Totaal aantal reserveringen per periode</div>
                <div class="chart-wrapper">
                    <canvas id="reserveringenChart" height="260"></canvas>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Aantal reserveringen</th>
                        </tr>
                    </thead>
                    <tbody id="tabelBodyRes"></tbody>
                </table>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="script.js"></script>
    <script src="leden.js"></script>
    <script src="reserveringen.js"></script>
</body>

</html>