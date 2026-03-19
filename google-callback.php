<?php
// google-callback.php
require_once 'config.php';
if (!isset($_GET['code'])) die("Geen autorisatiecode ontvangen.");

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code' => $_GET['code'],
    'client_id' => $googleClientID,
    'client_secret' => $googleClientSecret,
    'redirect_uri' => $googleRedirectUri,
    'grant_type' => 'authorization_code'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$data = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($data['access_token'])) {
    $payload = [
        'id' => 1,
        'access_token' => $data['access_token'],
        'expires_at'   => date('Y-m-d H:i:s', time() + $data['expires_in'])
    ];
    if (isset($data['refresh_token'])) {
        $payload['refresh_token'] = $data['refresh_token'];
    }
    
    supabaseRequest('google_tokens', 'UPSERT', $payload);
    header('Location: admin.php');
} else {
    echo "Fout bij inwisselen code: " . print_r($data, true);
}