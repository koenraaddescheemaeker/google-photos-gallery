<?php
// config.php - De Nieuwe Fundering op new.forcekes.be
$host     = getenv('DB_HOST')     ?: 'supa.forcekes.be';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'postgres';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'x0NoycAEhtoaUuziBUEzEML88NpnwzQ4';

// De arend zorgt voor een zuivere host
$host = str_replace(['https://', 'http://', '/'], '', $host);

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Verbindingsfout: " . $e->getMessage());
}

// De app-locatie
$base_url = "https://new.forcekes.be";