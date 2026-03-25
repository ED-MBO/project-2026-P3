<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Niet ingelogd"]);
    exit();
}

require '../../config.php';

$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
$stmtRol->execute([$_SESSION['gebruiker_id']]);
$mijnRol = $stmtRol->fetchColumn() ?: 'Lid';
if (!in_array($mijnRol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Geen toegang"]);
    exit();
}

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