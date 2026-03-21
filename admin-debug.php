<?php
// admin-debug.php
require_once 'config.php';
require_once 'logger.php';

forcekes_log("--- START DEBUG SESSIE ---");

// 1. Check Environment
forcekes_log("Check Env", [
    'client_id_prefix' => substr(getenv('GOOGLE_CLIENT_ID'), 0, 15),
    'has_secret' => !empty(getenv('GOOGLE_CLIENT_SECRET'))
]);

// 2. Haal token uit Supabase
$tokenData = getValidAccessToken(); // We gaan ervan uit dat dit je functie is
forcekes_log("Token verkregen via getValidAccessToken", [
    'access_token_exists' => !empty($tokenData)
]);

if (!$tokenData) {
    forcekes_log("CRITISCHE FOUT: Geen token kunnen ophalen.");
    die("Check debug_oauth.log");
}

// 3. De API aanroep met volledige logging
$url = "https://photoslibrary.googleapis.com/v1/albums";
$ch = curl_init($url);

$headers = [
    "Authorization: Bearer $tokenData",
    "Accept: application/json"
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true); // Extra info
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Vang de ruwe output op
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

forcekes_log("API Response details", [
    'http_code' => $httpCode,
    'curl_error' => $curlError,
    'raw_body' => json_decode($response, true)
]);

curl_close($ch);

echo "<h1>Debug klaar.</h1>";
echo "<p>Bekijk <b>debug_oauth.log</b> voor de volledige trace.</p>";
if ($httpCode === 200) {
    echo "<p style='color:green;'>SUCCES! De albums zijn binnen.</p>";
} else {
    echo "<p style='color:red;'>FOUT: Code $httpCode. Check de log.</p>";
}