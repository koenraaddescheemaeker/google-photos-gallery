<?php
/** * FORCEKES - auth-handler.php (Strict Save) */
require_once 'config.php';
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $url = SUPABASE_URL . '/auth/v1/token?grant_type=password';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: '.SUPABASE_KEY, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'password' => $password]));
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res['access_token'])) {
        // Cookie zetten
        setcookie('sb-access-token', $res['access_token'], time() + (3600 * 24 * 7), '/', '', true, true);
        
        // Sessie vullen
        $_SESSION['user_id'] = $res['user']['id'];
        $_SESSION['user_email'] = $res['user']['email'];
        
        // CRUCIAAL: Dwing PHP om de sessie NU op te slaan
        session_write_close();
        
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}

if ($action === 'logout') {
    setcookie('sb-access-token', '', time() - 3600, '/');
    session_destroy();
    header("Location: login.php");
    exit;
}