<?php
/** * FORCEKES - config.php (De Fundering) */
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. DUBBELCHECK DEZE URL EN KEY (Kopieer ze rechtstreeks uit Supabase -> Settings -> API)
define('SUPABASE_URL', 'https://supa.forcekes.be'); 
define('SUPABASE_SERVICE_KEY', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs'); // GEBRUIK DE 'service_role' key!

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
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    
    // Als de API een foutcode geeft (niet 200/201), sturen we de fout door
    if ($httpCode >= 400) {
        return ['error' => true, 'message' => $decoded['message'] ?? 'Onbekende API fout', 'code' => $httpCode];
    }
    return $decoded;
}