<?php
require_once 'config.php';

// Foutopsporing aan
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['code'])) {
    echo "Bezig met inwisselen van code voor tokens...<br>";

    // 1. Wissel code in voor tokens bij Google
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $googleClientID,
        'client_secret' => $googleClientSecret,
        'redirect_uri'  => $googleRedirectUri,
        'grant_type'    => 'authorization_code',
        'code'          => $_GET['code']
    ]));

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        echo "✅ Tokens ontvangen van Google.<br>";
        
        $access_token  = $response['access_token'];
        $refresh_token = $response['refresh_token'] ?? null; // Alleen bij eerste keer
        $expires_in    = $response['expires_in'];
        $expires_at    = date('Y-m-d H:i:s', time() + $expires_in);

        // 2. Opslaan in Supabase via de werkende helper in config.php
        echo "Bezig met opslaan in Supabase...<br>";
        
        $data = [
            'id'            => 1, // We gebruiken ID 1 voor de hoofdgebruiker
            'access_token'  => $access_token,
            'expires_at'    => $expires_at
        ];
        
        // Als we een refresh_token hebben, voegen we die toe
        if ($refresh_token) {
            $data['refresh_token'] = $refresh_token;
        }

        // Gebruik UPSERT (via onze helper) om de tokens op te slaan
        $res = supabaseRequest('google_tokens', 'UPSERT', $data);

        if ($res && !isset($res['error'])) {
            echo "✅ Succesvol opgeslagen! Je wordt nu doorgestuurd...";
            header("Refresh: 2; url=admin.php");
        } else {
            echo "❌ Database Fout!<br>";
            echo "Antwoord van Supabase: <pre>" . print_r($res, true) . "</pre>";
        }
    } else {
        echo "❌ Google OAuth Fout: " . ($response['error_description'] ?? 'Onbekende fout');
    }
} else {
    echo "Geen autorisatiecode ontvangen.";
}