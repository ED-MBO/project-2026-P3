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
<html>
<head>
    <title>Overzicht Geplande Lessen</title>
    <link rel="stylesheet" href="Overzicht_lessen.css">
</head>
<body>

<h2 style="text-align:center;">Overzicht Geplande Lessen</h2>

<table>
    <tr>
        <th>Naam Les</th>
        <th>Datum</th>
        <th>Tijd</th>
        <th>Min. Personen</th>
        <th>Max. Personen</th>
        <th>Beschikbaarheid</th>
        <th>Prijs (€)</th>
    </tr>

    <?php if (count($lessen) > 0): ?>

        <?php foreach ($lessen as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['Naam']) ?></td>
                <td><?= htmlspecialchars($row['Datum']) ?></td>
                <td><?= htmlspecialchars($row['Tijd']) ?></td>
                <td><?= htmlspecialchars($row['MinAantalPersonen']) ?></td>
                <td><?= htmlspecialchars($row['MaxAantalPersonen']) ?></td>
                <td><?= htmlspecialchars($row['Beschikbaarheid']) ?></td>
                <td><?= htmlspecialchars($row['Prijs']) ?></td>
            </tr>
        <?php endforeach; ?>

    <?php else: ?>
        <tr>
            <td colspan="7">Geen geplande lessen beschikbaar</td>
        </tr>
    <?php endif; ?>

</table>

</body>
</html>
