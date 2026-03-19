<?php
// google-auth.php
require_once 'config.php';
$params = [
    'client_id'     => $googleClientID,
    'redirect_uri'  => $googleRedirectUri,
    'response_type' => 'code',
    'scope'         => $googleScope,
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account',
    'include_granted_scopes' => 'false' // Forceert een schone aanvraag
];
header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;