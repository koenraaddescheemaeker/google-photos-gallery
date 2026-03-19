<?php
require_once 'config.php';

$params = [
    'client_id'              => trim($googleClientID),
    'redirect_uri'           => trim($googleRedirectUri),
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent', // Dwingt het scherm af
    'include_granted_scopes' => 'false',
    'state'                  => bin2hex(random_bytes(16)) // Extra veiligheid/validatie
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;