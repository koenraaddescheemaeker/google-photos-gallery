<?php
/** * FORCEKES - config.php 
 * Haalt variabelen op uit de Docker/Coolify omgeving
 */
session_start();

// Mappen van Coolify variabelen naar PHP variabelen
$supabaseUrl = getenv('SUPABASE_URL') ?: getenv('SERVICE_URL_SUPABASEKONG');
$supabaseKey = getenv('SUPABASE_ANON_KEY') ?: getenv('SERVICE_SUPABASEANON_KEY');

/**
 * Algemene functie voor Supabase Database verzoeken (REST API)
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    
    if (!$supabaseUrl || !$supabaseKey) return ["error" => "Config ontbreekt"];

    $url = rtrim($supabaseUrl, '/') . "/rest/v1/" . $endpoint;
    
    $ch = curl_init($url);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($response, true);
}
?>