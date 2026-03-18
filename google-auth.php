<?php
require_once 'config.php';
$params = [
    'client_id'     => $googleClientID,
    'redirect_uri'  => $googleRedirectUri,
    'response_type' => 'code',
    'scope'         => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'   => 'offline',
    'prompt'        => 'consent'
];
header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));