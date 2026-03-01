<?php
require "../../config.php";

try {
    $sql = "SELECT 
                 Voornaam
                ,Tussenvoegsel
                ,Achternaam
                ,Medewerkersoort
                ,Nummer
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
            "naam" => $naam
            ,"functie" => $row['Medewerkersoort']
            ,"afdeling" => "FitForFun"
            ,"status" => "Beschikbaar"
            ,"nummer" => $row['Nummer']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($medewerkers);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database fout"]);
}