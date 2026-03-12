<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
require "../../config.php";
 
try {
    $sql = "SELECT 
                 Voornaam
                ,Tussenvoegsel
                ,Achternaam
            FROM medewerker
            WHERE IsActief = 1
            ORDER BY Voornaam";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $medewerkers = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $naam = $row['Voornaam'] . " " .
                ($row['Tussenvoegsel'] ? $row['Tussenvoegsel'] . " " : "") .
                $row['Achternaam'];

        $medewerkers[] = [
            "naam"      => $naam,
            "afdeling"  => "FitForFun",
            "status"    => "Beschikbaar"
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($medewerkers);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database fout"]);
}