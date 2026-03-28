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
$magAccountBeheren = in_array($_SESSION['rol'] ?? '', ['Medewerker', 'Administrator']);
$rol = $_SESSION['rol'] ?? 'Lid';
$isAdministrator = $rol === 'Administrator';
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator']);

if (!$magAccountBeheren) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geen toegang — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Inter, system-ui, sans-serif;
            background: #111318;
            color: #e6e8ef;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            text-align: center;
            max-width: 420px;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #e6e8ef;
        }
        p {
            font-size: 14px;
            color: #8b90a7;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background: #6b8cff;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        a:hover {
            filter: brightness(1.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>403 – Geen toegang</h1>
        <p>U heeft geen rechten om accounts te beheren.</p>
        <a href="../../Informatie/home.php">Terug naar home</a>
    </div>
</body>
</html>
<?php
    exit();
}
$flashSucces = $_SESSION['flash_succes_account'] ?? null;
$flashFout   = $_SESSION['flash_fout_account'] ?? null;
unset($_SESSION['flash_succes_account'], $_SESSION['flash_fout_account']);
?>
<!doctype html>
<html lang="nl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Accounten overzicht — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="style.css?v=<?= time() ?>" />
</head>

<body>
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>

    <div class="wrapper">
        <div class="heading-row">
            <div>
                <h1>Accounten overzicht</h1>
                <div class="sub" id="countLine"></div>
            </div>
            <button type="button" class="btn-primary" id="openAccountModal">
                <i class="fa-solid fa-plus"></i> Account aanmaken
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
            <input id="search" placeholder="Zoek op naam of gebruikersnaam..." />
            <select id="rolFilter">
                <option value="">Alle rollen</option>
            </select>
            <select id="statusFilter">
                <option value="">Alle statussen</option>
                <option value="Ingelogd">Ingelogd</option>
                <option value="Uitgelogd">Uitgelogd</option>
            </select>
        </div>

        <table id="table">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Gebruikersnaam</th>
                    <th>Rol</th>
                    <th>Status</th>
                    <th>Wijzigen</th>
                </tr>
            </thead>
            <tbody id="body"></tbody>
        </table>

        <div id="cardContainer" class="card-container"></div>

        <div id="emptyState" class="empty" style="display: none">
            Geen resultaten. Probeer een andere zoekterm of filter.
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>

    <div class="modal-backdrop" id="modalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitel">
            <div class="modal-header">
                <h2 id="modalTitel">Nieuw account aanmaken</h2>
                <button type="button" class="modal-close" id="sluitModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="add_account.php">
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
                    <label for="gebruikersnaam">Gebruikersnaam <span class="required">*</span></label>
                    <input type="text" id="gebruikersnaam" name="gebruikersnaam" placeholder="Bijv. janj" required />
                </div>
                <div class="form-group">
                    <label for="wachtwoord">Wachtwoord <span class="required">*</span></label>
                    <input type="password" id="wachtwoord" name="wachtwoord" placeholder="Min. 8 tekens" required minlength="8" />
                </div>
                <div class="form-group">
                    <label for="rol">Rol <span class="required">*</span></label>
                    <select id="rol" name="rol" required>
                        <option value="Lid">Lid</option>
                        <?php if ($isAdministrator): ?>
                        <option value="Medewerker">Medewerker</option>
                        <option value="Administrator">Administrator</option>
                        <?php endif; ?>
                    </select>
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

    <div class="modal-backdrop" id="editModalBackdrop">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitel">
            <div class="modal-header">
                <h2 id="editModalTitel">Account wijzigen</h2>
                <button type="button" class="modal-close" id="sluitEditModal" aria-label="Sluiten">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="update_account.php" id="editAccountForm">
                <input type="hidden" name="gebruiker_id" id="edit_gebruiker_id" value="" />
                <div class="form-group">
                    <label for="edit_voornaam">Voornaam <span class="required">*</span></label>
                    <input type="text" id="edit_voornaam" name="voornaam" required />
                </div>
                <div class="form-group">
                    <label for="edit_tussenvoegsel">Tussenvoegsel</label>
                    <input type="text" id="edit_tussenvoegsel" name="tussenvoegsel" />
                </div>
                <div class="form-group">
                    <label for="edit_achternaam">Achternaam <span class="required">*</span></label>
                    <input type="text" id="edit_achternaam" name="achternaam" required />
                </div>
                <div class="form-group">
                    <label for="edit_gebruikersnaam">Gebruikersnaam <span class="required">*</span></label>
                    <input type="text" id="edit_gebruikersnaam" name="gebruikersnaam" required autocomplete="username" />
                </div>
                <div class="form-group">
                    <label for="edit_wachtwoord">Nieuw wachtwoord</label>
                    <input type="password" id="edit_wachtwoord" name="wachtwoord" placeholder="Leeg laten om niet te wijzigen" minlength="8" autocomplete="new-password" />
                </div>
                <div class="form-group" id="editRolGroepSelect">
                    <label for="edit_rol">Rol <span class="required">*</span></label>
                    <select id="edit_rol" name="rol" required>
                        <option value="Lid">Lid</option>
                        <?php if ($isAdministrator): ?>
                        <option value="Medewerker">Medewerker</option>
                        <option value="Administrator">Administrator</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" id="editRolGroepReadonly" style="display: none">
                    <label>Rol</label>
                    <p id="edit_rol_readonly_text" class="edit-rol-readonly"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Opslaan
                    </button>
                    <button type="button" class="btn-secondary" id="annuleerEditModal">Annuleren</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.accountBeheerConfig = {
            isAdministrator: <?= $isAdministrator ? 'true' : 'false' ?>
        };
    </script>
    <script src="script.js"></script>
</body>

</html>