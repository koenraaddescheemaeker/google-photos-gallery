<?php
/** FORCEKES - auth-handler.php (Supabase Auth Integration) */
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $action !== 'logout') {
    header("Location: index.php");
    exit;
}

// Helper voor Supabase Auth API calls
function supabaseAuth($endpoint, $data) {
    global $supabaseUrl, $supabaseKey;
    $url = $supabaseUrl . "/auth/v1/" . $endpoint;
    
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

// --- ACTIONS ---

if ($action === 'register') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    $res = supabaseAuth('signup', [
        'email' => $email,
        'password' => $password,
        'data' => ['display_name' => $name]
    ]);

    if ($res['status'] === 200 || $res['status'] === 201) {
        // Succes: Gebruiker moet meestal e-mail bevestigen (afhankelijk van Supabase settings)
        header("Location: index.php?view=login&msg=check_email");
    } else {
        $error = $res['data']['msg'] ?? 'Registratie mislukt';
        header("Location: index.php?view=register&error=" . urlencode($error));
    }
}

if ($action === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = supabaseAuth('token?grant_type=password', [
        'email' => $email,
        'password' => $password
    ]);

    if ($res['status'] === 200) {
        // Sessie vullen
        $_SESSION['user_id'] = $res['data']['user']['id'];
        $_SESSION['user_name'] = $res['data']['user']['user_metadata']['display_name'] ?? 'Familielid';
        $_SESSION['access_token'] = $res['data']['access_token'];

        // Update Presence in database
        supabaseRequest('presence', 'POST', [
            'display_name' => $_SESSION['user_name'],
            'last_seen' => date('c')
        ]);

        header("Location: index.php?view=dashboard");
    } else {
        $error = $res['data']['error_description'] ?? 'Ongeldige inloggegevens';
        header("Location: index.php?view=login&error=" . urlencode($error));
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php?view=login");
}