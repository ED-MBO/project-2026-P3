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

if (!in_array($mijnRol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    $_SESSION['flash_fout_lid'] = 'Geen toegang.';
    header('Location: index.php');
    exit();
}

$voornaam = trim($_POST['voornaam'] ?? '');
$tussenvoegsel = trim($_POST['tussenvoegsel'] ?? '');
$achternaam = trim($_POST['achternaam'] ?? '');
$relatienummer = trim($_POST['relatienummer'] ?? '');
$mobiel = trim($_POST['mobiel'] ?? '');
$email = trim($_POST['email'] ?? '');
$opmerking = trim($_POST['opmerking'] ?? '');

if ($voornaam === '' || $achternaam === '' || $relatienummer === '' || $mobiel === '' || $email === '') {
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
    // Pre-check op unieke constraints (Relatienummer/Email zijn cruciaal voor joins).
    $checkEmail = $pdo->prepare("SELECT COUNT(*) FROM lid WHERE Email = ?");
    $checkEmail->execute([$email]);
    if ((int) $checkEmail->fetchColumn() > 0) {
        $_SESSION['flash_fout_lid'] = 'Deze e-mail bestaat al.';
        header('Location: index.php');
        exit();
    }

    $checkRelatie = $pdo->prepare("SELECT COUNT(*) FROM lid WHERE Relatienummer = ?");
    $checkRelatie->execute([(int) $relatienummer]);
    if ((int) $checkRelatie->fetchColumn() > 0) {
        $_SESSION['flash_fout_lid'] = 'Dit relatienummer bestaat al.';
        header('Location: index.php');
        exit();
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO lid
            (Voornaam, Tussenvoegsel, Achternaam, Relatienummer, Mobiel, Email, IsActief, Opmerking)
        VALUES
            (?, ?, ?, ?, ?, ?, 1, ?)
    ");

    $stmt->execute([
        $voornaam,
        $tussenvoegsel !== '' ? $tussenvoegsel : null,
        $achternaam,
        (int) $relatienummer,
        $mobiel,
        $email,
        $opmerking !== '' ? $opmerking : null,
    ]);

    $pdo->commit();
    $_SESSION['flash_succes_lid'] = 'Lid is succesvol aangemaakt.';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['flash_fout_lid'] = 'Er ging iets mis bij het opslaan.';
}

header('Location: index.php');
exit();

