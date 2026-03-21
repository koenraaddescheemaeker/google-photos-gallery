<?php
// admin-debug.php
require_once 'config.php';
require_once 'logger.php';

forcekes_log("--- START SCOPE CHECK ---");

$token = getValidAccessToken(); 

if ($token) {
    forcekes_log("Token uit script ontvangen", ["prefix" => substr($token, 0, 15)]);

    // Vraag aan Google wat de scopes zijn van DEZE specifieke token
    $checkUrl = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=" . $token;
    $info = json_decode(file_get_contents($checkUrl), true);

    forcekes_log("Google Token Info", [
        'azp' => $info['azp'] ?? 'onbekend', // Voor welke client id is dit?
        'scope' => $info['scope'] ?? 'GEEN SCOPES GEVONDEN',
        'exp' => $info['exp'] ?? 'n.v.t.'
    ]);

    if (!isset($info['scope']) || (strpos($info['scope'], 'photoslibrary') === false)) {
        forcekes_log("⚠️ CONCLUSIE: Deze token heeft GEEN foto-rechten!");
    } else {
        forcekes_log("✅ CONCLUSIE: Token heeft wel rechten. Probleem ligt bij de API-endpoint.");
    }
}

display_forcekes_logs();