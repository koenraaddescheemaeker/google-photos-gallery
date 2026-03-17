<?php
/**
 * config.php - PUBLIEKE ROUTE VERSIE
 */

// 1. Supabase Instellingen
// We gebruiken de PUBLIEKE URL die Coolify aan je Kong-gateway heeft gegeven.
// Dit omzeilt de interne Docker-bridge problemen.
$supabaseUrl = "http://supabasekong-cs8cwo8c48g4www4w4scss84.167.86.73.61.sslip.io:8000"; 

// Gebruik de SERVICE_ROLE KEY (uit je ENV lijst)
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA";

// 2. Google OAuth Instellingen
$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$googleRedirectUri  = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';

/**
 * Supabase API Request Helper
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    
    $ch = curl_init($baseUrl);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json"
    ];
    
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_POST, true); // Expliciet POST aanzetten voor data
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = "Prefer: return=representation";
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) return ['error' => $err];
    return json_decode($response, true);
}

// ... rest van de functies (getValidAccessToken) blijven gelijk ...