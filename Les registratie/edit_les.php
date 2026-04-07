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
        $_SESSION['flash_fout'] = "U heeft niet voldoende rechten om een les te bewerken.";
        header("Location: Overzicht_lessen.php");
        exit();
    }

    $id             = $_POST["lesId"] ?? null;
    $naam           = trim($_POST["naam"] ?? "");
    $prijs          = trim($_POST["prijs"] ?? "");
    $datum          = trim($_POST["datum"] ?? "");
    $tijd           = trim($_POST["tijd"] ?? "");
    $min_personen   = trim($_POST["min_personen"] ?? "");
    $max_personen   = trim($_POST["max_personen"] ?? "");
    $beschikbaarheid = trim($_POST["beschikbaarheid"] ?? "Ingepland");

    // Validatie
    $fouten = [];

    if (empty($id)) {
        $fouten[] = "Geen les-ID opgegeven.";
    }
    if (empty($naam)) {
        $fouten[] = "Lesnaam is verplicht.";
    }
    if ($prijs === '' || !is_numeric($prijs) || $prijs < 0) {
        $fouten[] = "Een geldige prijs is verplicht.";
    }
    if (empty($datum)) {
        $fouten[] = "Datum is verplicht.";
    }
    if (empty($tijd)) {
        $fouten[] = "Tijd is verplicht.";
    }
    if (empty($min_personen) || !is_numeric($min_personen) || $min_personen < 1) {
        $fouten[] = "Min. personen is verplicht (minimaal 1).";
    }
    if (empty($max_personen) || !is_numeric($max_personen) || $max_personen < 1) {
        $fouten[] = "Max. personen is verplicht (minimaal 1).";
    }
    if (!empty($min_personen) && !empty($max_personen) && $min_personen > $max_personen) {
        $fouten[] = "Minimum mag niet groter zijn dan maximum.";
    }
    if (!in_array($beschikbaarheid, ['Ingepland', 'Niet gestart', 'Gestart', 'Geannuleerd'])) {
        $fouten[] = "Ongeldige status geselecteerd.";
    }

    if (!empty($fouten)) {
        $_SESSION['flash_fout'] = implode(' ', $fouten);
        header("Location: Overzicht_lessen.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE les 
                               SET Naam = :naam, 
                                   Prijs = :prijs, 
                                   Datum = :datum, 
                                   Tijd = :tijd, 
                                   MinAantalPersonen = :minp, 
                                   MaxAantalPersonen = :maxp, 
                                   Beschikbaarheid = :beschikbaarheid 
                               WHERE Id = :id AND IsActief = 1");
        $stmt->execute([
            ":naam"  => $naam,
            ":prijs" => $prijs,
            ":datum" => $datum,
            ":tijd"  => $tijd,
            ":minp"  => (int) $min_personen,
            ":maxp"  => (int) $max_personen,
            ":beschikbaarheid" => $beschikbaarheid,
            ":id"    => $id
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['flash_succes'] = "Les succesvol gewijzigd.";
        } else {
            $_SESSION['flash_fout'] = "Les niet gevonden of geen wijzigingen aangebracht.";
        }

        session_write_close();
        header("Location: Overzicht_lessen.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash_fout'] = "Fout bij opslaan: " . $e->getMessage();
        header("Location: Overzicht_lessen.php");
        exit;
    }
}
