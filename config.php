<?php
// config.php
$supabaseUrl = "https://supa.forcekes.be";
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA";

$googleClientID     = getenv('GOOGLE_CLIENT_ID');
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET');
$googleRedirectUri  = 'https://forcekes.be/google-callback.php';
$googleScope        = 'https://www.googleapis.com/auth/photoslibrary.readonly';

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $url = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    $ch = curl_init($url);
    
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ];

    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(isset($data[0]) ? $data : [$data]));
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($res, true) ?: ($code < 300 ? ['status' => 'ok'] : ['error' => $code]);
}

function getValidAccessToken() {
    global $googleClientID, $googleClientSecret;
    $res = supabaseRequest('google_tokens?id=eq.1&select=*');
    
    if (!$res || !isset($res[0])) return null;
    $t = $res[0];

    // Check of token nog geldig is (minstens 60 sec)
    if (!empty($t['access_token']) && !empty($t['expires_at']) && strtotime($t['expires_at']) > (time() + 60)) {
        return $t['access_token'];
    }

    // Refresh indien nodig
    if (!empty($t['refresh_token'])) {
        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $googleClientID,
            'client_secret' => $googleClientSecret,
            'refresh_token' => $t['refresh_token'],
            'grant_type' => 'refresh_token'
        ]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($resp['access_token'])) {
            $expiry = date('Y-m-d H:i:s', time() + $resp['expires_in']);
            supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
                'access_token' => $resp['access_token'],
                'expires_at' => $expiry
            ]);
            return $resp['access_token'];
        }
    }
    return null;
}