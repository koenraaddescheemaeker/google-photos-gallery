<?php
require_once 'config.php';

// We dwingen een volledig schone lei af met deze parameters
$params = [
    'client_id'              => $googleClientID,
    'redirect_uri'           => $googleRedirectUri,
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account', // Dwingt vinkjes EN accountkeuze
    'include_granted_scopes' => 'false'                   // Negeer oude, foute permissies
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

header('Location: ' . $authUrl);
exit;