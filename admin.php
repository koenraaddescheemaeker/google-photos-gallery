<?php
/**
 * FORCEKES ADMIN - Deep Debug Edition
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$supabaseUrl        = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey        = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));
$masterScope        = 'https://www.googleapis.com/auth/photoslibrary';

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
    if ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['error' => ['message' => "CURL Fout: $err"]];
    return json_decode($res, true);
}

function getDetailedToken() {
    global $googleClientID, $googleClientSecret, $masterScope;
    
    // 1. Haal data uit Supabase
    $tokens = supabaseRequest('google_tokens?select=*&id=eq.1');
    
    if (empty($tokens)) return "FOUT: Geen rij gevonden in Supabase met id=1. Voer de SQL INSERT/UPDATE uit.";
    if (isset($tokens['error'])) return "SUPABASE FOUT: " . $tokens['error']['message'];
    
    $row = $tokens[0];
    if (empty($row['refresh_token'])) return "FOUT: refresh_token kolom is leeg in de database.";

    // 2. Probeer te refreshen bij Google
    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $googleClientID,
        'client_secret' => $googleClientSecret,
        'refresh_token' => $row['refresh_token'],
        'grant_type' => 'refresh_token',
        'scope' => $masterScope
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resRaw = curl_exec($ch);
    $res = json_decode($resRaw, true);
    curl_close($ch);

    if (isset($res['access_token'])) {
        $newExpiry = date('Y-m-d H:i:sO', time() + $res['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at' => $newExpiry
        ]);
        return ['token' => $res['access_token']];
    }
    
    return "GOOGLE REFRESH FOUT: " . ($res['error_description'] ?? $res['error'] ?? 'Onbekende fout') . " (Raw: $resRaw)";
}

// Uitvoering
$result = getDetailedToken();

if (is_string($result)) {
    die("<body style='background:#000;color:#fff;padding:50px;font-family:sans-serif;'>
            <h1 style='color:#ef4444;'>Debug Informatie</h1>
            <div style='background:#111;padding:20px;border-radius:10px;border:1px solid #333;'>
                <p><strong>Melding:</strong> $result</p>
                <hr style='border:0;border-top:1px solid #333;margin:20px 0;'>
                <p><strong>Checklist:</strong></p>
                <ul style='color:#aaa;font-size:14px;'>
                    <li>Heeft rij in google_tokens ID 1?</li>
                    <li>Is GOOGLE_CLIENT_ID correct in Coolify?</li>
                    <li>Staat de refresh_token exact zo in de DB: 1//04PjMn... ?</li>
                </ul>
            </div>
         </body>");
}

$token = $result['token'];
// ... (Rest van de foto-logica zoals in de vorige stap)
?>