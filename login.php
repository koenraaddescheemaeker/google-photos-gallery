<?php
/**
 * FORCEKES - login.php (Simplified Scope)
 */
$clientID = trim(getenv('GOOGLE_CLIENT_ID'));
$params = [
    'client_id'     => $clientID,
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php',
    'response_type' => 'code',
    'scope'         => 'openid email https://www.googleapis.com/auth/photoslibrary',
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account',
    'state'         => bin2hex(random_bytes(16))
];
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

if (!isset($_GET['pw']) || $_GET['pw'] !== 'admin123') { 
    header('HTTP/1.0 403 Forbidden');
    die("Verboden toegang."); 
}
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>Login</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white flex items-center justify-center min-h-screen">
    <div class="bg-zinc-900 p-12 rounded-[3rem] text-center border border-zinc-800 shadow-2xl">
        <h1 class="text-3xl font-black italic text-blue-500 mb-6 uppercase tracking-tighter">FORCEKES LOGIN</h1>
        <a href="<?= $authUrl ?>" class="py-5 px-10 bg-blue-600 hover:bg-blue-500 rounded-2xl font-bold uppercase text-xs shadow-lg shadow-blue-900/40 block transition-all">
            Inloggen bij Google
        </a>
    </div>
</body>
</html>