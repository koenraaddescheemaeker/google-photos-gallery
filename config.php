<?php
// config.php
$supabaseUrl = "http://172.17.0.1:8000"; // Test eerst dit, werkt dit niet? Gebruik de Internal DNS naam.
$supabaseKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs";

$googleClientID     = getenv('GOOGLE_CLIENT_ID') ?: ''; 
$googleClientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$googleRedirectUri  = 'https://aco8s8skwgog88wg40ckkws4.167.86.73.61.sslip.io/google-callback.php';

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $baseUrl = rtrim($supabaseUrl, '/') . "/rest/v1/" . ltrim($endpoint, '/');
    
    $ch = curl_init($baseUrl);
    $headers = ["apikey: $supabaseKey", "Authorization: Bearer $supabaseKey", "Content-Type: application/json"];
    
    if ($method === 'UPSERT') {
        $headers[] = "Prefer: resolution=merge-duplicates";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers[] = "Prefer: return=representation";
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) return ['error' => $err];
    return json_decode($response, true);
}
// ... rest van de getValidAccessToken functie ...