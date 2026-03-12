<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
 
try {
    $stmt = $pdo->query("
        SELECT
            Id,
            Voornaam,
            Tussenvoegsel,
            Achternaam,
            Mobiel,
            Email,
            Datumaangemaakt,
            IsActief
        FROM lid
        ORDER BY Achternaam, Voornaam
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $leden = [];
    foreach ($rows as $r) {
        $naam = implode(' ', array_filter([$r['Voornaam'], $r['Tussenvoegsel'] ?? '', $r['Achternaam']]));
        $leden[] = [
            'Id'         => (int) $r['Id'],
            'Naam'       => $naam,
            'Mobiel'     => $r['Mobiel'] ?? '',
            'Email'      => $r['Email'],
            'LidSinds'   => date('d-m-Y', strtotime($r['Datumaangemaakt'])),
            'Status'     => (int) $r['IsActief'] === 1 ? 'Actief' : 'Inactief',
        ];
    }

    echo json_encode($leden);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database fout']);
}
