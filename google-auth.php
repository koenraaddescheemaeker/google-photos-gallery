<?php
require_once 'config.php';

$params = [
    'client_id'     => trim($googleClientID),
    'redirect_uri'  => trim($googleRedirectUri),
    'response_type' => 'code',
    'scope'         => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account' // DIT MOET DE VINKJES FORCEREN IN TESTING MODE
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;