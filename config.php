<?php
/**
 * FORCEKES 2026 - ARCHITECTUUR CONFIGURATIE
 * De fundering is heilig.
 */

// 1. Database Coördinaten
// In Coolify vul je deze in bij 'Environment Variables'.
$host     = getenv('DB_HOST')     ?: 'db.xxxxxx.supabase.co'; // Je Supabase host
$port     = getenv('DB_PORT')     ?: '5432';
$dbname   = getenv('DB_NAME')     ?: 'postgres';
$user     = getenv('DB_USER')     ?: 'postgres';
$password = getenv('DB_PASSWORD') ?: 'JE_WACHTWOORD_HIER';

try {
    // 2. De DSN (Data Source Name) voor PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

    // 3. De Vlijmscherpe PDO Verbinding
    // We noemen de variabele expliciet $db zoals afgesproken.
    $db = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Gooi errors bij fouten
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Haal data op als associatieve arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Gebruik echte prepared statements voor veiligheid
    ]);

    // De verbinding is geslaagd. De architectuur ademt.
} catch (PDOException $e) {
    // Als de kluis op slot blijft, loggen we het vlijmscherp
    error_log("Databaseverbinding mislukt: " . $e->getMessage());
    
    // In productie tonen we een humane foutmelding, geen technische details
    die("De kluis is momenteel niet bereikbaar. Onze excuses voor het ongemak.");
}

// 4. Globale App Instellingen
$config = [
    'app_name'     => 'FORCEKES 2026',
    'base_url'     => 'https://forcekes.be',
    'museum_limit' => 100, // De 'Harde Grens' voor de ID's
];

// De uil waakt over de sessies
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}