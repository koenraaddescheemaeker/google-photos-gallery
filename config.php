<?php
/**
 * config.php
 * Centraal configuratiebestand voor het Familie Portaal.
 * Gebruikt getenv() voor maximale compatibiliteit met Coolify/Docker.
 */

// --- 1. Supabase Instellingen ---
// Je kunt deze hier hardcoderen of ook in Coolify zetten als SUPABASE_URL en SUPABASE_KEY
$supabaseUrl = "http://172.17.0.1:8000";
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs";

// --- 2. Google OAuth Instellingen (Veilig via getenv) ---
$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
// De Redirect URI moet EXACT overeenkomen met je Google Cloud Console instelling
$googleRedirectUri  = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';

/**
 * Supabase API Request Helper
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    
    // Zorg dat we niet dubbel /rest/v1 toevoegen
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    
    $ch = curl_init($baseUrl);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    
    // Voor Upsert (gebruikt in callback)
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    ////
    // In de supabaseRequest functie:
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Max 3 sec om verbinding te maken
curl_setopt($ch, CURLOPT_TIMEOUT, 5);        // Max 5 sec voor de hele data
    ////
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
 * Vernieuwt de token automatisch als deze verlopen is.
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;

    // 1. Haal huidige tokens uit Supabase
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    $tokens = $res[0] ?? null;

    if (!$tokens || empty($tokens['refresh_token'])) {
        return null;
    }

    // 2. Check of de token nog minstens 60 seconden geldig is
    if (!empty($tokens['expires_at']) && strtotime($tokens['expires_at']) > (time() + 60)) {
        return $tokens['access_token'];
    }

    // 3. Token verlopen -> Vernieuwen via Google API
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
        
        // Update de nieuwe access token in Supabase
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $response['access_token'],
            'expires_at'   => $newExpiry
        ]);
        
        return $response['access_token'];
    }

    return null;
}
?>