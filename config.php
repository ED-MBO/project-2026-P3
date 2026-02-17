<?php

$host = "localhost";
$dbname = "fitForFunDB";
$user = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Verbonden met database!";  // Alleen gebruiken voor testen
} catch (PDOException $e) {
    die("Connectie mislukt.");
}
