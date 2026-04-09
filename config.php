<?php
// config.php - De Nieuwe Fundering
$db_host = 'supa.forcekes.be';
$db_name = 'postgres';
$db_user = 'postgres';
$db_pass = 'x0NoycAEhtoaUuziBUEzEML88NpnwzQ4';

try {
    // We gebruiken poort 5432 en dwingen een timeout af van 10 seconden
    $dsn = "pgsql:host=$db_host;port=5432;dbname=$db_name;sslmode=require";
    $db = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    // De uil knikt: verbinding geslaagd.
} catch (PDOException $e) {
    die("Architect, de kluis blijft dicht: " . $e->getMessage());
}