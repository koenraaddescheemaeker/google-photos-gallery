<?php
/**
 * FORCEKES - Admin Google Login (Identity & Scope Fix)
 */

// Haal variabelen direct uit de omgeving
$clientID = trim(getenv('GOOGLE_CLIENT_ID'));
$redirectUri = 'https://new.forcekes.be/google-callback.php';

// De scopes die we nodig hebben voor identiteit + foto's
$scopes = [
    'openid',
    'email',
    'https://www.googleapis.com/auth/photoslibrary'
];

$params = [
    'client_id'              => $clientID,
    'redirect_uri'           => $redirectUri,
    'response_type'          => 'code',
    'scope'                  => implode(' ', $scopes),
    'access_type'            => 'offline',   
    'prompt'                 => 'consent select_account', // FORCEERT account-keuze en vinkjes
    'include_granted_scopes' => 'false',           // Voorkomt dat Google oude, beperkte rechten hergebruikt
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

// Beveiliging: pas 'admin123' aan naar je eigen wachtwoord
$adminPassword = 'admin123'; 

if (!isset($_GET['pw']) || $_GET['pw'] !== $adminPassword) {
    header('HTTP/1.0 403 Forbidden');
    die("<body style='background:#000;color:#333;display:flex;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;'><h1>403 - Toegang Geweigerd</h1></body>");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Google Handshake</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; }
    </style>
</head>
<body class="text-white flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 p-10 rounded-[3rem] shadow-2xl text-center">
        <div class="mb-8 text-center">
            <div class="inline-flex w-20 h-20 bg-blue-600 rounded-3xl items-center justify-center mb-6 shadow-lg shadow-blue-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 15h6"/><path d="M9 11h6"/><path d="M9 19h1"/></svg>
            </div>
            <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-2 italic text-blue-500">FORCEKES <span class="text-white">AUTH</span></h1>
            <p class="text-zinc-500 text-sm">Herstel de Google Photos verbinding</p>
        </div>

        <div class="bg-red-950/20 border border-red-900/40 p-6 rounded-2xl mb-10 text-left">
            <h4 class="text-red-400 font-bold text-[10px] uppercase tracking-[0.2em] mb-3">Belangrijke Instructie</h4>
            <ol class="text-zinc-400 text-xs leading-relaxed space-y-3 list-decimal pl-4">
                <li>Kies zometeen het account waar de <strong>albums</strong> in staan (Koen of Forcekes).</li>
                <li><strong>VINK HANDMATIG HET VAKJE AAN:</strong><br>
                    <span class="text-white font-bold italic">"Google Foto's: Uw bibliotheek bekijken..."</span>
                </li>
            </ol>
        </div>

        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl transition-all uppercase tracking-widest text-[11px] shadow-lg shadow-blue-900/40 transform active:scale-95">
            Verbinding Herstellen
        </a>

        <p class="mt-10 text-zinc-700 text-[10px] uppercase tracking-[0.5em]">System Ready &bull; 2026</p>
    </div>

</body>
</html>