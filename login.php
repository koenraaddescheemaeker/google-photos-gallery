<?php
/**
 * FORCEKES - login.php (The Enforcer)
 */
$clientID = '937650128725-h6c3dbh2hs7q93qjbq95mp34kccqkthp.apps.googleusercontent.com';
$redirectUri = 'https://new.forcekes.be/google-callback.php';

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    // We vragen de VOLLEDIGE scope aan
    'scope'                  => 'openid email https://www.googleapis.com/auth/photoslibrary',
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account', // Dwingt Google om de vinkjes weer te tonen
    'include_granted_scopes' => 'true',
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white flex items-center justify-center min-h-screen">
    <div class="bg-zinc-900 p-12 rounded-[3.5rem] border border-zinc-800 text-center shadow-2xl max-w-sm">
        <h1 class="text-3xl font-black italic text-blue-500 mb-6 uppercase tracking-tighter">Forcekes <span class="text-white">Reset</span></h1>
        <p class="text-zinc-500 text-[11px] mb-10 leading-relaxed italic">
            Na het klikken moet je bij Google het vakje voor <strong>"Google Photos"</strong> handmatig aanvinken!
        </p>
        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all shadow-lg shadow-blue-900/40">
            Start Schone Handshake
        </a>
    </div>
</body>
</html>