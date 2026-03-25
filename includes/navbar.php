<?php
/**
 * FitForFun — gedeelde hoofdnavigatie.
 * - Niet ingelogd: alleen Home + Inloggen
 * - Ingelogd: Home + beheer-items op basis van rol + Uitloggen
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$projectRoot = realpath(dirname(__DIR__));
if ($projectRoot === false) {
    $projectRoot = dirname(__DIR__);
}

$scriptPath = isset($_SERVER['SCRIPT_FILENAME']) ? realpath($_SERVER['SCRIPT_FILENAME']) : false;
$navBase = '';
if ($scriptPath && strpos($scriptPath, $projectRoot) === 0) {
    $currentDir = dirname($scriptPath);
    while ($currentDir !== $projectRoot) {
        $parent = dirname($currentDir);
        if ($parent === $currentDir) {
            $navBase = '';
            break;
        }
        $navBase .= '../';
        $currentDir = $parent;
    }
}

$ingelogd = !empty($_SESSION['ingelogd']) && !empty($_SESSION['gebruiker_id']);
$rol = null;

if ($ingelogd) {
    if (!isset($GLOBALS['pdo'])) {
        require_once $projectRoot . DIRECTORY_SEPARATOR . 'config.php';
    }
    if (empty($_SESSION['rol']) && isset($pdo)) {
        $stmtRol = $pdo->prepare('SELECT Naam FROM rol WHERE GebruikerId = ? AND IsActief = 1 LIMIT 1');
        $stmtRol->execute([$_SESSION['gebruiker_id']]);
        $rol = $stmtRol->fetchColumn() ?: 'Lid';
        $_SESSION['rol'] = $rol;
    } else {
        $rol = $_SESSION['rol'] ?? 'Lid';
    }
}

$isAdmin = ($rol === 'Administrator');
$isMedewerkerOfAdmin = in_array($rol, ['Medewerker', 'Administrator'], true);

$toonAccountBeheren = $isMedewerkerOfAdmin;
$toonMedewerkerBeheren = $isAdmin;
$toonLidBeheren = $isMedewerkerOfAdmin;
$toonLesBeheren = $isMedewerkerOfAdmin;
$toonReserveringBeheren = $isMedewerkerOfAdmin;
$toonDashboard = $isMedewerkerOfAdmin;

$homeUrl = $navBase . 'Informatie/home.php';
$loginUrl = $navBase . 'login.php';
$uitlogUrl = $navBase . 'uitloggen.php';
$accountUrl = $navBase . 'Account registratie/Account beheren/index.php';
$medewerkerUrl = $navBase . 'Medewerker registratie/Medewerker beheren/index.php';
$lidUrl = $navBase . 'Lid registratie/index.php';
$lesUrl = $navBase . 'Les registratie/Overzicht_lessen.php';
$resUrl = $navBase . 'Reservering registratie/Reservering_Registratie.php';
$dashUrl = $navBase . 'Management Dashboard/Dashboard beheren/index.php';
?>
<header class="header">
    <div class="navbar-container">
        <a href="<?= htmlspecialchars($homeUrl) ?>" class="logo">FitForFun</a>
        <div class="hamburger" id="hamburger">
            <i class="fa-solid fa-bars"></i>
        </div>
        <nav class="navbar" id="navbar">
            <div class="close-menu" id="closeMenu">
                <i class="fa-solid fa-xmark"></i>
            </div>
            <ul class="navbar-nav">
                <li>
                    <a class="nav-link" href="<?= htmlspecialchars($homeUrl) ?>">Home</a>
                </li>
                <?php if ($ingelogd): ?>
                    <?php if ($toonAccountBeheren): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($accountUrl) ?>">Account beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonMedewerkerBeheren): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($medewerkerUrl) ?>">Medewerker beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonLidBeheren): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($lidUrl) ?>">Lid beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonLesBeheren): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($lesUrl) ?>">Les beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonReserveringBeheren): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($resUrl) ?>">Reservering beheren</a>
                    </li>
                    <?php endif; ?>
                    <?php if ($toonDashboard): ?>
                    <li>
                        <a class="nav-link" href="<?= htmlspecialchars($dashUrl) ?>">Dashboard beheren</a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="nav-link nav-link-uitloggen" href="<?= htmlspecialchars($uitlogUrl) ?>">Uitloggen</a>
                    </li>
                <?php else: ?>
                    <li>
                        <a class="nav-link nav-link-uitloggen" href="<?= htmlspecialchars($loginUrl) ?>">Inloggen</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="overlay" id="overlay"></div>
    </div>
</header>
