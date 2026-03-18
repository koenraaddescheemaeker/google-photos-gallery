<?php
/**
 * google-auth.php - De 'Account-Dwang' Editie
 */
require_once 'config.php';

$params = [
    'client_id'              => $googleClientID,
    'redirect_uri'           => $googleRedirectUri,
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    // We voegen 'select_account' toe aan de prompt
    'prompt'                 => 'select_account consent', 
    'include_granted_scopes' => 'true'
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
header('Location: ' . $url);
exit;