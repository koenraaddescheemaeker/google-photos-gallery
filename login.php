<?php
/**
 * FORCEKES - login.php (Isolatietest)
 */
$clientID = '937650128725-h6c3dbh2hs7q93qjbq95mp34kccqkthp.apps.googleusercontent.com';
$redirectUri = 'https://new.forcekes.be/google-callback.php';

$params = [
    'client_id'     => $clientID,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'openid email', // WE LATEN DE FOTO'S HIER EVEN WEG
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account',
    'state'         => 'test123'
];
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
?>
<a href="<?= $authUrl ?>" style="padding:20px; background:blue; color:white; display:inline-block;">TEST MINIMALE LOGIN</a>