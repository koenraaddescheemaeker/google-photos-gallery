<?php
require_once 'config.php';

// Controleer met strtolower() om hoofdletter-fouten te voorkomen
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';

if ($currentUser !== 'koen@lauwe.com') {
    // We sturen je door, maar we loggen even in de URL waarom
    header("Location: index.php?auth_error=not_authorized&debug_user=" . urlencode($currentUser));
    exit;
}
/** * FORCEKES - config.php (Nuclear Session Fix) */

// 1. FORCEER SESSIE INSTELLINGEN (Vóór session_start)
ini_set('session.cookie_domain', '.forcekes.be'); // Werkt op forcekes.be én www.forcekes.be
ini_set('session.cookie_path', '/');
ini_set('session.gc_maxlifetime', 604800); // 1 week
session_set_cookie_params(604800, '/', '.forcekes.be', true, true);

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
 * SESSIE HERSTEL: Controleer de cookie als de sessie leeg is
 */
if (!isset($_SESSION['user_email']) && isset($_COOKIE['sb-access-token'])) {
    $token = $_COOKIE['sb-access-token'];
    $ch = curl_init(SUPABASE_URL . '/auth/v1/user');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: '.SUPABASE_KEY, 'Authorization: Bearer '.$token]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userData = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($userData['email'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = strtolower($userData['email']); // Altijd kleine letters
    }
}

function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $ch = curl_init($url);
    $headers = ['apikey: '.SUPABASE_KEY, 'Authorization: Bearer '.SUPABASE_KEY, 'Content-Type: application/json', 'Prefer: return=representation'];
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}