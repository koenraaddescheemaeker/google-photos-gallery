<?php
/**
 * config.php - DE FINITIEVE VERSIE
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/') . "/rest/v1/" . $endpoint;
    $key = getenv('SUPABASE_SERVICE_ROLE_KEY');
    
    $ch = curl_init($url);
    $headers = [
        "apikey: $key",
        "Authorization: Bearer $key",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    
    if ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function getValidAccessToken() {
    $res = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (!$res || !isset($res[0])) return false;
    
    $row = $res[0];
    $now = time();
    $expires = strtotime($row['expires_at']);

    // Als de token nog minstens 5 minuten geldig is, gebruik hem
    if (!empty($row['access_token']) && $row['access_token'] !== 'reset' && ($expires - $now) > 300) {
        return $row['access_token'];
    }

    // Anders: Refreshen
    if (empty($row['refresh_token']) || $row['refresh_token'] === 'reset') return false;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id'     => getenv('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
        'refresh_token' => $row['refresh_token'],
        'grant_type'    => 'refresh_token'
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($data['access_token'])) {
        $newExpiry = date('Y-m-d H:i:sO', time() + $data['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $data['access_token'],
            'expires_at'   => $newExpiry
        ]);
        return $data['access_token'];
    }

    return false;
}