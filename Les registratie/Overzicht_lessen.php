<?php
require_once '../config.php';

try {
    $sql = "SELECT Naam
                  ,Datum
                  ,Tijd
                  ,MinAantalPersonen
                  ,MaxAantalPersonen
                  ,Beschikbaarheid
                  ,Prijs
            FROM les
            WHERE Isactief = 1
            ORDER BY Datum, Tijd";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $lessen = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Fout bij ophalen lessen.");
}

$totaal = count($lessen);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesoverzicht — Beheer</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Overzicht_lessen.css">
   
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <span>AcademiaPro</span>
        <small>Beheerpaneel</small>
    </div>
    <nav class="nav">
        <div class="nav-label">Menu</div>
        <a href="#" class="active">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8"  y1="2" x2="8"  y2="6"/>
                <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
            Lessen
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="avatar">JD</div>
        <div class="sidebar-user-info">
            <strong>Jan de Vries</strong>
            <span>Medewerker</span>
        </div>
    </div>
</aside>

<!-- Main -->
<div class="main">

    <header class="topbar">
        <div style="display:flex; align-items:center; gap:12px;">
            <button class="hamburger" onclick="openSidebar()" aria-label="Menu openen">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="topbar-breadcrumb">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Lessen</span>
            </div>
        </div>
    </header>

    <div class="content">

        <h1 class="page-title">Geplande Lessen</h1>
        <p class="page-subtitle">Overzicht van alle actieve lessen</p>

        <div class="stat-card">
            <div class="stat-value"><?= $totaal ?></div>
            <div class="stat-label">Actieve lessen</div>
        </div>

        <div class="toolbar">
            <div class="search-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input class="search-input" type="text" id="searchInput" placeholder="Zoek op lesnaam…">
            </div>
            <span class="result-count" id="resultCount"><?= $totaal ?> lessen</span>
        </div>

        <div class="table-wrap">
            <table id="lessenTable">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Datum</th>
                        <th class="col-tijd">Tijd</th>
                        <th class="col-minmax">Min / Max</th>
                        <th>Status</th>
                        <th>Prijs</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($totaal > 0): ?>
                    <?php foreach ($lessen as $row):
                        $status = strtolower($row['Beschikbaarheid']);
                        if ($status === 'beschikbaar') {
                            $badgeClass = 'badge-green';
                            $label = 'Beschikbaar';
                        } elseif ($status === 'vol') {
                            $badgeClass = 'badge-amber';
                            $label = 'Vol';
                        } else {
                            $badgeClass = 'badge-red';
                            $label = htmlspecialchars($row['Beschikbaarheid']);
                        }

                        $datum = new DateTime($row['Datum']);
                        $dag = ['Mon'=>'Ma','Tue'=>'Di','Wed'=>'Wo','Thu'=>'Do','Fri'=>'Vr','Sat'=>'Za','Sun'=>'Zo'][$datum->format('D')] ?? $datum->format('D');
                    ?>
                    <tr data-naam="<?= strtolower(htmlspecialchars($row['Naam'])) ?>">
                        <td>
                            <div class="les-cell">
                                <div class="les-icon">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#4f8ef7" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                </div>
                                <span class="les-naam"><?= htmlspecialchars($row['Naam']) ?></span>
                            </div>
                        </td>
                        <td class="date-cell">
                            <strong><?= $datum->format('d M Y') ?></strong>
                            <?= $dag ?>
                        </td>
                        <td class="col-tijd"><?= htmlspecialchars(substr($row['Tijd'], 0, 5)) ?></td>
                        <td class="col-minmax"><?= (int)$row['MinAantalPersonen'] ?> – <?= (int)$row['MaxAantalPersonen'] ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <span class="badge-dot"></span>
                                <?= $label ?>
                            </span>
                        </td>
                        <td class="prijs-cell">€<?= number_format((float)$row['Prijs'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="geen-data">
                            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin:0 auto;display:block;color:var(--text-dim)"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="2"/></svg>
                            Geen geplande lessen gevonden
                            <p>Er zijn momenteel geen actieve lessen in het systeem.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                <span id="footerCount"><?= $totaal ?> lessen weergegeven</span>
                <span>Laatste update: <?= date('d M Y, H:i') ?></span>
            </div>
        </div>

    </div>
</div>

<script src="Overzicht_lessen.js"></script>

</body>
</html>