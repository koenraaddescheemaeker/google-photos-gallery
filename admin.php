<?php
/**
 * FORCEKES - Final Specialist Config
 */

$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';

$supabaseUrl = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));

// MATCH EXACT MET JE PLAYGROUND VINKJES:
$requiredScopes = 'https://www.googleapis.com/auth/photoslibrary.readonly https://www.googleapis.com/auth/photoslibrary.sharing';

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $url = "$supabaseUrl/rest/v1/$endpoint";
    $ch = curl_init($url);
    $headers = ["apikey: $supabaseKey", "Authorization: Bearer $supabaseKey", "Content-Type: application/json", "Prefer: return=representation"];
    if ($method === 'POST' || $method === 'UPSERT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        if ($method === 'UPSERT') $headers[] = "Prefer: resolution=merge-duplicates";
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function getValidAccessToken() {
    global $googleClientID, $googleClientSecret, $requiredScopes;
    
    $tokens = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (empty($tokens) || isset($tokens['error']) || empty($tokens[0])) return false;

    $row = $tokens[0];
    $expiresAt = strtotime($row['expires_at']);

    // Is de token nog geldig?
    if (!empty($row['access_token']) && $row['access_token'] !== 'leeg' && $expiresAt > (time() + 300)) {
        return $row['access_token'];
    }

    // Refresh nodig
    if (empty($row['refresh_token'])) return false;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $googleClientID,
        'client_secret' => $googleClientSecret,
        'refresh_token' => $row['refresh_token'],
        'grant_type'    => 'refresh_token',
        'scope'         => $requiredScopes // EXACT MATCH
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res['access_token'])) {
        // Gebruik exact formaat voor Postgres timestamptz
        $newExpiry = date('Y-m-d H:i:sO', time() + $res['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at'   => $newExpiry
        ]);
        return $res['access_token'];
    } else {
        // DEBUG: Als de refresh faalt, toon waarom
        echo "<pre>REFRESH FAILED: " . print_r($res, true) . "</pre>";
        return false;
    }
}