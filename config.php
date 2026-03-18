<?php
/**
 * config.php
 * Geoptimaliseerd voor forcekes.be & supa.forcekes.be
 */

// --- 1. Supabase Instellingen ---
// We gebruiken de nieuwe subdomein-URL. Geen poort :8000 nodig!
$supabaseUrl = "https://supa.forcekes.be"; 

// Gebruik de SERVICE_ROLE_KEY voor volledige rechten (God-mode)
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA";

// --- 2. Google OAuth Instellingen ---
$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
// Let op: Verander dit ook in je Google Cloud Console!
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';

/**
 * Supabase API Request Helper
 * Handelt GET, POST, PATCH en UPSERT af naar de REST API.
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
    
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_POST, true);
        // PostgREST verwacht een array [] voor UPSERT acties
        $payload = (isset($data[0])) ? $data : [$data];
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = "Prefer: return=representation";
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Ruime timeout voor stabiliteit
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) return ['error' => $curlError];
    
    return json_decode($response, true) ?: ['status' => $httpCode];
}

/**
 * Google Access Token Helper
 * Controleert of de token nog geldig is, zo niet: vernieuwen via Refresh Token.
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;

    // Haal huidige tokens op uit de database (Record ID 1)
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (!$res || isset($res['error']) || !isset($res[0])) return null;
    
    $tokens = $res[0];
    if (empty($tokens['refresh_token'])) return null;

    // Check of token nog minstens 60 seconden geldig is
    if (!empty($tokens['expires_at']) && strtotime($tokens['expires_at']) > (time() + 60)) {
        return $tokens['access_token'];
    }

    // Token is verlopen, vraag nieuwe aan bij Google
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
        // Update de nieuwe access_token in Supabase
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $response['access_token'],
            'expires_at'   => $newExpiry
        ]);
        return $response['access_token'];
    }
    return null;
}