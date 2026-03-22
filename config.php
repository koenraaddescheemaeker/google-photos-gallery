<?php
/**
 * FORCEKES - Master Config & Auth Logic
 * Stack: PHP, Coolify, Supabase, Google Photos API
 */

// 1. Google OAuth Credentials (uit Coolify Environment)
$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$googleRedirectUri  = 'https://new.forcekes.be/google-callback.php';

// 2. Supabase Settings (uit Coolify Environment)
$supabaseUrl = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));

// 3. De Master Scope (Alles-in-één voor stabiliteit)
$masterScope = 'https://www.googleapis.com/auth/photoslibrary';

/**
 * Supabase Request Helper
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $url = "$supabaseUrl/rest/v1/$endpoint";
    
    $ch = curl_init($url);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

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

/**
 * Haalt de Access Token op en ververst deze indien nodig via de Master Scope
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret, $masterScope;
    
    // A. Haal token-rij op uit Supabase
    $tokens = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (empty($tokens) || isset($tokens['error']) || empty($tokens[0])) {
        return false;
    }

    $row = $tokens[0];
    $expiresAt = strtotime($row['expires_at']);

    // B. Check of huidige token nog minstens 5 minuten (300 sec) werkt
    if (!empty($row['access_token']) && $row['access_token'] !== 'leeg' && $expiresAt > (time() + 300)) {
        return $row['access_token'];
    }

    // C. Token is (bijna) verlopen: Refresh uitvoeren
    if (empty($row['refresh_token'])) {
        return false;
    }

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $googleClientID,
        'client_secret' => $googleClientSecret,
        'refresh_token' => $row['refresh_token'],
        'grant_type'    => 'refresh_token',
        'scope'         => $masterScope // Dwing Google om alle rechten te behouden
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // D. Sla de nieuwe token op
    if (isset($res['access_token'])) {
        $isoExpiry = date('c', time() + $res['expires_in']); // ISO 8601 voor Postgres
        
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at'   => $isoExpiry
        ]);
        
        return $res['access_token'];
    }

    return false;
}