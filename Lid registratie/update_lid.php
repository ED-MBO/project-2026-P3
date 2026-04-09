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
$voornaam = trim($_POST['voornaam'] ?? '');
$tussenvoegsel = trim($_POST['tussenvoegsel'] ?? '');
$achternaam = trim($_POST['achternaam'] ?? '');
$relatienummer = trim($_POST['relatienummer'] ?? '');
$mobiel = trim($_POST['mobiel'] ?? '');
$email = trim($_POST['email'] ?? '');
$opmerking = trim($_POST['opmerking'] ?? '');

if ($id <= 0 || $voornaam === '' || $achternaam === '' || $relatienummer === '' || $mobiel === '' || $email === '') {
    $_SESSION['flash_fout_lid'] = 'Vul alle verplichte velden in.';
    header('Location: index.php');
    exit();
}
if (!ctype_digit($relatienummer)) {
    $_SESSION['flash_fout_lid'] = 'Relatienummer moet een geheel getal zijn.';
    header('Location: index.php');
    exit();
}

try {
    $check = $pdo->prepare("SELECT Id FROM lid WHERE Id = ? LIMIT 1");
    $check->execute([$id]);
    if (!$check->fetch()) {
        $_SESSION['flash_fout_lid'] = 'Lid niet gevonden.';
        header('Location: index.php');
        exit();
    }

    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM lid WHERE Email = ? AND Id != ?");
    $checkEmail->execute([$email, $id]);
    if ((int) $checkEmail->fetchColumn() > 0) {
        $_SESSION['flash_fout_lid'] = 'Deze e-mail bestaat al.';
        header('Location: index.php');
        exit();
    }

    $checkRelatie = $pdo->prepare("SELECT COUNT(*) FROM lid WHERE Relatienummer = ? AND Id != ?");
    $checkRelatie->execute([(int) $relatienummer, $id]);
    if ((int) $checkRelatie->fetchColumn() > 0) {
        $_SESSION['flash_fout_lid'] = 'Dit relatienummer bestaat al.';
        header('Location: index.php');
        exit();
    }

    $stmt = $pdo->prepare("
        UPDATE lid
        SET Voornaam = ?, Tussenvoegsel = ?, Achternaam = ?, Relatienummer = ?, Mobiel = ?, Email = ?, Opmerking = ?
        WHERE Id = ?
    ");
    $stmt->execute([
        $voornaam,
        $tussenvoegsel !== '' ? $tussenvoegsel : null,
        $achternaam,
        (int) $relatienummer,
        $mobiel,
        $email,
        $opmerking !== '' ? $opmerking : null,
        $id,
    ]);

    $_SESSION['flash_succes_lid'] = 'Lid is bijgewerkt.';
} catch (PDOException $e) {
    $_SESSION['flash_fout_lid'] = 'Er ging iets mis bij het opslaan.';
}

header('Location: index.php');
exit();
