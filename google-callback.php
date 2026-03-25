<?php
/**
 * FORCEKES - google-callback.php
 */
require_once 'config.php';

if (!isset($_GET['code'])) {
    die("Geen code ontvangen.");
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
    $expiresAt = date('Y-m-d H:i:sO', time() + $res['expires_in']);
    
    $updateData = [
        'access_token' => $res['access_token'],
        'expires_at'   => $expiresAt
    ];
    
    if (isset($res['refresh_token'])) {
        $updateData['refresh_token'] = $res['refresh_token'];
    }

    // Update de token in Supabase (id=1)
    supabaseRequest('google_tokens?id=eq.1', 'PATCH', $updateData);

    header("Location: admin.php?auth=success");
    exit;
} else {
    die("Fout bij ophalen token: " . print_r($res, true));
}