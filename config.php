<?php
/**
 * config.php
 * Geoptimaliseerd voor een gedeeld Coolify-project.
 */

// --- 1. Supabase Instellingen ---
// Omdat ze in hetzelfde project zitten, gebruiken we de interne servicenaam uit je compose file.
$supabaseUrl = "http://supabase-kong:8000"; 
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs";

// --- 2. Google OAuth Instellingen ---
$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$googleRedirectUri  = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';

/**
 * Supabase API Request Helper
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    
    $ch = curl_init($baseUrl);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ];
    
    // Specifieke afhandeling voor UPSERT (gebruikt in callback)
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = "Prefer: return=representation";
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Google Access Token Helper
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;

    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    $tokens = $res[0] ?? null;

    if (!$tokens || empty($tokens['refresh_token'])) return null;

    if (!empty($tokens['expires_at']) && strtotime($tokens['expires_at']) > (time() + 60)) {
        return $tokens['access_token'];
    }

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => $googleClientID,
        'client_secret' => $googleClientSecret,
        'refresh_token' => $tokens['refresh_token'],
        'grant_type'    => 'refresh_token'
    ]));
    
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        $newExpiry = date('Y-m-d H:i:s', time() + $response['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $response['access_token'],
            'expires_at'   => $newExpiry
        ]);
        return $response['access_token'];
    }
    return null;
}
?>