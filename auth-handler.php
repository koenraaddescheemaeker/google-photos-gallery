<?php
/** FORCEKES - auth-handler.php (Debug Edition) */
require_once 'config.php';

$action = $_GET['action'] ?? '';

// Helper voor Supabase Auth API met betere Error Handling
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
            "Authorization: Bearer $supabaseKey",
            "Content-Type: application/json"
        ],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if(curl_errno($ch)) {
        return ['status' => 500, 'data' => ['msg' => 'CURL Fout: ' . curl_error($ch)]];
    }
    
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
        header("Location: index.php?view=login&msg=Succes! Je kunt nu inloggen.");
    } else {
        // Schrijf de fout letterlijk uit voor debug
        die("❌ Registratie Fout (Status ".$res['status']."): " . ($res['data']['msg'] ?? json_encode($res['data'])));
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
        die("❌ Login Fout: " . ($res['data']['error_description'] ?? json_encode($res['data'])));
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php?view=login");
}