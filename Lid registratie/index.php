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
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator']);

if (!$isMedewerkerOfAdmin) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Geen toegang — FitForFun</title><style>*{box-sizing:border-box;margin:0;padding:0}body{font-family:Inter,system-ui,sans-serif;background:#111318;color:#e6e8ef;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}.container{text-align:center;max-width:420px}h1{font-size:24px;font-weight:600;margin-bottom:12px}p{font-size:14px;color:#8b90a7;margin-bottom:24px;line-height:1.5}a{display:inline-block;padding:10px 20px;background:#6b8cff;color:#fff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:500}</style></head><body><div class="container"><h1>403 – Geen toegang</h1><p>U heeft geen rechten om leden te beheren.</p><a href="../Informatie/home.php">Terug naar home</a></div></body></html>';
    exit();
}

$flashSucces = $_SESSION['flash_succes_lid'] ?? null;
$flashFout = $_SESSION['flash_fout_lid'] ?? null;
unset($_SESSION['flash_succes_lid'], $_SESSION['flash_fout_lid']);
?> 
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leden beheren — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>

<body>

    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="wrapper">
        <div class="heading-row">
            <div>
                <h1>Leden beheren</h1>
                <div class="sub" id="countLine">0 leden</div>
            </div>
            <button type="button" class="btn-primary" id="openLidModal">
                <i class="fa-solid fa-plus"></i> Nieuw lid
            </button>
        </div>

        <?php if ($flashSucces): ?>
        <div class="alert-success" id="successAlert">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flashSucces) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashFout): ?>
        <div class="alert-error" id="errorAlert">
            <i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($flashFout) ?>
        </div>
        <?php endif; ?>

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

    <div class="modal-backdrop" id="modalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitel">
            <div class="modal-header">
                <h2 id="modalTitel">Nieuw lid</h2>
                <button type="button" class="modal-close" id="sluitModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="add_lid.php">
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
                <div class="form-group">
                    <label for="relatienummer">Relatienummer <span class="required">*</span></label>
                    <input type="number" id="relatienummer" name="relatienummer" placeholder="Bijv. 301" required />
                </div>
                <div class="form-group">
                    <label for="mobiel">Mobiel <span class="required">*</span></label>
                    <input type="text" id="mobiel" name="mobiel" placeholder="Bijv. 0612345678" required />
                </div>
                <div class="form-group">
                    <label for="email">E-mail <span class="required">*</span></label>
                    <input type="email" id="email" name="email" placeholder="Bijv. naam@example.com" required />
                </div>
                <div class="form-group">
                    <label for="opmerking">Opmerking</label>
                    <input type="text" id="opmerking" name="opmerking" placeholder="Optioneel" />
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Opslaan
                    </button>
                    <button type="button" class="btn-secondary" id="annuleerModal">Annuleren</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js?v=<?= time() ?>"></script>
</body>

</html>