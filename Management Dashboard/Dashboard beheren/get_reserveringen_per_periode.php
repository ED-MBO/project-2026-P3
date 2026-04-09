<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Niet ingelogd"]);
    exit();
}
require '../../config.php';

header('Content-Type: application/json');

// Rolcontrole
$stmtRol = $pdo->prepare("SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1");
$stmtRol->execute([$_SESSION['gebruiker_id']]);
$mijnRol = $stmtRol->fetchColumn() ?: 'Lid';

if (!in_array($mijnRol, ['Medewerker', 'Administrator'])) {
    http_response_code(403);
    echo json_encode(["error" => "Geen toegang"]);
    exit();
}

$type = $_GET['type'] ?? 'maand';
$jaar = (int)($_GET['jaar'] ?? date('Y'));

try {
    // Halen we de beschikbare jaren op uit de reserveringen
    $jarenStmt = $pdo->query("
        SELECT DISTINCT YEAR(Datum) AS jaar
        FROM reservering
        WHERE IsActief = 1
        ORDER BY jaar DESC
    ");
    $jarenResource = $jarenStmt->fetchAll(PDO::FETCH_COLUMN);
    $jaren = array_map('intval', $jarenResource);

    if (!in_array((int)date('Y'), $jaren)) {
        array_unshift($jaren, (int)date('Y'));
    }

    if ($type === "jaar") {
        // Groeperen per jaar
        $stmt = $pdo->query("
            SELECT YEAR(Datum) AS periode, COUNT(*) AS aantal
            FROM reservering
            WHERE IsActief = 1
            GROUP BY YEAR(Datum)
            ORDER BY periode
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = []; $aantal = [];
        foreach ($rows as $r) {
            $labels[] = $r["periode"];
            $aantal[] = (int)$r["aantal"];
        }
    } else {
        // Groeperen per maand voor een specifiek jaar
        $stmt = $pdo->prepare("
            SELECT MONTH(Datum) AS m, COUNT(*) AS aantal
            FROM reservering
            WHERE IsActief = 1 AND YEAR(Datum) = ?
            GROUP BY MONTH(Datum)
        ");
        $stmt->execute([$jaar]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r["m"]] = (int)$r["aantal"];
        }

        $months = ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
        $labels = []; $aantal = [];
        for ($i = 1; $i <= 12; $i++) {
            $n = $map[$i] ?? 0;
            $labels[] = $months[$i - 1];
            $aantal[] = $n;
        }
    }

    echo json_encode([
        "labels" => $labels,
        "aantal" => $aantal,
        "jaren" => $jaren
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database fout bij ophalen reserveringen"]);
}

// Erorr voor overzicht Aantal reserveringen per periode
// http_response_code(500); exit();
