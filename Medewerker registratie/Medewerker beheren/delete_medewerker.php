<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Niet ingelogd"]);
    exit();
}

require "../../config.php";

// Check if ID and surname are provided (can be POST or JSON)
$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? ($_POST['id'] ?? null);
$ingevoerdeAchternaam = trim($data['achternaam'] ?? ($_POST['achternaam'] ?? ''));

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Geen ID opgegeven"]);
    exit();
}

// Rolcontrole — alleen Administrator mag een medewerker verwijderen
$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = :id AND IsActief = 1 LIMIT 1");
$stmtRol->execute([":id" => $_SESSION['gebruiker_id']]);
$rol = $stmtRol->fetchColumn();

if ($rol !== "Administrator") {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Je hebt onvoldoende rechten om deze medewerker te verwijderen."]);
    exit();
}

try {
    // Halen we de achternaam op voor verificatie
    $stmtFetch = $pdo->prepare("SELECT Achternaam FROM medewerker WHERE Id = :id AND IsActief = 1");
    $stmtFetch->execute([":id" => $id]);
    $medewerker = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    if (!$medewerker) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Medewerker niet gevonden of al verwijderd"]);
        exit();
    }

    // Vergelijk de achternaam (case-insensitive)
    if (strcasecmp($medewerker['Achternaam'], $ingevoerdeAchternaam) !== 0) {
        http_response_code(400); // Bad Request
        echo json_encode(["success" => false, "message" => "De ingevoerde achternaam komt niet overeen. De medewerker is NIET verwijderd."]);
        exit();
    }

    // Soft delete: zet IsActief op 0 zodat de medewerker niet meer getoond wordt in get_medewerkers.php
    $stmt = $pdo->prepare("UPDATE medewerker SET IsActief = 0 WHERE Id = :id");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Medewerker succesvol verwijderd"]);
    } else {
        // Mogelijk was de medewerker al op IsActief = 0
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Medewerker niet gevonden of al verwijderd"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Fout bij verwijderen: " . $e->getMessage()]);
}
