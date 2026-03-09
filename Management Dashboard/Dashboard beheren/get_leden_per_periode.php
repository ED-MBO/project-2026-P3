<?php
require '../../config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'maand';
$jaar = (int)($_GET['jaar'] ?? date('Y'));

try {

$jarenStmt = $pdo->query("
SELECT DISTINCT YEAR(Datumaangemaakt) AS jaar
FROM lid
WHERE IsActief = 1
ORDER BY jaar DESC
");

$jaren = $jarenStmt->fetchAll(PDO::FETCH_COLUMN);

if(!in_array(date('Y'),$jaren)){
array_unshift($jaren,date('Y'));
}

$actiefStmt = $pdo->query("
SELECT COUNT(DISTINCT l.Id)
FROM lid l
INNER JOIN reservering r ON l.Relatienummer = r.Nummer
WHERE l.IsActief=1 AND r.IsActief=1
");

$actief = (int)$actiefStmt->fetchColumn();

$totaalStmt = $pdo->query("SELECT COUNT(*) FROM lid WHERE IsActief=1");
$totaalNu = (int)$totaalStmt->fetchColumn();
$inactief = max(0,$totaalNu-$actief);

if($type === "jaar"){

$stmt = $pdo->query("
SELECT YEAR(Datumaangemaakt) AS periode, COUNT(*) AS nieuw
FROM lid
WHERE IsActief=1
GROUP BY YEAR(Datumaangemaakt)
ORDER BY periode
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels=[]; $nieuw=[]; $totaal=[]; $sum=0;

foreach($rows as $r){
$sum += $r["nieuw"];
$labels[] = $r["periode"];
$nieuw[] = (int)$r["nieuw"];
$totaal[] = $sum;
}

}else{

$startStmt = $pdo->prepare("
SELECT COUNT(*) FROM lid
WHERE IsActief=1 AND YEAR(Datumaangemaakt) < ?
");
$startStmt->execute([$jaar]);

$start = (int)$startStmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT MONTH(Datumaangemaakt) AS m, COUNT(*) AS nieuw
FROM lid
WHERE IsActief=1 AND YEAR(Datumaangemaakt)=?
GROUP BY MONTH(Datumaangemaakt)
");

$stmt->execute([$jaar]);

$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

$map=[];
foreach($rows as $r){
$map[$r["m"]]=$r["nieuw"];
}

$months=['Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];

$labels=[];$nieuw=[];$totaal=[];
$sum=$start;

for($i=1;$i<=12;$i++){

$n=$map[$i]??0;
$sum+=$n;

$labels[]=$months[$i-1];
$nieuw[]=$n;
$totaal[]=$sum;

}

}

echo json_encode([
"labels"=>$labels,
"nieuw"=>$nieuw,
"totaal"=>$totaal,
"actief"=>$actief,
"inactief"=>$inactief,
"jaren"=>$jaren
]);

}catch(PDOException $e){
http_response_code(500);
echo json_encode(["error"=>"Database fout"]);
}

// fetch("invalid_url").catch(() => toonFout("Unhappy scenario getest!"));