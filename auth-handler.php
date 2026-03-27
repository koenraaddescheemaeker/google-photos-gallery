<?php
/** FORCEKES - auth-handler.php (Master Connectivity Edition) */
require_once 'config.php';
$action = $_GET['action'] ?? '';

function supabaseAuth($endpoint, $data) {
    global $supabaseUrl, $supabaseKey;
    $url = rtrim($supabaseUrl, '/') . "/auth/v1/" . $endpoint;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            "apikey: $supabaseKey",
            "Content-Type: application/json"
        ],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['status' => $status, 'data' => json_decode($response, true)];
}

if ($action === 'register') {
    $res = supabaseAuth('signup', [
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'data' => ['display_name' => $_POST['name']]
    ]);

    if ($res['status'] === 200 || $res['status'] === 201) {
        header("Location: index.php?view=login&msg=Account aangemaakt!");
    } else {
        die("❌ Registratie Fout: " . ($res['data']['msg'] ?? 'Onbekende fout'));
    }
}

if ($action === 'login') {
    $res = supabaseAuth('token?grant_type=password', [
        'email' => $_POST['email'],
        'password' => $_POST['password']
    ]);

    if ($res['status'] === 200) {
        $_SESSION['user_id'] = $res['data']['user']['id'];
        $_SESSION['user_name'] = $res['data']['user']['user_metadata']['display_name'] ?? 'Familielid';
        header("Location: index.php?view=dashboard");
    } else {
        die("❌ Login Fout: " . ($res['data']['error_description'] ?? 'E-mail of wachtwoord onjuist'));
    }
}

if ($action === 'recover') {
    $res = supabaseAuth('recover', ['email' => $_POST['email']]);
    if ($res['status'] === 200) {
        header("Location: index.php?view=login&msg=Herstel-link verzonden.");
    } else {
        die("❌ Herstel Fout: " . ($res['data']['msg'] ?? 'Kon link niet verzenden'));
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php?view=login");
}