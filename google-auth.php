<?php
/**
 * google-auth.php - De 'Dwang-modus' Editie
 */
require_once 'config.php';

$params = [
    'client_id'             => $googleClientID,
    'redirect_uri'          => $googleRedirectUri,
    'response_type'         => 'code',
    'scope'                 => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'           => 'offline',
    // --- DIT IS HET BREEKIJZER ---
    'prompt'                => 'consent', // Dwingt Google om de vinkjes ALTIJD te tonen
    'include_granted_scopes' => 'true'     // Voegt nieuwe rechten toe aan eventuele oude
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

header('Location: ' . $url);
exit;