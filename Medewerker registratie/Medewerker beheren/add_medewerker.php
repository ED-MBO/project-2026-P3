<?php
session_start();

// Niet ingelogd → terug naar login
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}

require "../../config.php";

// Unhappy path: controleer of de ingelogde gebruiker de rol Administrator heeft
$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1");
$stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
$rolGebruiker = $stmtRol->fetchColumn();

if ($rolGebruiker !== "Administrator") {
    $_SESSION['flash_fout'] = "Toegang geweigerd — u heeft geen rechten om een medewerker toe te voegen.";
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $voornaam   = trim($_POST["voornaam"]);
    $tussen     = trim($_POST["tussenvoegsel"] ?? "");
    $achternaam = trim($_POST["achternaam"]);
    $rolNaam    = "Medewerker";

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

        $gebruikerId = (int) $pdo->lastInsertId();

        // Stap 3: rol koppelen
        $stmtR = $pdo->prepare("INSERT INTO rol (GebruikerId, Naam, IsActief) VALUES (:g, :n, 1)");
        $stmtR->execute([
            ":g" => $gebruikerId,
            ":n" => $rolNaam
        ]);

        // Stap 4: medewerker aanmaken
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