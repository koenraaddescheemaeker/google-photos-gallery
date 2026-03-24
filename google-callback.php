<?php
/**
 * google-callback.php - DE DEFINITIEVE VERSIE
 */
require_once 'config.php';

if (!isset($_GET['code'])) {
    die("Geen autorisatiecode ontvangen van Google.");
}

// 1. Wissel de code in voor een token
$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => getenv('GOOGLE_CLIENT_ID'),
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
    'code'          => $_GET['code'],
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

if (isset($res['access_token'])) {
    $expiresAt = date('Y-m-d H:i:sO', time() + $res['expires_in']);
    
    // 2. Update Supabase geforceerd
    $data = [
        'access_token'  => $res['access_token'],
        'expires_at'    => $expiresAt,
        'refresh_token' => $res['refresh_token'] ?? null
    ];

    // Verwijder lege waarden zodat we bestaande refresh_tokens niet wissen als Google ze niet stuurt
    $data = array_filter($data);

    $result = supabaseRequest('google_tokens?id=eq.1', 'PATCH', $data);

    // Controleer of de update gelukt is
    header("Location: admin.php?auth_status=success_and_saved");
    exit;
} else {
    echo "<h1>Fout bij Google:</h1><pre>";
    print_r($res);
    echo "</pre>";
}