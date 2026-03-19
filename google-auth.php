<?php
require_once 'config.php';

$params = [
    'client_id'              => trim($googleClientID),
    'redirect_uri'           => trim($googleRedirectUri),
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account',
    'include_granted_scopes' => 'false',
    'login_hint'             => 'koenraad.descheemaeker@gmail.com' 
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;