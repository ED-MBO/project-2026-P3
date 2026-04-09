<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Niet ingelogd"]);
    exit();
}

require "../config.php";

// Accepteer zowel JSON-body als POST-data
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? ($_POST['id'] ?? null);
$ingevoerdeNaam = trim($data['naam'] ?? ($_POST['naam'] ?? ''));

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Geen ID opgegeven"]);
    exit();
}

// Rolcontrole — alleen Medewerker of Administrator mag een reservering verwijderen
$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1 LIMIT 1");
$stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
$rol = $stmtRol->fetchColumn();

if (!in_array($rol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Je hebt onvoldoende rechten om deze reservering te verwijderen."]);
    exit();
}

try {
    // Haal de reservering op voor verificatie
    $stmtFetch = $pdo->prepare("SELECT Voornaam, Tussenvoegsel, Achternaam FROM reservering WHERE Id = :id AND IsActief = 1");
    $stmtFetch->execute([":id" => $id]);
    $reservering = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$reservering) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Reservering niet gevonden of al verwijderd"]);
        exit();
    }

    // Bouw volledige naam op voor vergelijking
    $volledigeNaam = $reservering['Voornaam'];
    if (!empty(trim($reservering['Tussenvoegsel']))) {
        $volledigeNaam .= ' ' . $reservering['Tussenvoegsel'];
    }
    $volledigeNaam .= ' ' . $reservering['Achternaam'];

    // Vergelijk de naam (case-insensitive)
    if (strcasecmp(trim($volledigeNaam), trim($ingevoerdeNaam)) !== 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "De ingevoerde naam komt niet overeen. De reservering is NIET verwijderd."]);
        exit();
    }

    // Soft delete: zet IsActief op 0
    $stmt = $pdo->prepare("UPDATE reservering SET IsActief = 0 WHERE Id = :id");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Reservering succesvol verwijderd"]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Reservering niet gevonden of al verwijderd"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . $e->getMessage()]);
}
