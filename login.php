<?php
require_once 'config.php';
session_start();

// Als de gebruiker al ingelogd is, stuur door naar de reserveringen pagina
if (isset($_SESSION['gebruiker_id'])) {
    header('Location: reservering registratie/Reservering_Registratie.php');
    exit();
}

$foutmelding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $wachtwoord     = $_POST['wachtwoord'];

    // Zoek de gebruiker op in de gebruiker tabel
    $sql  = "SELECT * FROM gebruiker WHERE Gebruikersnaam = ? AND IsActief = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$gebruikersnaam]);
    $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

    // Controleer of de gebruiker bestaat en het wachtwoord klopt
    if ($gebruiker && $gebruiker['Wachtwoord'] === $wachtwoord) {
        $_SESSION['gebruiker_id']   = $gebruiker['Id'];
        $_SESSION['gebruiker_naam'] = $gebruiker['Voornaam'] . ' ' . $gebruiker['Achternaam'];
        header('Location: reservering registratie/Reservering_Registratie.php');
        exit();
    } else {
        $foutmelding = 'Gebruikersnaam of wachtwoord is onjuist.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen — AcademiaPro</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-pagina">
    <div class="login-kaart">

        <h1>AcademiaPro</h1>
        <p class="subtitel">Log in om verder te gaan</p>

        <?php if ($foutmelding): ?>
            <div class="foutmelding"><?= htmlspecialchars($foutmelding) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="gebruikersnaam">Gebruikersnaam</label>
            <input type="text" id="gebruikersnaam" name="gebruikersnaam" placeholder="Vul je gebruikersnaam in" required>

            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" id="wachtwoord" name="wachtwoord" placeholder="Vul je wachtwoord in" required>

            <button type="submit">Inloggen</button>
        </form>

    </div>
</div>

</body>
</html>