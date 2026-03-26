<?php
/** FORCEKES - google-callback.php */
require_once 'config.php';

if (!isset($_GET['code'])) die("Geen autorisatiecode ontvangen.");

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POSTFIELDS => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $googleConfig['client_id'],
        'client_secret' => $googleConfig['client_secret'],
        'redirect_uri'  => $googleConfig['redirect_uri'],
        'grant_type'    => 'authorization_code'
    ])
]);
$res = json_decode(curl_exec($ch), true); curl_close($ch);

if (isset($res['access_token'])) {
    $expiry = date('c', time() + $res['expires_in']);
    $data = [
        'access_token' => $res['access_token'],
        'expires_at'   => $expiry
    ];
    // Sla de refresh_token alleen op als die is meegeleverd
    if (isset($res['refresh_token'])) {
        $data['refresh_token'] = $res['refresh_token'];
    }

    supabaseRequest('google_tokens?id=eq.1', 'PATCH', $data);
    header("Location: admin.php?auth=success");
    exit;
} else {
    die("Token Exchange Error: " . json_encode($res));
}