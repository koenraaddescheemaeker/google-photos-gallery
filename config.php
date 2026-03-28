<?php
/**
 * FORCEKES - config.php
 * Centraal configuratiebestand met robuuste sessie- en database-afhandeling.
 */

// 1. SESSIE MANAGEMENT: Altijd als eerste starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. DOMEIN & OMGEVINGSVARIABELEN
define('SITE_URL', 'https://forcekes.be');

// URL detectie (gebruikt Coolify env of fallback naar supa.forcekes.be)
$envUrl = getenv('SUPABASE_URL') ?: getenv('NEXT_PUBLIC_SUPABASE_URL') ?: 'https://supa.forcekes.be';
define('SUPABASE_URL', rtrim((string)$envUrl, '/'));

// Sleutel detectie (zoekt naar alle mogelijke aliassen uit Coolify)
$envKey = getenv('SUPABASE_KEY') ?: getenv('SERVICE_SUPABASEANON_KEY') ?: getenv('SUPABASE_ANON_KEY');
define('SUPABASE_KEY', (string)$envKey);

// Service Key (nodig voor admin acties indien van toepassing)
define('SUPABASE_SERVICE_KEY', (string)getenv('SUPABASE_SERVICE_ROLE_KEY'));

/**
 * Centrale functie voor Supabase REST API requests
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    if (!SUPABASE_KEY || SUPABASE_KEY === "") {
        return ['error' => true, 'message' => 'Geen API sleutel geconfigureerd.'];
    }

    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
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
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}