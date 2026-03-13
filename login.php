<?php
session_start();
require_once __DIR__ . '/config.php';

// Al ingelogd → doorsturen naar home
if (!empty($_SESSION['ingelogd']) && !empty($_SESSION['gebruiker_id'])) {
    header('Location: Informatie/index.html');
    exit();
}

$foutmelding = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if ($gebruikersnaam === '' || $wachtwoord === '') {
        $foutmelding = 'Vul gebruikersnaam en wachtwoord in.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT Id, Gebruikersnaam, Wachtwoord, Voornaam, Tussenvoegsel, Achternaam
                FROM gebruiker
                WHERE Gebruikersnaam = ? AND IsActief = 1
                LIMIT 1
            ");
            $stmt->execute([$gebruikersnaam]);
            $gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$gebruiker) {
                $foutmelding = 'Onjuiste gebruikersnaam of wachtwoord.';
            } else {
                $wachtwoordOk = false;
                $hash = $gebruiker['Wachtwoord'];
                // Bcrypt-hash (nieuwe wachtwoorden) of plain (bestandsdata)
                if (strlen($hash) >= 60 && strpos($hash, '$2y$') === 0) {
                    $wachtwoordOk = password_verify($wachtwoord, $hash);
                } else {
                    $wachtwoordOk = ($wachtwoord === $hash);
                }

                if (!$wachtwoordOk) {
                    $foutmelding = 'Onjuiste gebruikersnaam of wachtwoord.';
                } else {
                    $_SESSION['ingelogd'] = true;
                    $_SESSION['gebruiker_id'] = (int) $gebruiker['Id'];
                    $_SESSION['gebruikersnaam'] = $gebruiker['Gebruikersnaam'];
                    $_SESSION['naam'] = trim($gebruiker['Voornaam'] . ' ' . ($gebruiker['Tussenvoegsel'] ?? '') . ' ' . $gebruiker['Achternaam']);
                    $rolStmt = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
                    $rolStmt->execute([$gebruiker['Id']]);
                    $_SESSION['rol'] = $rolStmt->fetchColumn() ?: 'Lid';

                    $update = $pdo->prepare("UPDATE gebruiker SET IsIngelogd = 1, Ingelogd = CURRENT_TIMESTAMP WHERE Id = ?");
                    $update->execute([$gebruiker['Id']]);

                    header('Location: Informatie/index.html');
                    exit();
                }
            }
        } catch (PDOException $e) {
            $foutmelding = 'Er ging iets mis. Probeer het later opnieuw.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111318">
    <title>Inloggen — FitForFun</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css?v=2">
    <style>
      /* Dark theme – altijd toepassen (zelfde als rest van site) */
      html, body { background: #111318 !important; color: #e6e8ef !important; }
      .login-pagina { background: #111318 !important; }
      .login-kaart {
        background: #1a1d26 !important;
        border: 1px solid #262a36 !important;
        color: #e6e8ef !important;
      }
      .login-kaart h1 { color: #e6e8ef !important; }
      .login-kaart .subtitel { color: #8b90a7 !important; }
      .login-kaart label { color: #8b90a7 !important; }
      .login-kaart input {
        background: #111318 !important;
        border-color: #262a36 !important;
        color: #e6e8ef !important;
      }
      .login-kaart input::placeholder { color: #8b90a7 !important; }
      .login-kaart button[type="submit"] { background: #6b8cff !important; color: #fff !important; }
      .login-terug {
        color: #8b90a7 !important;
        border-color: #262a36 !important;
        background: transparent !important;
      }
      .login-terug:hover { color: #6b8cff !important; border-color: #6b8cff !important; }
      .login-kaart .foutmelding {
        background: rgba(248, 113, 113, 0.15) !important;
        color: #f87171 !important;
        border-left-color: #f87171 !important;
      }
    </style>
</head>
<body class="login-body">

<div class="login-pagina">
    <div class="login-kaart">
        <h1>FitForFun</h1>
        <p class="subtitel">Log in op je account</p>

        <?php if ($foutmelding !== ''): ?>
            <div class="foutmelding"><?= htmlspecialchars($foutmelding) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="gebruikersnaam">Gebruikersnaam</label>
            <input type="text" id="gebruikersnaam" name="gebruikersnaam" value="<?= htmlspecialchars($_POST['gebruikersnaam'] ?? '') ?>" required autofocus autocomplete="username">

            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" id="wachtwoord" name="wachtwoord" required autocomplete="current-password">

            <button type="submit">Inloggen</button>
        </form>
        <a href="Informatie/index.html" class="login-terug">Terug naar home</a>
    </div>
</div>

</body>
</html>
 