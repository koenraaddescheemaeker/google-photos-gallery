<?php
require_once 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Diagnose na Project Verhuizing</h2>";

echo "Testen van interne verbinding naar: <b>$supabaseUrl</b>... ";
$start = microtime(true);

$res = supabaseRequest('google_tokens?select=*');

if (is_array($res)) {
    echo "<b style='color:green'>OK!</b> Verbinding geslaagd.<br>";
    echo "Aantal rijen in database: " . count($res) . "<br>";
} else {
    echo "<b style='color:red'>FOUT!</b> Kon Supabase niet bereiken.<br>";
    echo "Check of de naam 'supabase-kong' klopt in je Docker Compose.";
}

echo "<br>Google ID aanwezig: " . (getenv('GOOGLE_CLIENT_ID') ? "✅ Ja" : "❌ Nee");