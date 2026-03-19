<?php
require_once 'config.php';

// We genereren een willekeurige string om de sessie uniek te maken voor Google
$state = bin2hex(random_bytes(16));

$params = [
    'client_id'              => trim($googleClientID),
    'redirect_uri'           => trim($googleRedirectUri),
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account', // Dwingt het keuzescherm af
    'include_granted_scopes' => 'false',
    'state'                  => $state
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;