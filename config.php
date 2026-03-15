<?php
/**
 * config.php
 * Centraal configuratiebestand voor het Familie Portaal.
 * Bevat verbindingen voor Supabase en de officiële Google Photos API.
 */

// --- 1. Supabase Instellingen ---
$supabaseUrl = "http://supabasekong-cs8cwo8c48g4www4w4scss84.167.86.73.61.sslip.io";
// Gebruik bij voorkeur ook hier een Env Var in Coolify voor de veiligheid:
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs";

// --- 2. Google OAuth Instellingen (Veilig via Environment Variables) ---
// Stel deze in Coolify in onder 'Environment Variables'
$googleClientID     = $_ENV['GOOGLE_CLIENT_ID'] ?? ''; 
$googleClientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
// $googleRedirectUri  = 'https://' . $_SERVER['HTTP_HOST'] . '/google-callback.php';
$googleRedirectUri = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';
/**
 * Supabase API Request Helper
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    
    $url = $supabaseUrl . "/rest/v1/" . $endpoint;
    $ch = curl_init($url);
    
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Google Access Token Helper
 * Controleert of de token nog geldig is, anders wordt de refresh_token gebruikt.
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;

    // Haal huidige tokens uit Supabase
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    $tokens = $res[0] ?? null;

    if (!$tokens) return null;

    // Als de token nog minstens 60 seconden geldig is, gebruik deze
    if (isset($tokens['expires_at']) && strtotime($tokens['expires_at']) > time() + 60) {
        return $tokens['access_token'];
    }

    // Token verlopen -> Vernieuwen met Refresh Token
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
        
        // Update Supabase
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $response['access_token'],
            'expires_at'   => $newExpiry
        ]);
        
        return $response['access_token'];
    }

    return null;
}
?>