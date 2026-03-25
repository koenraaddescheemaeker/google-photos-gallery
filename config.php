<?php
/**
 * FORCEKES - config.php
 * Gecentraliseerde configuratie en database functies.
 */

// Foutrapportage voor premium debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = getenv('SUPABASE_URL') . '/rest/v1/' . $endpoint;
    $apiKey = getenv('SUPABASE_SERVICE_ROLE_KEY');

    $ch = curl_init($url);
    $headers = [
        "apikey: $apiKey",
        "Authorization: Bearer $apiKey",
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
    $res = supabaseRequest('google_tokens?id=eq.1', 'GET');
    if (!$res || !isset($res[0])) return null;

    $tokenData = $res[0];
    $expiresAt = $tokenData['expires_at'] ?? null;

    // Fix voor de strtotime(null) error:
    if (!$expiresAt || strtotime($expiresAt) < (time() + 60)) {
        return refreshGoogleToken($tokenData['refresh_token'] ?? null);
    }

    return $tokenData['access_token'];
}

function refreshGoogleToken($refreshToken) {
    if (!$refreshToken || $refreshToken === 'reset') return null;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => getenv('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
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