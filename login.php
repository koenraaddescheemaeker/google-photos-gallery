<?php
/**
 * login.php
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

if (!isset($_GET['pw']) || $_GET['pw'] !== 'admin123') { die("403"); }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Final Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-zinc-900 p-10 rounded-[3rem] text-center border border-zinc-800">
        <h1 class="text-3xl font-black italic text-blue-500 mb-6 uppercase">Laatste Poging</h1>
        <p class="text-zinc-500 text-sm mb-8">Zorg dat je eerst de app hebt verwijderd in je Google-instellingen!</p>
        <a href="<?= $authUrl ?>" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-bold uppercase tracking-widest text-xs">
            Start Schone Handshake
        </a>
    </div>
</body>
</html>