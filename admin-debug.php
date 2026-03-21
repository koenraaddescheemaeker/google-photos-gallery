<?php
// admin-debug.php
require_once 'config.php';
require_once 'logger.php';

forcekes_log("--- START DEBUG SESSIE ---");

// 1. Check Env Vars (maskeer de secret voor veiligheid)
forcekes_log("Check Environment Variables", [
    'client_id' => substr(getenv('GOOGLE_CLIENT_ID'), 0, 20) . "...",
    'has_secret' => !empty(getenv('GOOGLE_CLIENT_SECRET')) ? "JA" : "NEE"
]);

// 2. Haal token uit database via jouw functie
$token = getValidAccessToken(); 
forcekes_log("Resultaat getValidAccessToken()", [
    'access_token_prefix' => $token ? substr($token, 0, 15) . "..." : "GEEN TOKEN"
]);

if ($token) {
    // 3. De API aanroep
    $url = "https://photoslibrary.googleapis.com/v1/albums";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    forcekes_log("Google API Response", [
        'http_code' => $httpCode,
        'body' => json_decode($response, true)
    ]);
    curl_close($ch);
}

// 4. TOON DE LOGS OP HET SCHERM
display_forcekes_logs();