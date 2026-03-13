<?php
session_start();
require_once __DIR__ . '/config.php';

$gebruikerId = $_SESSION['gebruiker_id'] ?? null;

if ($gebruikerId) {
    try {
        $stmt = $pdo->prepare("UPDATE gebruiker SET IsIngelogd = 0, Uitgelogd = CURRENT_TIMESTAMP WHERE Id = ?");
        $stmt->execute([$gebruikerId]);
    } catch (PDOException $e) {
        // Doorgaan met uitloggen ook bij DB-fout
    }
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: Informatie/index.html');
exit();
 