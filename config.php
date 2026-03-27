<?php
/** * FORCEKES - config.php 
 * Haalt variabelen op uit de Docker/Coolify omgeving
 */
session_start();

// We proberen alle variaties die Coolify gebruikt
$supabaseUrl = getenv('NEXT_PUBLIC_SUPABASE_URL') 
            ?: getenv('SERVICE_URL_SUPABASEKONG') 
            ?: getenv('SUPABASE_URL');

$supabaseKey = getenv('SERVICE_SUPABASEANON_KEY') 
            ?: getenv('SUPABASE_ANON_KEY') 
            ?: getenv('NEXT_PUBLIC_SUPABASE_ANON_KEY');

/**
 * Algemene functie voor Supabase Database verzoeken
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
    curl_close($ch);
    
    return json_decode($response, true);
}
?>