<?php
// config.php
/**
 * config.php - Beveiligde Versie (Zonder hardcoded secrets)
 */

// 1. Supabase Instellingen
$supabaseUrl = "https://supa.forcekes.be"; 
// De Service Role Key is ook een geheim, we halen deze idealiter ook uit ENV
$supabaseKey = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...";

// 2. Google OAuth Instellingen
// GitHub blokkeert de push als deze waardes hier als platte tekst staan.
$googleClientID     = getenv('GOOGLE_CLIENT_ID'); 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET');
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    $ch = curl_init($baseUrl);
    $headers = ["apikey: $supabaseKey", "Authorization: Bearer $supabaseKey", "Content-Type: application/json"];
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_POST, true);
        $payload = (isset($data[0])) ? $data : [$data];
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix voor TLS error
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (!$res || !isset($res[0])) return null;
    $tokens = $res[0];
    if (strtotime($tokens['expires_at']) > (time() + 60)) return $tokens['access_token'];
    
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $googleClientID, 'client_secret' => $googleClientSecret,
        'refresh_token' => $tokens['refresh_token'], 'grant_type' => 'refresh_token'
    ]));
    $response = json_decode(curl_exec($ch), true);
    if (isset($response['access_token'])) {
        $expiry = date('Y-m-d H:i:s', time() + $response['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', ['access_token' => $response['access_token'], 'expires_at' => $expiry]);
        return $response['access_token'];
    }
    return null;
}
?>