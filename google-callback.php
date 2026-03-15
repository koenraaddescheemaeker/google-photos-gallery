<?php
require_once 'config.php';

if (isset($_GET['code'])) {
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $googleClientID,
        'client_secret' => $googleClientSecret,
        'redirect_uri'  => $googleRedirectUri,
        'grant_type'    => 'authorization_code'
    ]));
    
    $tokens = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($tokens['refresh_token'])) {
        // Opslaan in Supabase via je bestaande functie
        supabaseRequest('google_tokens', 'POST', [
            'id'            => 1,
            'refresh_token' => $tokens['refresh_token'],
            'access_token'  => $tokens['access_token'],
            'expires_at'    => date('Y-m-d H:i:s', time() + $tokens['expires_in'])
        ]);
        echo "<h1>Succes!</h1><p>De Google-verbinding is gemaakt. Je kunt dit tabblad sluiten.</p>";
    } else {
        echo "Fout bij ophalen tokens: " . print_r($tokens, true);
    }
}