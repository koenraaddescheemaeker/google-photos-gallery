<?php
/** * FORCEKES - config.php (De Fundering) */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Supabase Configuratie
define('SUPABASE_URL', 'https://supa.forcekes.be'); 
define('SUPABASE_SERVICE_KEY', 'JOUW_SERVICE_ROLE_KEY_HIER'); // Gebruik je eigen key!

/**
 * De Universele Supabase Request Functie
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $ch = curl_init($url);
    
    $headers = [
        'apikey: ' . SUPABASE_SERVICE_KEY,
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}