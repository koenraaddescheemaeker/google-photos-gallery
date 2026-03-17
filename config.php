<?php
// config.php

// 1. Verbinding (Gebruik het bridge IP dat werkte)
$supabaseUrl = "http://172.17.0.1:8000"; 

// 2. Gebruik de SERVICE_ROLE KEY (uit je ENV lijst) voor maximale snelheid/rechten
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA";

$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$googleRedirectUri  = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    
    $ch = curl_init($baseUrl);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ];
    
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = "Prefer: return=representation";
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Verhoogd
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);        // Verhoogd om 504 te voorkomen
    
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) return ['error' => $err];
    return json_decode($response, true);
}

function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (!$res || isset($res['error']) || !isset($res[0])) return null;
    $tokens = $res[0];
    if (empty($tokens['refresh_token'])) return null;
    if (!empty($tokens['expires_at']) && strtotime($tokens['expires_at']) > (time() + 60)) {
        return $tokens['access_token'];
    }
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $googleClientID, 'client_secret' => $googleClientSecret,
        'refresh_token' => $tokens['refresh_token'], 'grant_type' => 'refresh_token'
    ]));
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    if (isset($response['access_token'])) {
        $newExpiry = date('Y-m-d H:i:s', time() + $response['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', ['access_token' => $response['access_token'], 'expires_at' => $newExpiry]);
        return $response['access_token'];
    }
    return null;
}
?>