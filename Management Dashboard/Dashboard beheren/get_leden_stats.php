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

$stmt = $pdo->query("SELECT COUNT(*) FROM lid WHERE IsActief = 1");
$totaal = (int)$stmt->fetchColumn();

$stmt = $pdo->query("
SELECT COUNT(*) FROM lid
WHERE IsActief = 1
AND YEAR(Datumaangemaakt)=YEAR(CURDATE())
AND MONTH(Datumaangemaakt)=MONTH(CURDATE())
");
$nieuw = (int)$stmt->fetchColumn();

$stmt = $pdo->query("
SELECT COUNT(DISTINCT l.Id)
FROM lid l
INNER JOIN reservering r ON l.Relatienummer = r.Nummer
WHERE l.IsActief=1 AND r.IsActief=1
");

$actief = (int)$stmt->fetchColumn();
$inactief = max(0,$totaal-$actief);

echo json_encode([
"totaal"=>$totaal,
"nieuw"=>$nieuw,
"actief"=>$actief,
"inactief"=>$inactief
]);

} catch(PDOException $e){
http_response_code(500);
echo json_encode(["error"=>"Database fout"]);
}