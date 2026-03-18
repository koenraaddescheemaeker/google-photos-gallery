<?php
/**
 * google-auth.php - De 'Clean State' Editie
 */
require_once 'config.php';

$params = [
    'client_id'              => $googleClientID,
    'redirect_uri'           => $googleRedirectUri,
    'response_type'          => 'code',
    'scope'                  => $googleScope, // Gebruikt de variabele uit config.php
    'access_type'            => 'offline',
    'prompt'                 => 'select_account consent', 
    'include_granted_scopes' => 'false' // Dwingt Google om alleen naar DEZE aanvraag te kijken
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;