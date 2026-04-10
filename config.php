<?php
// config.php - De Gezuiverde Fundering
$host     = getenv('DB_HOST')     ?: 'supabase-db';
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'postgres';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'x0NoycAEhtoaUuzIBUeZEML88NpnwzQ4';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=disable";
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);
} catch (PDOException $e) {
    die("Lasknaat-fout (DB): " . $e->getMessage());
}

define('MUSEUM_THRESHOLD', 100);
