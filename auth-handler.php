<?php
/** * FORCEKES - auth-handler.php (Email/Password Auth) */

error_reporting(E_ALL & ~E_DEPRECATED); 
ini_set('display_errors', 0);

require_once 'config.php';

$rawUrl = getenv('SUPABASE_URL') ?: (defined('SUPABASE_URL') ? SUPABASE_URL : '');
$supabaseUrl = rtrim((string)$rawUrl, '/');

$action = $_GET['action'] ?? '';

// Als je een inlogformulier hebt dat hierheen post
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $data = [
        'email' => $email,
        'password' => $password
    ];

    // We schieten de login direct naar je eigen Supabase
    $ch = curl_init($supabaseUrl . "/auth/v1/token?grant_type=password");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ]);

    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($response['access_token'])) {
        // Login succes! Sla het token op in een cookie voor 1 week
        setcookie("sb-access-token", $response['access_token'], time() + (86400 * 7), "/", "", true, true);
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}

// Uitloggen
if ($action === 'logout') {
    setcookie("sb-access-token", "", time() - 3600, "/");
    header("Location: index.php");
    exit;
}