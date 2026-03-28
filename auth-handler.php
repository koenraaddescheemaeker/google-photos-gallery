<?php
/**
 * FORCEKES - auth-handler.php
 * Handelt login en logout acties af via de Supabase Auth API.
 */
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty_fields");
        exit;
    }

    // Aanroep naar de Supabase Auth API voor Email/Password login
    $url = SUPABASE_URL . '/auth/v1/token?grant_type=password';
    $ch = curl_init($url);
    
    $payload = json_encode([
        'email'    => $email,
        'password' => $password
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . SUPABASE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (isset($data['access_token'])) {
        // 1. Browser cookie zetten (7 dagen geldig)
        setcookie('sb-access-token', $data['access_token'], time() + (3600 * 24 * 7), '/', '', true, true);
        
        // 2. PHP SESSIE VULLEN (Cruciaal voor admin-rechten in menu.php)
        $_SESSION['user_id'] = $data['user']['id'];
        $_SESSION['user_email'] = $data['user']['email'];
        
        // Terug naar het portaal
        header("Location: index.php");
        exit;
    } else {
        // Login mislukt
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}

// Uitloggen: Wist zowel de cookie als de sessie
if ($action === 'logout') {
    setcookie('sb-access-token', '', time() - 3600, '/');
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Als er geen actie is, terug naar start
header("Location: index.php");
exit;