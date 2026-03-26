<?php
/** FORCEKES - config.php (Merged Logic Edition) */
ini_set('display_errors', 1); error_reporting(E_ALL);

$googleConfig = [
    'client_id'     => '483664701477-oe11ldk8bitgvc8vi2m7ootvrpbb0ki1.apps.googleusercontent.com',
    'client_secret' => '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',
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php'
];

$supabaseConfig = [
    'url' => 'https://supa.forcekes.be',
    'key' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA'
];

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseConfig;
    $url = rtrim($supabaseConfig['url'], '/') . '/rest/v1/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    $headers = [
        "apikey: " . $supabaseConfig['key'],
        "Authorization: Bearer " . $supabaseConfig['key'],
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch); curl_close($ch);
    return json_decode($response, true);
}

function getValidAccessToken() {
    $res = supabaseRequest('google_tokens?id=eq.1', 'GET');
    if (!$res || !isset($res[0])) return null;
    
    $tokenData = $res[0];
    $expiresAt = strtotime($tokenData['expires_at']);
    
    // Als token bijna verloopt (binnen 5 min), verversen
    if (time() >= ($expiresAt - 300)) {
        return refreshGoogleToken($tokenData['refresh_token']);
    }
    return $tokenData['access_token'];
}

function refreshGoogleToken($refreshToken) {
    global $googleConfig;
    if (!$refreshToken || in_array($refreshToken, ['reset', 'empty'])) return null;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => $googleConfig['client_id'],
            'client_secret' => $googleConfig['client_secret'],
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ])
    ]);
    $res = json_decode(curl_exec($ch), true); curl_close($ch);

    if (isset($res['access_token'])) {
        $expiry = date('c', time() + $res['expires_in']); // ISO 8601 voor Postgres
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at'   => $expiry
        ]);
        return $res['access_token'];
    }
    return null;
}