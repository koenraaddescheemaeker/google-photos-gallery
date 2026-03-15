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

    if (isset($tokens['refresh_token']) || isset($tokens['access_token'])) {
        // We bereiden de data voor
        $payload = [
            'id'            => 1,
            'access_token'  => $tokens['access_token'],
            'expires_at'    => date('Y-m-d H:i:s', time() + ($tokens['expires_in'] ?? 3600))
        ];

        // Alleen overschrijven als we een NIEUWE refresh token krijgen
        // (Google geeft deze soms alleen de allereerste keer)
        if (isset($tokens['refresh_token'])) {
            $payload['refresh_token'] = $tokens['refresh_token'];
        }

        // Gebruik PATCH om de bestaande rij (id=1) bij te werken
        // Als dit faalt (omdat rij 1 nog niet bestaat), doen we een POST
        $res = supabaseRequest('google_tokens?id=eq.1', 'PATCH', $payload);
        
        // Als PATCH niets heeft aangepast (lege array), probeer dan POST
        if (empty($res)) {
            supabaseRequest('google_tokens', 'POST', $payload);
        }

        echo "<h1>Succes!</h1><p>De Google-verbinding is gemaakt en opgeslagen. <a href='index.php'>Klik hier om naar het album te gaan.</a></p>";
    } else {
        echo "<h1>Fout!</h1><p>Geen tokens ontvangen van Google. Details:</p><pre>";
        print_r($tokens);
        echo "</pre>";
    }
} else {
    echo "Geen autorisatiecode gevonden in de URL.";
}