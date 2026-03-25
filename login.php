<?php
/**
 * FORCEKES - login.php (Clean & Minimal)
 */

// De omgevingsvariabelen uit Coolify
$clientID = trim(getenv('GOOGLE_CLIENT_ID'));
$redirectUri = 'https://new.forcekes.be/google-callback.php';

// De absolute basis voor Google Photos
$scopes = [
    'openid',
    'email',
    'https://www.googleapis.com/auth/photoslibrary.readonly'
];

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    'scope'                  => implode(' ', $scopes),
    'access_type'            => 'offline',
    'prompt'                 => 'consent select_account',
    'include_granted_scopes' => 'false',
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

// Beveiliging: vervang 'admin123' eventueel in je URL
if (!isset($_GET['pw']) || $_GET['pw'] !== 'admin123') {
    header('HTTP/1.0 403 Forbidden');
    die("Toegang geweigerd. Gebruik de juiste link.");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="text-white flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 p-12 rounded-[3rem] shadow-2xl text-center">
        <h1 class="text-3xl font-black italic uppercase text-blue-500 mb-6">Start <span class="text-white">Handshake</span></h1>
        <p class="text-zinc-500 text-xs mb-10 leading-relaxed italic">Zorg dat je als 'Test User' bent toegevoegd in de Google Cloud Console!</p>
        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all shadow-lg shadow-blue-900/40">
            Verbinden met Google
        </a>
    </div>
</body>
</html>