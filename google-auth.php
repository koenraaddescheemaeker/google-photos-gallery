<?php
/**
 * google-auth.php - DE "VINKJES-DWANG" EDITIE
 */
require_once 'config.php';

$params = [
    'client_id'              => $googleClientID,
    'redirect_uri'           => $googleRedirectUri,
    'response_type'          => 'code',
    'scope'                  => 'https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    // DIT IS DE KEY: 'consent' dwingt Google om de vinkjes ALTIJD te tonen
    // 'select_account' zorgt dat je opnieuw je account moet aanklikken
    'prompt'                 => 'consent', 
    'include_granted_scopes' => 'false' // We willen geen oude troep meenemen
];

$url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

header('Location: ' . $url);
exit;