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
$ingevoerdeAchternaam = trim($data['achternaam'] ?? ($_POST['achternaam'] ?? ''));

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Geen ID opgegeven"]);
    exit();
}

// Rolcontrole — alleen Medewerker of Administrator mag een les verwijderen
$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1 LIMIT 1");
$stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
$rol = $stmtRol->fetchColumn();

if (!in_array($rol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Je hebt onvoldoende rechten om deze les te verwijderen."]);
    exit();
}

try {
    // Haal de les op voor verificatie
    $stmtFetch = $pdo->prepare("
        SELECT r.Achternaam
        FROM les l
        LEFT JOIN reservering r ON l.Datum = r.Datum AND l.Tijd = r.Tijd AND r.IsActief = 1
        WHERE l.Id = :id AND l.IsActief = 1
    ");
    $stmtFetch->execute([":id" => $id]);
    $les = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$les) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Les niet gevonden of al verwijderd"]);
        exit();
    }

    // Vergelijk de achternaam (case-insensitive)
    if (strcasecmp(trim($les['Achternaam'] ?? ''), $ingevoerdeAchternaam) !== 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "De ingevoerde achternaam komt niet overeen. De les is NIET verwijderd."]);
        exit();
    }

    // Soft delete: zet IsActief op 0
    $stmt = $pdo->prepare("UPDATE les SET IsActief = 0 WHERE Id = :id");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Les succesvol verwijderd"]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Les niet gevonden of al verwijderd"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . $e->getMessage()]);
}
