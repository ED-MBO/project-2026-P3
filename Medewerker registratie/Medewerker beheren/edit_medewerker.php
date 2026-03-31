<?php
session_start();

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}

require "../../config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1");
    $stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
    $rol = $stmtRol->fetchColumn();

    if (!in_array($rol, ["Administrator"])) {
        $_SESSION['flash_fout'] = "U heeft niet voldoende rechten om een medewerker te bewerken.";
        header("Location: index.php");
        exit();
    }

    $id = $_POST["medewerkerId"] ?? null;
    $voornaam   = trim($_POST["voornaam"] ?? "");
    $tussen     = trim($_POST["tussenvoegsel"] ?? "");
    $achternaam = trim($_POST["achternaam"] ?? "");

    if (empty($voornaam) || empty($achternaam) || empty($id)) {
        $_SESSION['flash_fout'] = "Een verplicht veld is leeg. Medewerker niet gewijzigd.";
        header("Location: index.php");
        exit();
    }

    try {
        $stmtM = $pdo->prepare("UPDATE medewerker 
                                SET Voornaam = :v, Tussenvoegsel = :t, Achternaam = :a 
                                WHERE Id = :id");
        $stmtM->execute([
            ":v" => $voornaam,
            ":t" => $tussen ?: null,
            ":a" => $achternaam,
            ":id" => $id
        ]);

        $_SESSION['flash_succes'] = "Medewerker succesvol gewijzigd.";
        session_write_close();
        header("Location: index.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash_fout'] = "Fout bij opslaan: " . $e->getMessage();
        header("Location: index.php");
        exit;
    }
}
