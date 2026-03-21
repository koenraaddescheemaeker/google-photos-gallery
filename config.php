<?php
/**
 * FORCEKES - Centrale Configuratie & Google Auth
 * * Bevat de koppelingen met Coolify Environment Variables en Supabase.
 * Inclusief de specialistische fix voor het behoud van Google Photos Scopes.
 */

// 1. Google OAuth Client Instellingen
$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';

// 2. Supabase Configuratie
$supabaseUrl = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));

/**
 * Functie voor communicatie met de Supabase REST API
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
        if ($method === 'UPSERT') {
            $headers[] = "Prefer: resolution=merge-duplicates";
        }
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Haalt een geldige Google Access Token op.
 * Ververst de token automatisch via de Refresh Token indien nodig.
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;
    
    // 1. Haal de huidige token-set op uit Supabase
    $tokens = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (empty($tokens) || isset($tokens['error']) || empty($tokens[0])) {
        return false;
    }

    $row = $tokens[0];
    $expiresAt = strtotime($row['expires_at']);

    // 2. Controleer of de huidige access_token nog minstens 5 minuten geldig is
    if (!empty($row['access_token']) && $row['access_token'] !== 'leeg' && $expiresAt > (time() + 300)) {
        return $row['access_token'];
    }

    // 3. Token is verlopen of niet aanwezig: Verversen via Google API
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
        // CRUCIALE FIX: We dwingen Google om de scopes te behouden tijdens de refresh.
        // Zonder deze regel stript Google de 'sharing' scope bij ongeverifieerde apps.
        'scope'         => 'https://www.googleapis.com/auth/photoslibrary.readonly https://www.googleapis.com/auth/photoslibrary.sharing'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // 4. Sla de nieuwe access_token en nieuwe verloopdatum op in Supabase
    if (isset($res['access_token'])) {
        $newExpiresAt = date('c', time() + $res['expires_in']); // Gebruik ISO 8601 formaat
        
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at'   => $newExpiresAt
        ]);
        
        return $res['access_token'];
    }

    return false;
}