<?php
/**
 * google-callback.php
 */
require_once 'config.php';

if (!isset($_GET['code'])) {
    die("Geen code ontvangen van Google.");
}

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => getenv('GOOGLE_CLIENT_ID'),
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
    'code'          => $_GET['code'],
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($res['access_token'])) {
    $expiresAt = date('Y-m-d H:i:sO', time() + ($res['expires_in'] ?? 3600));
    
    // We updaten ID 1 met de VOLLEDIGE nieuwe set rechten
    $data = [
        'access_token'  => $res['access_token'],
        'expires_at'    => $expiresAt
    ];
    
    // Alleen updaten als we een nieuwe refresh_token krijgen
    if (!empty($res['refresh_token'])) {
        $data['refresh_token'] = $res['refresh_token'];
    }

    supabaseRequest('google_tokens?id=eq.1', 'PATCH', $data);

    header("Location: admin.php?auth=success");
    exit;
} else {
    echo "<h1>Token Error</h1><pre>";
    print_r($res);
    echo "</pre>";
}