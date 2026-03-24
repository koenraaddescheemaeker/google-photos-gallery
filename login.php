<?php
/**
 * FORCEKES - login.php (Full Scope Edition)
 */
$clientID = trim(getenv('GOOGLE_CLIENT_ID'));
$params = [
    'client_id'     => $clientID,
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php',
    'response_type' => 'code',
    // We vragen nu zowel de volledige als de readonly scope voor maximale kans
    'scope'         => 'openid email https://www.googleapis.com/auth/photoslibrary https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'   => 'offline',
    'prompt'        => 'consent select_account',
    'state'         => bin2hex(random_bytes(16))
];
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
if (!isset($_GET['pw']) || $_GET['pw'] !== 'admin123') { die("403"); }
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>Login</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white flex items-center justify-center min-h-screen">
    <div class="bg-zinc-900 p-12 rounded-[3rem] text-center border border-zinc-800">
        <h1 class="text-3xl font-black italic text-blue-500 mb-6 uppercase">START HANDSHAKE</h1>
        <p class="text-zinc-500 text-xs mb-8">Zorg dat je als 'Test User' bent toegevoegd in Google Cloud!</p>
        <a href="<?= $authUrl ?>" class="py-5 px-10 bg-blue-600 rounded-2xl font-bold uppercase text-xs">Inloggen bij Google</a>
    </div>
</body>
</html>