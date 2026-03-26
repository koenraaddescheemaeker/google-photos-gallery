<?php
/** FORCEKES - login.php */
require_once 'config.php';

$params = [
    'client_id'              => $googleConfig['client_id'],
    'redirect_uri'           => $googleConfig['redirect_uri'],
    'response_type'          => 'code',
    'scope'                  => 'openid email https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent', // Dwingt toestemming en nieuwe refresh_token af
    'include_granted_scopes' => 'true',
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header("Location: $authUrl");
exit;