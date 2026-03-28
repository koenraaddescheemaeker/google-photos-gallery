<?php
/** * FORCEKES - config.php (Super-Glue Edition) */

// 1. Forceer sessie start direct bij de eerste byte
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_URL', 'https://forcekes.be');
$envUrl = getenv('SUPABASE_URL') ?: 'https://supa.forcekes.be';
define('SUPABASE_URL', rtrim((string)$envUrl, '/'));
$envKey = getenv('SUPABASE_KEY') ?: getenv('SERVICE_SUPABASEANON_KEY');
define('SUPABASE_KEY', (string)$envKey);
define('SUPABASE_SERVICE_KEY', (string)getenv('SUPABASE_SERVICE_ROLE_KEY'));

/**
 * SESSIE HERSTEL: Als de sessie leeg is maar de cookie bestaat,
 * halen we de gebruikersgegevens opnieuw op bij Supabase.
 */
if (!isset($_SESSION['user_email']) && isset($_COOKIE['sb-access-token'])) {
    $token = $_COOKIE['sb-access-token'];
    $ch = curl_init(SUPABASE_URL . '/auth/v1/user');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userData = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($userData['email'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
    }
}

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $ch = curl_init($url);
    $headers = ['apikey: '.SUPABASE_KEY, 'Authorization: Bearer '.SUPABASE_KEY, 'Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}