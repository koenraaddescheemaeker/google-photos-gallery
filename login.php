<?php
/**
 * FORCEKES - login.php (Nuclear Scope Edition)
 */
$clientID = '937650128725-h6c3dbh2hs7q93qjbq95mp34kccqkthp.apps.googleusercontent.com';
$redirectUri = 'https://new.forcekes.be/google-callback.php';

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    // We gaan voor de volle mep (geen .readonly)
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
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white flex items-center justify-center min-h-screen">
    <div class="bg-zinc-900 p-12 rounded-[3rem] border border-zinc-800 text-center shadow-2xl">
        <h1 class="text-3xl font-black italic text-blue-500 mb-6 uppercase">Master <span class="text-white">Handshake</span></h1>
        <p class="text-zinc-500 text-xs mb-8 italic">Vink alle vakjes aan die Google je toont!</p>
        <a href="<?= $authUrl ?>" class="py-5 px-10 bg-blue-600 rounded-2xl font-bold uppercase text-xs block transition-all shadow-lg shadow-blue-900/40">Verbinding herstarten</a>
    </div>
</body>
</html>