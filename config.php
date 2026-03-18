<?php
/**
 * config.php - Owl-Bever-Elephant-Eagle Editie
 * Strategisch, robuust en veilig voor forcekes.be
 */

// --- 1. Supabase Configuratie ---
$supabaseUrl = "https://supa.forcekes.be"; 
// We houden de key hier omdat deze specifiek voor jouw project is, 
// maar wees alert op GitHub-waarschuwingen (of zet hem ook in een ENV var).
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA";

// --- 2. Google OAuth Configuratie (Via Environment Variables) ---
$googleClientID     = getenv('GOOGLE_CLIENT_ID'); 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET');
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';

/**
 * De Sluis: supabaseRequest
 * Handelt alle communicatie met de database af.
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
    
    // Method-specifieke instellingen (Bever-stijl: bouwen op maat)
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_POST, true);
        // PostgREST vereist een array [] voor een UPSERT
        $payload = (isset($data[0])) ? $data : [$data];
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } elseif ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        // Standaard GET: GEEN postfields meesturen (voorkomt 400 errors)
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // De Olifant onthoudt: we omzeilen de TLS-fout van de container
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($curlError) return ['error' => $curlError];
    
    $decoded = json_decode($response, true);
    // Sommige Supabase calls geven bij succes een lege array of 'true' terug
    return $decoded ?: ($httpCode < 300 ? ['status' => 'success'] : ['error' => 'HTTP ' . $httpCode]);
}

/**
 * Het Geheugen: getValidAccessToken
 * Controleert en ververst de Google-toegang.
 */
function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;

    // Haal specifiek record 1 op
    $res = supabaseRequest('google_tokens?id=eq.1&select=*');
    
    if (!is_array($res) || empty($res)) return null;
    $tokens = $res[0];

    // Is de huidige token nog minstens 60 seconden geldig?
    if (!empty($tokens['access_token']) && !empty($tokens['expires_at'])) {
        if (strtotime($tokens['expires_at']) > (time() + 60)) {
            return $tokens['access_token'];
        }
    }

    // De token is verlopen, gebruik de refresh_token (de eeuwige sleutel)
    if (!empty($tokens['refresh_token'])) {
        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
            
            // Sla de nieuwe access_token op (zonder de refresh_token te wissen!)
            supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
                'access_token' => $response['access_token'],
                'expires_at'   => $newExpiry
            ]);
            
            return $response['access_token'];
        }
    }

    return null;
}