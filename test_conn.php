<?php
require_once 'config.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Diagnose Systeem</h2>";

// 1. Test Supabase
echo "1. Testen van Supabase verbinding... ";
$start = microtime(true);
$ch = curl_init($supabaseUrl . "/rest/v1/google_tokens?select=*");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5); // We wachten max 5 seconden
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $supabaseKey", "Authorization: Bearer $supabaseKey"]);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo "<b style='color:red'>FAILED!</b> Fout: $err<br>";
} else {
    echo "<b style='color:green'>OK!</b> (Code: $httpCode) in " . round(microtime(true) - $start, 2) . "s<br>";
}

// 2. Test Google Variabelen
echo "2. Testen van Google Variabelen... ";
$id = getenv('GOOGLE_CLIENT_ID');
if ($id) {
    echo "<b style='color:green'>GEVONDEN!</b> (Eindigt op: " . substr($id, -10) . ")<br>";
} else {
    echo "<b style='color:red'>NIET GEVONDEN!</b> (Controleer Coolify Env Vars)<br>";
}