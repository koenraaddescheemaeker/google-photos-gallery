<?php
/**
 * FORCEKES - Admin Google Login
 */
require_once 'config.php';

// Simpele beveiliging: verander 'admin123' naar iets sterks
$adminPassword = 'skoen123'; 

if (!isset($_GET['pw']) || $_GET['pw'] !== $adminPassword) {
    die("Toegang geweigerd. Gebruik login.php?pw=jouw_wachtwoord");
}

// De Google Auth URL opbouwen
$params = [
    'client_id'     => getenv('GOOGLE_CLIENT_ID'),
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php',
    'response_type' => 'code',
    'scope'         => 'https://www.googleapis.com/auth/photoslibrary', // Master Scope
    'access_type'   => 'offline',        // CRUCIAAL voor de refresh_token
    'prompt'        => 'consent',       // Dwingt Google om de refresh_token te tonen
    'include_granted_scopes' => 'true',
    'state'         => bin2hex(random_bytes(16))
];

$authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white flex items-center justify-center h-screen font-sans">
    <div class="bg-zinc-900 p-12 rounded-[3rem] border border-zinc-800 text-center max-w-md">
        <h1 class="text-3xl font-black italic mb-6 uppercase tracking-tighter">Google <span class="text-blue-500">Photos</span> Koppelen</h1>
        <p class="text-zinc-500 mb-8 text-sm leading-relaxed">Klik op de knop om de server direct toestemming te geven voor je albums. Dit herstelt alle 403-fouten.</p>
        <a href="<?= $authUrl ?>" class="inline-block bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-8 rounded-2xl transition-all uppercase tracking-widest text-xs">
            Start Handshake
        </a>
    </div>
</body>
</html>