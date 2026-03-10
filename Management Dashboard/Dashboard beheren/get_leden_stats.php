<?php
session_start();
if (empty($_SESSION['ingelogd']) || empty($_SESSION['gebruiker_id'])) {
    header('Location: ../../login.php');
    exit();
}
require '../../config.php';

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