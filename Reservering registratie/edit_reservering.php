<?php
session_start();

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}

require "../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1 LIMIT 1");
    $stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
    $rol = $stmtRol->fetchColumn();

    if (!in_array($rol, ['Medewerker', 'Administrator'])) {
        $_SESSION['flash_fout'] = "U heeft niet voldoende rechten om een reservering te bewerken.";
        header("Location: Reservering_Registratie.php");
        exit();
    }

    $id             = $_POST["reserveringId"] ?? null;
    $voornaam       = trim($_POST["voornaam"] ?? "");
    $tussenvoegsel  = trim($_POST["tussenvoegsel"] ?? "");
    $achternaam     = trim($_POST["achternaam"] ?? "");
    $nummer         = trim($_POST["nummer"] ?? "");
    $datum          = trim($_POST["datum"] ?? "");
    $tijd           = trim($_POST["tijd"] ?? "");
    $status         = trim($_POST["reserveringstatus"] ?? "Gereserveerd");

    // Validatie
    $fouten = [];

    if (empty($id)) {
        $fouten[] = "Geen reservering-ID opgegeven.";
    }
    if (empty($voornaam)) {
        $fouten[] = "Voornaam is verplicht.";
    } elseif (strlen($voornaam) > 50) {
        $fouten[] = "Voornaam mag maximaal 50 tekens bevatten.";
    }
    if (empty($achternaam)) {
        $fouten[] = "Achternaam is verplicht.";
    } elseif (strlen($achternaam) > 50) {
        $fouten[] = "Achternaam mag maximaal 50 tekens bevatten.";
    }
    if ($nummer === '' || !ctype_digit($nummer) || (int)$nummer < 1) {
        $fouten[] = "Voer een geldig nummer in.";
    }
    if (empty($datum)) {
        $fouten[] = "Datum is verplicht.";
    }
    if (empty($tijd)) {
        $fouten[] = "Tijd is verplicht.";
    }
    if (!in_array($status, ['Gereserveerd', 'Vrij'])) {
        $fouten[] = "Ongeldige status geselecteerd.";
    }

    if (!empty($fouten)) {
        $_SESSION['flash_fout'] = implode(' ', $fouten);
        header("Location: Reservering_Registratie.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE reservering 
                               SET Voornaam = :voornaam, 
                                   Tussenvoegsel = :tussenvoegsel, 
                                   Achternaam = :achternaam, 
                                   Nummer = :nummer, 
                                   Datum = :datum, 
                                   Tijd = :tijd, 
                                   Reserveringstatus = :status 
                               WHERE Id = :id AND IsActief = 1");
        $stmt->execute([
            ":voornaam"       => $voornaam,
            ":tussenvoegsel"  => $tussenvoegsel,
            ":achternaam"     => $achternaam,
            ":nummer"         => (int) $nummer,
            ":datum"          => $datum,
            ":tijd"           => $tijd,
            ":status"         => $status,
            ":id"             => $id
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_succes'] = "Reservering succesvol gewijzigd.";
        } else {
            $_SESSION['flash_fout'] = "Reservering niet gevonden of geen wijzigingen aangebracht.";
        }

        session_write_close();
        header("Location: Reservering_Registratie.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash_fout'] = "Fout bij opslaan: " . $e->getMessage();
        header("Location: Reservering_Registratie.php");
        exit;
    }
}
