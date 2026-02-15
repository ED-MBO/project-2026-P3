<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "fitForFunDB";

$conn = mysqli_connect($host,$user,$password,$database);

if (!$conn) {
    die("Connectie mislukt: " . mysqli_connect_error());
}

echo "Verbonden met database!";