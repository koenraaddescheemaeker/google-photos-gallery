<?php
require_once 'config.php';

// Zorg dat we fouten zien
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['code'])) {
    echo "Bezig met inwisselen van code voor tokens...<br>";

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
    
    $token_res = curl_exec($ch);
    $tokens = json_decode($token_res, true);
    curl_close($ch);

    if (isset($tokens['access_token'])) {
        echo "✅ Tokens ontvangen van Google.<br>";
        
        $payload = [
            'id'            => 1,
            'access_token'  => $tokens['access_token'],
            'expires_at'    => date('Y-m-d H:i:s', time() + ($tokens['expires_in'] ?? 3600))
        ];

        if (isset($tokens['refresh_token'])) {
            $payload['refresh_token'] = $tokens['refresh_token'];
        }

        echo "Bezig met opslaan in Supabase...<br>";

        // We proberen een 'Upsert' (Update of Insert)
        // We sturen een extra header mee om Supabase te dwingen de rij te overschrijven als id=1 al bestaat
        $url = $supabaseUrl . "/rest/v1/google_tokens";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabaseKey",
            "Authorization: Bearer $supabaseKey",
            "Content-Type: application/json",
            "Prefer: resolution=merge-duplicates" // Dit is de 'Upsert' magie
        ]);
        
        $db_res = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300) {
            echo "<h1 style='color:green'>🔥 Dubbel Succes!</h1>";
            echo "<p>De tokens staan nu veilig in de database. Je kunt nu naar <a href='index.php'>het album</a>.</p>";
        } else {
            echo "<h1 style='color:red'>❌ Database Fout!</h1>";
            echo "Status code: " . $http_code . "<br>";
            echo "Antwoord van Supabase: <pre>" . htmlspecialchars($db_res) . "</pre>";
        }
    } else {
        echo "<h1>❌ Google Fout!</h1><pre>" . print_r($tokens, true) . "</pre>";
    }
} else {
    echo "Geen code ontvangen.";
}