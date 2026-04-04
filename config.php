<?php
// config.php - De onverwoestbare bron
$host     = 'https://supa.forcekes.be'; // Gebaseerd op je logs
$port     = '5432';
$dbname   = 'postgres';
$user     = 'postgres';
$password = 'x0NoycAEhtoaUuzIBUeZEML88NpnwzQ4'; // VLIJMSCHERP INVULLEN!

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // De uil waarschuwt: toon de echte fout even voor de debug-fase
    die("Kluis-fout: " . $e->getMessage()); 
}

// De Harde Grens ID
define('MUSEUM_THRESHOLD', 100);