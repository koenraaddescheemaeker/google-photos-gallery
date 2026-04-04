<?php
// config.php - De Onverwoestbare Verbinding
$raw_host = getenv('DB_HOST')     ?: 'supa.forcekes.be';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'postgres';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'x0NoycAEhtoaUuziBUEzEML88NpnwzQ4';

// Vlijmscherpe opschoning van de hostnaam
$host = str_replace(['https://', 'http://', '/'], '', $raw_host);

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5
    ]);
} catch (PDOException $e) {
    die("Kluis-fout: " . $e->getMessage());
}

define('MUSEUM_THRESHOLD', 100);