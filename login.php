<?php
/**
 * FORCEKES - Admin Google Login (Scope Fix Edition)
 */

// We halen de variabelen direct uit de omgeving om 'config.php' issues te vermijden
$clientID = trim(getenv('GOOGLE_CLIENT_ID'));
$redirectUri = 'https://new.forcekes.be/google-callback.php';

// De scopes die we nodig hebben:
// 1. openid/email -> Voor de naam van het account
// 2. photoslibrary -> Voor de eigenlijke foto-albums
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
    'access_type'            => 'offline',   // Nodig voor de refresh_token
    'prompt'                 => 'consent',   // Dwingt Google om de vinkjes ALTIJD te tonen
    'include_granted_scopes' => 'true',
    'state'                  => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);

// Simpele beveiliging: verander 'admin123' naar je eigen wachtwoord in de URL
$adminPassword = 'admin123'; 

if (!isset($_GET['pw']) || $_GET['pw'] !== $adminPassword) {
    die("<body style='background:#000;color:#333;display:flex;align-items:center;justify-center;height:100vh;font-family:sans-serif;'><h1>403 - Verboden toegang</h1></body>");
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Connect Google</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; }
    </style>
</head>
<body class="text-white flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-zinc-900 border border-zinc-800 p-10 rounded-[3rem] shadow-2xl text-center">
        <div class="mb-8">
            <div class="w-20 h-20 bg-blue-600 rounded-3xl mx-auto flex items-center justify-center mb-6 shadow-lg shadow-blue-900/20">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M9 15h6"/><path d="M9 11h6"/><path d="M9 19h1"/></svg>
            </div>
            <h1 class="text-3xl font-black italic uppercase tracking-tighter mb-2">Google <span class="text-blue-500">Auth</span></h1>
            <p class="text-zinc-500 text-sm italic">Stap 1: De Handshake</p>
        </div>

        <div class="bg-blue-950/30 border border-blue-900/50 p-6 rounded-2xl mb-10 text-left">
            <h4 class="text-blue-400 font-bold text-xs uppercase tracking-widest mb-2">Let op bij het inloggen:</h4>
            <p class="text-zinc-400 text-xs leading-relaxed">
                Google toont zometeen een lijst met rechten. <br><br>
                <strong>VINK HANDMATIG AAN:</strong><br>
                <span class="text-white">"Google Photos: Uw bibliotheek bekijken"</span><br><br>
                Als je dit niet aanvinkt, krijg je een leeg dashboard!
            </p>
        </div>

        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl transition-all uppercase tracking-widest text-[10px] shadow-lg shadow-blue-900/40">
            Verbinding maken
        </a>

        <p class="mt-8 text-zinc-600 text-[10px] uppercase tracking-widest">Forcekes Portaal &copy; 2026</p>
    </div>

</body>
</html>