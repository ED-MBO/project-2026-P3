<?php
session_start();

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
$stmtRol->execute([$_SESSION['gebruiker_id']]);
$mijnRol = $stmtRol->fetchColumn() ?: 'Lid';

if (!in_array($mijnRol, ['Medewerker', 'Administrator'], true)) {
    $_SESSION['flash_fout_lid'] = 'Geen toegang.';
    header('Location: index.php');
    exit();
}

$id = isset($_POST['lid_id']) ? (int) $_POST['lid_id'] : 0;
$bevestigAchternaam = trim($_POST['bevestig_achternaam'] ?? '');
if ($id <= 0) {
    $_SESSION['flash_fout_lid'] = 'Ongeldig lid.';
    header('Location: index.php');
    exit();
}
if ($bevestigAchternaam === '') {
    $_SESSION['flash_fout_lid'] = 'Vul de achternaam in om verwijderen te bevestigen.';
    header('Location: index.php');
    exit();
}

try {
    $check = $pdo->prepare("SELECT Id, Achternaam, IsActief FROM lid WHERE Id = ? LIMIT 1");
    $check->execute([$id]);
    $lid = $check->fetch(PDO::FETCH_ASSOC);
    if (!$lid) {
        $_SESSION['flash_fout_lid'] = 'Lid niet gevonden.';
        header('Location: index.php');
        exit();
    }
    if ((int) $lid['IsActief'] !== 1) {
        $_SESSION['flash_fout_lid'] = 'Dit lid is al verwijderd.';
        header('Location: index.php');
        exit();
    }
    if (strcasecmp($bevestigAchternaam, trim((string) $lid['Achternaam'])) !== 0) {
        $_SESSION['flash_fout_lid'] = 'Achternaam komt niet overeen. Lid is niet verwijderd.';
        header('Location: index.php');
        exit();
    }

    $upd = $pdo->prepare("UPDATE lid SET IsActief = 0 WHERE Id = ?");
    $upd->execute([$id]);

    $_SESSION['flash_succes_lid'] = 'Lid is verwijderd.';
} catch (PDOException $e) {
    $_SESSION['flash_fout_lid'] = 'Er ging iets mis bij het verwijderen.';
}

header('Location: index.php');
exit();
