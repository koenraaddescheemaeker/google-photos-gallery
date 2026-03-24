<?php
/**
 * FORCEKES - Google Callback Handler
 */
require_once 'config.php';

if (!isset($_GET['code'])) {
    die("Geen autorisatiecode ontvangen.");
}

$code = $_GET['code'];

// 1. Wissel code in voor tokens
$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => getenv('GOOGLE_CLIENT_ID'),
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
    'code'          => $code,
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($res['refresh_token'])) {
    // 2. Opslaan in Supabase (ID 1)
    $expiresAt = date('Y-m-d H:i:sO', time() + $res['expires_in']);
    
    $update = supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'access_token'  => $res['access_token'],
        'refresh_token' => $res['refresh_token'],
        'expires_at'    => $expiresAt
    ]);

    header("Location: admin.php?status=success");
    exit;
} else {
    echo "<h1>Fout tijdens koppelen</h1><pre>";
    print_r($res);
    echo "</pre>";
}