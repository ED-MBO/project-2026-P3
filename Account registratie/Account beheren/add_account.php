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

if (!in_array($mijnRol, ['Medewerker', 'Administrator'])) {
    $_SESSION['flash_fout_account'] = 'U heeft geen rechten om accounts aan te maken.';
    header('Location: index.php');
    exit();
}

$voornaam       = trim($_POST['voornaam'] ?? '');
$tussenvoegsel  = trim($_POST['tussenvoegsel'] ?? '');
$achternaam      = trim($_POST['achternaam'] ?? '');
$gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
$wachtwoord     = $_POST['wachtwoord'] ?? '';
$rol            = trim($_POST['rol'] ?? '');

if ($voornaam === '' || $achternaam === '' || $gebruikersnaam === '' || $wachtwoord === '' || $rol === '') {
    $_SESSION['flash_fout_account'] = 'Vul alle verplichte velden in.';
    header('Location: index.php');
    exit();
}

$toegestaneRollen = ['Lid'];
if ($mijnRol === 'Administrator') {
    $toegestaneRollen = ['Lid', 'Medewerker', 'Administrator'];
}
if (!in_array($rol, $toegestaneRollen)) {
    $_SESSION['flash_fout_account'] = 'U mag alleen accounts met de rol Lid aanmaken.';
    header('Location: index.php');
    exit();
}

if (strlen($wachtwoord) < 8) {
    $_SESSION['flash_fout_account'] = 'Wachtwoord moet minimaal 8 tekens zijn.';
    header('Location: index.php');
    exit();
}

try {
    $check = $pdo->prepare("SELECT COUNT(*) FROM gebruiker WHERE Gebruikersnaam = ?");
    $check->execute([$gebruikersnaam]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['flash_fout_account'] = 'Deze gebruikersnaam bestaat al.';
        header('Location: index.php');
        exit();
    }

    $pdo->beginTransaction();

    $stmtG = $pdo->prepare("
        INSERT INTO gebruiker (Voornaam, Tussenvoegsel, Achternaam, Gebruikersnaam, Wachtwoord, IsActief)
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmtG->execute([
        $voornaam,
        $tussenvoegsel ?: null,
        $achternaam,
        $gebruikersnaam,
        password_hash($wachtwoord, PASSWORD_DEFAULT)
    ]);
    $nieuweId = (int) $pdo->lastInsertId();

    $stmtR = $pdo->prepare("
        INSERT INTO rol (GebruikerId, Naam, IsActief) VALUES (?, ?, 1)
    ");
    $stmtR->execute([$nieuweId, $rol]);

    $pdo->commit();
    $_SESSION['flash_succes_account'] = 'Account is succesvol aangemaakt.';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash_fout_account'] = 'Er ging iets mis bij het opslaan.';
}

header('Location: index.php');
exit();
