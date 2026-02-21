<?php
// Database connectie includen
require_once '../config.php'; // dit moet je PDO bestand zijn

try {

    // Query voorbereiden
    $sql = "SELECT Naam
                  ,Datum
                  ,Tijd
                  ,MinAantalPersonen
                  ,MaxAantalPersonen
                  ,Beschikbaarheid
                  ,Prijs
            FROM les
            WHERE Isactief = 1
            ORDER BY Datum
                    ,Tijd";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $lessen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Fout bij ophalen lessen.");
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Overzicht Geplande Lessen</title>
    <link rel="stylesheet" href="Overzicht_lessen.css">
</head>
<body>

<div class="container">
    <h1>📅 Overzicht Geplande Lessen</h1>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Naam Les</th>
                    <th>Datum</th>
                    <th>Tijd</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Status</th>
                    <th>Prijs (€)</th>
                </tr>
            </thead>
            <tbody>

            <?php if (count($lessen) > 0): ?>
                <?php foreach ($lessen as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Naam']) ?></td>
                        <td><?= htmlspecialchars($row['Datum']) ?></td>
                        <td><?= htmlspecialchars($row['Tijd']) ?></td>
                        <td><?= htmlspecialchars($row['MinAantalPersonen']) ?></td>
                        <td><?= htmlspecialchars($row['MaxAantalPersonen']) ?></td>
                        <td class="status"><?= htmlspecialchars($row['Beschikbaarheid']) ?></td>
                        <td class="prijs">€<?= htmlspecialchars($row['Prijs']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="geen-data">
                        Geen geplande lessen beschikbaar
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>
    </div>
</div>

</body>
</html>
