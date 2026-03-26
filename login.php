<?php
/** FORCEKES - login.php (Full Scope Power) */
require_once 'config.php';
$params = [
    'client_id'              => $googleConfig['client_id'],
    'redirect_uri'           => $googleConfig['redirect_uri'],
    'response_type'          => 'code',
    'scope'                  => 'openid email https://www.googleapis.com/auth/photoslibrary',
    'access_type'            => 'offline',
    'prompt'                 => 'consent',
    'include_granted_scopes' => 'true'
];
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header("Location: $authUrl"); exit;