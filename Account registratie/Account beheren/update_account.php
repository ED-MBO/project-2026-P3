<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
$stmtRol->execute([$_SESSION['gebruiker_id']]);
$mijnRol = $stmtRol->fetchColumn() ?: 'Lid';

if (!in_array($mijnRol, ['Medewerker', 'Administrator'], true)) {
    $_SESSION['flash_fout_account'] = 'U heeft geen rechten om accounts te wijzigen.';
    header('Location: index.php');
    exit();
}

$id             = isset($_POST['gebruiker_id']) ? (int) $_POST['gebruiker_id'] : 0;
$voornaam       = trim($_POST['voornaam'] ?? '');
$tussenvoegsel  = trim($_POST['tussenvoegsel'] ?? '');
$achternaam     = trim($_POST['achternaam'] ?? '');
$gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
$wachtwoord     = $_POST['wachtwoord'] ?? '';
$rolGepost      = trim($_POST['rol'] ?? '');

if ($id <= 0 || $voornaam === '' || $achternaam === '' || $gebruikersnaam === '') {
    $_SESSION['flash_fout_account'] = 'Vul alle verplichte velden in.';
    header('Location: index.php');
    exit();
}

try {
    $stmtDoel = $pdo->prepare("
        SELECT g.Id,
               (SELECT r.Naam FROM rol r WHERE r.GebruikerId = g.Id AND r.IsActief = 1 LIMIT 1) AS HuidigeRol
        FROM gebruiker g
        WHERE g.Id = ? AND g.IsActief = 1
        LIMIT 1
    ");
    $stmtDoel->execute([$id]);
    $doel = $stmtDoel->fetch(PDO::FETCH_ASSOC);
    if (!$doel) {
        $_SESSION['flash_fout_account'] = 'Account niet gevonden.';
        header('Location: index.php');
        exit();
    }

    $huidigeRol = $doel['HuidigeRol'] ?? 'Lid';
    $isDoelStaff = in_array($huidigeRol, ['Medewerker', 'Administrator'], true);

    if ($mijnRol === 'Medewerker') {
        if ($isDoelStaff) {
            $nieuweRol = $huidigeRol;
        } else {
            if ($rolGepost !== 'Lid') {
                $_SESSION['flash_fout_account'] = 'U mag alleen de rol Lid toewijzen.';
                header('Location: index.php');
                exit();
            }
            $nieuweRol = 'Lid';
        }
    } else {
        $toegestaan = ['Lid', 'Medewerker', 'Administrator'];
        if (!in_array($rolGepost, $toegestaan, true)) {
            $_SESSION['flash_fout_account'] = 'Ongeldige rol.';
            header('Location: index.php');
            exit();
        }
        $nieuweRol = $rolGepost;
    }

    if ($wachtwoord !== '' && strlen($wachtwoord) < 8) {
        $_SESSION['flash_fout_account'] = 'Wachtwoord moet minimaal 8 tekens zijn (of leeg laten om niet te wijzigen).';
        header('Location: index.php');
        exit();
    }

    $check = $pdo->prepare("SELECT COUNT(*) FROM gebruiker WHERE Gebruikersnaam = ? AND Id != ?");
    $check->execute([$gebruikersnaam, $id]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['flash_fout_account'] = 'Deze gebruikersnaam is al in gebruik.';
        header('Location: index.php');
        exit();
    }

    $pdo->beginTransaction();

    if ($wachtwoord !== '') {
        $stmtG = $pdo->prepare("
            UPDATE gebruiker
            SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Gebruikersnaam = ?, Wachtwoord = ?
            WHERE Id = ? AND IsActief = 1
        ");
        $stmtG->execute([
            $voornaam,
            $tussenvoegsel !== '' ? $tussenvoegsel : null,
            $achternaam,
            $gebruikersnaam,
            password_hash($wachtwoord, PASSWORD_DEFAULT),
            $id,
        ]);
    } else {
        $stmtG = $pdo->prepare("
            UPDATE gebruiker
            SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Gebruikersnaam = ?
            WHERE Id = ? AND IsActief = 1
        ");
        $stmtG->execute([
            $voornaam,
            $tussenvoegsel !== '' ? $tussenvoegsel : null,
            $achternaam,
            $gebruikersnaam,
            $id,
        ]);
    }

    if ($nieuweRol !== $huidigeRol) {
        $stmtR = $pdo->prepare("UPDATE rol SET Naam = ? WHERE GebruikerId = ? AND IsActief = 1");
        $stmtR->execute([$nieuweRol, $id]);
    }

    $pdo->commit();

    if ($id === (int) $_SESSION['gebruiker_id']) {
        $_SESSION['gebruikersnaam'] = $gebruikersnaam;
        $_SESSION['rol'] = $nieuweRol;
        $_SESSION['naam'] = trim($voornaam . ' ' . ($tussenvoegsel !== '' ? $tussenvoegsel . ' ' : '') . $achternaam);
    }

    $_SESSION['flash_succes_account'] = 'Account is bijgewerkt.';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_fout_account'] = 'Er ging iets mis bij het opslaan.';
}

header('Location: index.php');
exit();
