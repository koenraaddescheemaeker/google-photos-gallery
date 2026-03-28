<?php
/** * FORCEKES - config.php (Production Edition) */

// De officiële nieuwe locatie
define('SITE_URL', 'https://forcekes.be');
define('SUPABASE_URL', 'https://supa.forcekes.be'); // Je Supabase subdomein
define('SUPABASE_KEY', 'JOUW_ANON_KEY');
define('SUPABASE_SERVICE_KEY', 'JOUW_SERVICE_ROLE_KEY'); // Voor de scraper

/**
 * Centrale functie voor Supabase API requests
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}