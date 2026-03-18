<?php
require_once 'config.php';
if (!isset($_GET['code'])) die("Geen code ontvangen.");

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => $googleClientID,
    'client_secret' => $googleClientSecret,
    'redirect_uri'  => $googleRedirectUri,
    'grant_type'    => 'authorization_code',
    'code'          => $_GET['code']
]));
$response = json_decode(curl_exec($ch), true);

if (isset($response['access_token'])) {
    supabaseRequest('google_tokens', 'UPSERT', [
        'id'            => 1,
        'access_token'  => $response['access_token'],
        'refresh_token' => $response['refresh_token'] ?? null,
        'expires_at'    => date('Y-m-d H:i:s', time() + $response['expires_in'])
    ]);
    header('Location: admin.php'); // Naar de beheerpagina!
} else {
    echo "Fout: " . print_r($response, true);
}