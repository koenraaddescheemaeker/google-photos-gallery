<?php
// login.php
require_once 'config.php';

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scopes,
    'access_type' => 'offline',
    'prompt' => 'consent', // Cruciaal om een nieuw refresh_token te forceren
    'include_granted_scopes' => 'true'
]);

header('Location: ' . $auth_url);
exit;