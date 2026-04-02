<?php
session_start();
define('SUPABASE_URL', 'https://supa.forcekes.be');
define('SUPABASE_SERVICE_KEY', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoic2VydmljZV9yb2xlIn0.U_MZEZsEI0c2VNqDu578m-ItLlmHLQIPN1ndKHWT3pA');
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
$url = SUPABASE_URL . "/rest/v1/" . $endpoint;
$ch = curl_init($url);
$headers = ['Authorization: Bearer ' . SUPABASE_SERVICE_KEY, 'apikey: ' . SUPABASE_SERVICE_KEY, 'Content-Type: application/json', 'Prefer: return=representation'];
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
if ($data) { curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); }
$response = curl_exec($ch);
curl_close($ch);
return json_decode($response, true);
}
?>