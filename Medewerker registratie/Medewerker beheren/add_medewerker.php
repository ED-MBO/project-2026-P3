<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
require "../../config.php";
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $voornaam   = trim($_POST["voornaam"]);
    $tussen     = trim($_POST["tussenvoegsel"] ?? "");
    $achternaam = trim($_POST["achternaam"]);
    $rolNaam    = trim($_POST["rol"]);

    $toegestaneRollen = ["Lid", "Medewerker", "Administrator", "Gastgebruiker"];
    if (!in_array($rolNaam, $toegestaneRollen)) {
        die("Ongeldige rol opgegeven.");
    }

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

        // Stap 3: rol koppelen aan gebruiker.Id
        $stmtR = $pdo->prepare("INSERT INTO rol (GebruikerId, Naam, IsActief) VALUES (:g, :n, 1)");
        $stmtR->execute([
            ":g" => $gebruikerId,
            ":n" => $rolNaam
        ]);

        // Stap 4: medewerker aanmaken — zonder GebruikerId want kolom bestaat nog niet
        $nummer = (int) $pdo->query("SELECT COALESCE(MAX(Nummer), 0) + 1 FROM medewerker")->fetchColumn();

        $stmtM = $pdo->prepare("INSERT INTO medewerker 
                                    (Voornaam, Tussenvoegsel, Achternaam, Nummer, Medewerkersoort, IsActief)
                                 VALUES (:v, :t, :a, :n, :s, 1)");
        $stmtM->execute([
            ":v" => $voornaam,
            ":t" => $tussen ?: null,
            ":a" => $achternaam,
            ":n" => $nummer,
            ":s" => $rolNaam
        ]);

        $pdo->commit();

        header("Location: index.php?succes=1");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fout bij opslaan: " . $e->getMessage());
    }
}