<?php
require "../../config.php";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $voornaam = $_POST["voornaam"];
    $tussen = $_POST["tussenvoegsel"];
    $achternaam = $_POST["achternaam"];
    $functie = $_POST["functie"];
    $nummer = $_POST["nummer"];

    $sql = "INSERT INTO medewerker 
    (Voornaam, Tussenvoegsel, Achternaam, Medewerkersoort, Nummer, IsActief)
    VALUES (:v, :t, :a, :f, :n, 1)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":v" => $voornaam,
        ":t" => $tussen,
        ":a" => $achternaam,
        ":f" => $functie,
        ":n" => $nummer
    ]);

    header("Location: index.html");
    exit;

}