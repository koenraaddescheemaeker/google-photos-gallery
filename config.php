<?php
/**
 * FORCEKES - config.php (Hardcoded Edition)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIGURATIE ---
$googleConfig = [
    'client_id'     => '483664701477-oe11ldk8bitgvc8vi2m7ootvrpbb0ki1.apps.googleusercontent.com',
    'client_secret' => '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'
];

$supabaseConfig = [
    'url' => 'https://supa.forcekes.be', // Bijv: https://xyz.supabase.co
    'key' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA'
];
//was error van mij ---
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseConfig;
    $url = rtrim($supabaseConfig['url'], '/') . '/rest/v1/' . $endpoint;

    $ch = curl_init($url);
    $headers = [
        "apikey: " . $supabaseConfig['key'],
        "Authorization: Bearer " . $supabaseConfig['key'],
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getValidAccessToken() {
    global $googleConfig;
    $res = supabaseRequest('google_tokens?id=eq.1', 'GET');
    if (!$res || !isset($res[0])) return null;

    $tokenData = $res[0];
    $expiresAt = $tokenData['expires_at'] ?? null;

    if (!$expiresAt || strtotime($expiresAt) < (time() + 60)) {
        return refreshGoogleToken($tokenData['refresh_token'] ?? null);
    }

    return $tokenData['access_token'];
}

function refreshGoogleToken($refreshToken) {
    global $googleConfig;
    if (!$refreshToken || in_array($refreshToken, ['reset', 'empty'])) return null;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $googleConfig['client_id'],
        'client_secret' => $googleConfig['client_secret'],
        'refresh_token' => $refreshToken,
        'grant_type'    => 'refresh_token'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res['access_token'])) {
        $newExpiry = date('Y-m-d H:i:sO', time() + $res['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at'   => $newExpiry
        ]);
        return $res['access_token'];
    }
    return null;
}