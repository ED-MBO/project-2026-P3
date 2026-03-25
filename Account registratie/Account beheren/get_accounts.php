<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
require "../../config.php";

$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
$stmtRol->execute([$_SESSION['gebruiker_id']]);
$mijnRol = $stmtRol->fetchColumn() ?: 'Lid';
if (!in_array($mijnRol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Geen toegang"]);
    exit();
}

try {
    $sql = "SELECT 
                g.Id,
                g.Voornaam,
                g.Tussenvoegsel,
                g.Achternaam,
                g.Gebruikersnaam,
                g.IsIngelogd,
                (SELECT r.Naam FROM rol r WHERE r.GebruikerId = g.Id AND r.IsActief = 1 LIMIT 1) AS Rol
            FROM gebruiker g
            WHERE g.IsActief = 1
            ORDER BY g.Voornaam, g.Achternaam";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $accounts = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $naam = $row['Voornaam'] . " " .
                ($row['Tussenvoegsel'] ? $row['Tussenvoegsel'] . " " : "") .
                $row['Achternaam'];

        $status = $row['IsIngelogd'] ? 'Ingelogd' : 'Uitgelogd';
        $rol = $row['Rol'] ?? '—';

        $accounts[] = [
            "id" => $row['Id'],
            "naam" => $naam,
            "gebruikersnaam" => $row['Gebruikersnaam'],
            "rol" => $rol,
            "status" => $status
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($accounts);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database fout"]);
}
