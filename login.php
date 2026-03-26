<?php
/**
 * FORCEKES - login.php (Hardcoded Edition)
 */
$clientID = '483664701477-oe11ldk8bitgvc8vi2m7ootvrpbb0ki1.apps.googleusercontent.com';
$redirectUri = 'https://new.forcekes.be/google-callback.php';

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    'scope'                  => 'openid email https://www.googleapis.com/auth/photoslibrary',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account',
    'include_granted_scopes' => 'false',
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Auth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="text-white flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 p-16 rounded-[3rem] shadow-2xl text-center">
        <h1 class="text-3xl font-black italic uppercase text-blue-600 mb-8 tracking-tighter italic">FORCEKES <span class="text-white">START</span></h1>
        <p class="text-zinc-500 text-[11px] mb-12 leading-relaxed italic">Vink dadelijk de checkbox voor <strong>Google Photos</strong> aan.</p>
        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] transition-all">Verbinden</a>
    </div>
</body>
</html>