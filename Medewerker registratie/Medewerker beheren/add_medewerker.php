<?php
session_start();

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}

require "../../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Rolcontrole — alleen Administrator en Medewerker mogen een medewerker toevoegen
    $stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1");
    $stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
    $rol = $stmtRol->fetchColumn();

    if (!in_array($rol, ["Administrator"])) {
        $_SESSION['flash_fout'] = "U heeft niet voldoende rechten om een medewerker toe te voegen.";
        header("Location: index.php");
        exit();
    }

    $voornaam   = trim($_POST["voornaam"]);
    $tussen     = trim($_POST["tussenvoegsel"] ?? "");
    $achternaam = trim($_POST["achternaam"]);

    try {
        $pdo->beginTransaction();

        // Stap 1: Unieke gebruikersnaam genereren
        $basisnaam      = strtolower($voornaam . $achternaam);
        $gebruikersnaam = $basisnaam;
        $teller         = 1;
        while (true) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM gebruiker WHERE Gebruikersnaam = :g");
            $check->execute([":g" => $gebruikersnaam]);
            if ($check->fetchColumn() == 0) break;
            $teller++;
            $gebruikersnaam = $basisnaam . $teller;
        }

        // Stap 2: gebruiker aanmaken
        $stmtG = $pdo->prepare("INSERT INTO gebruiker 
                                    (Voornaam, Tussenvoegsel, Achternaam, Gebruikersnaam, Wachtwoord, IsActief)
                                 VALUES (:v, :t, :a, :g, :w, 1)");
        $stmtG->execute([
            ":v" => $voornaam,
            ":t" => $tussen ?: null,
            ":a" => $achternaam,
            ":g" => $gebruikersnaam,
            ":w" => password_hash("welkom123", PASSWORD_DEFAULT)
        ]);

        // Stap 3: medewerker aanmaken
        $stmtM = $pdo->prepare("INSERT INTO medewerker 
                                    (Voornaam, Tussenvoegsel, Achternaam, IsActief)
                                 VALUES (:v, :t, :a, 1)");
        $stmtM->execute([
            ":v" => $voornaam,
            ":t" => $tussen ?: null,
            ":a" => $achternaam
        ]);

        $pdo->commit();

        $_SESSION['flash_succes'] = "Medewerker is succesvol aangemaakt en toegevoegd.";
        session_write_close();
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fout bij opslaan: " . $e->getMessage());
    }
}