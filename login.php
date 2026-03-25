<?php
/**
 * FORCEKES - login.php (The Real Deal)
 */
$clientID = '937650128725-h6c3dbh2hs7q93qjbq95mp34kccqkthp.apps.googleusercontent.com';
$redirectUri = 'https://new.forcekes.be/google-callback.php';

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    'scope'                  => 'openid email https://www.googleapis.com/auth/photoslibrary.readonly',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account',
    'include_granted_scopes' => 'true', // We voegen deze toe om zeker te zijn
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Final Step</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="text-white flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 p-12 rounded-[3rem] shadow-2xl text-center">
        <h1 class="text-3xl font-black italic uppercase text-blue-500 mb-6 italic">Final <span class="text-white">Handshake</span></h1>
        <p class="text-zinc-500 text-xs mb-10 leading-relaxed italic">
            Nu de verbinding staat, gaan we de kluis openen. <br><strong>Vergeet het vinkje voor je foto's niet!</strong>
        </p>
        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black uppercase text-[11px] tracking-widest transition-all shadow-lg shadow-blue-900/40">
            Verbinden & Foto's Laden
        </a>
    </div>
</body>
</html>