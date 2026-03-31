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
    $_SESSION['flash_fout_account'] = 'U heeft geen rechten om accounts te verwijderen.';
    header('Location: index.php');
    exit();
}

$id = isset($_POST['gebruiker_id']) ? (int) $_POST['gebruiker_id'] : 0;
$bevestigAchternaam = trim($_POST['bevestig_achternaam'] ?? '');
if ($id <= 0) {
    $_SESSION['flash_fout_account'] = 'Ongeldig account.';
    header('Location: index.php');
    exit();
}
if ($bevestigAchternaam === '') {
    $_SESSION['flash_fout_account'] = 'Vul de achternaam in om verwijderen te bevestigen.';
    header('Location: index.php');
    exit();
}

if ($id === (int) $_SESSION['gebruiker_id']) {
    $_SESSION['flash_fout_account'] = 'U kunt uw eigen account niet verwijderen.';
    header('Location: index.php');
    exit();
}

try {
    $check = $pdo->prepare("SELECT Id, Achternaam FROM gebruiker WHERE Id = ? AND IsActief = 1 LIMIT 1");
    $check->execute([$id]);
    $teVerwijderen = $check->fetch(PDO::FETCH_ASSOC);
    if (!$teVerwijderen) {
        $_SESSION['flash_fout_account'] = 'Account niet gevonden of al verwijderd.';
        header('Location: index.php');
        exit();
    }
    $achternaamDb = trim((string) ($teVerwijderen['Achternaam'] ?? ''));
    if (strcasecmp($bevestigAchternaam, $achternaamDb) !== 0) {
        $_SESSION['flash_fout_account'] = 'Achternaam komt niet overeen. Account is niet verwijderd.';
        header('Location: index.php');
        exit();
    }

    $upd = $pdo->prepare("UPDATE gebruiker SET IsActief = 0 WHERE Id = ?");
    $upd->execute([$id]);

    $_SESSION['flash_succes_account'] = 'Account is verwijderd.';
} catch (PDOException $e) {
    $_SESSION['flash_fout_account'] = 'Er ging iets mis bij het verwijderen.';
}

header('Location: index.php');
exit();
