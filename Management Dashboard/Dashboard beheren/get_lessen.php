<?php
require '../../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            Id
            ,Naam
            ,Datum
            ,Tijd
            ,Prijs
            ,Beschikbaarheid
        FROM les
        WHERE IsActief = 1
        ORDER BY Datum ASC, Tijd ASC
    ");

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database fout"]);
}