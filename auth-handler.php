<?php
/** FORCEKES - auth-handler.php */
require_once 'config.php';

function supabaseAuth($endpoint, $data) {
    global $supabaseUrl, $supabaseKey;
    $url = rtrim($supabaseUrl, '/') . "/auth/v1/" . $endpoint;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => ["apikey: $supabaseKey", "Content-Type: application/json"],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'data' => json_decode($response, true)];
}

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $res = supabaseAuth('token?grant_type=password', [
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ]);

    if ($res['status'] === 200) {
        $_SESSION['user_id'] = $res['data']['user']['id'];
        $_SESSION['user_email'] = $res['data']['user']['email'];
        $_SESSION['user_name'] = $res['data']['user']['user_metadata']['display_name'] ?? 'Admin';
        header("Location: admin.php");
    } else {
        header("Location: index.php?view=login&msg=Onjuist.");
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}