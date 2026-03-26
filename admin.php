<?php
/**
 * FORCEKES - admin.php (The Truth Machine)
 */
require_once 'config.php';

// 1. Check Database Verbinding
$resDB = supabaseRequest('google_tokens?id=eq.1', 'GET');
$tokenFromDB = $resDB[0] ?? null;

// 2. Haal token via de officiële functie
$token = getValidAccessToken();

$apiOutput = null;
$httpCode = null;

if ($token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $apiOutput = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

$albums = ($apiOutput) ? json_decode($apiOutput, true)['albums'] ?? [] : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #000; color: #fff; font-family: sans-serif; }</style>
</head>
<body class="p-10 text-xs">
    <h1 class="text-blue-500 font-black text-2xl mb-8 uppercase italic">Diagnostic <span class="text-white">Panel</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-zinc-900 p-6 rounded-3xl border border-zinc-800">
            <h2 class="text-zinc-500 font-bold uppercase mb-4 tracking-widest">1. Database Status</h2>
            <p class="mb-2">Rij gevonden in DB: <span class="<?= $tokenFromDB ? 'text-green-500' : 'text-red-500' ?>"><?= $tokenFromDB ? 'JA' : 'NEE (ID 1 bestaat niet!)' ?></span></p>
            <p class="mb-2">Access Token aanwezig: <span class="<?= !empty($tokenFromDB['access_token']) ? 'text-green-500' : 'text-red-500' ?>"><?= !empty($tokenFromDB['access_token']) ? 'JA' : 'NEE' ?></span></p>
            <p>Refresh Token aanwezig: <span class="<?= !empty($tokenFromDB['refresh_token']) ? 'text-green-500' : 'text-red-500' ?>"><?= !empty($tokenFromDB['refresh_token']) ? 'JA' : 'NEE' ?></span></p>
        </div>

        <div class="bg-zinc-900 p-6 rounded-3xl border border-zinc-800">
            <h2 class="text-zinc-500 font-bold uppercase mb-4 tracking-widest">2. Google API Status</h2>
            <p class="mb-2">HTTP Code: <span class="<?= $httpCode == 200 ? 'text-green-500' : 'text-red-500' ?> font-bold"><?= $httpCode ?? 'N/A' ?></span></p>
            <p>Aantal albums: <span class="text-blue-500 font-bold"><?= count($albums) ?></span></p>
        </div>
    </div>

    <div class="mt-6 bg-zinc-900 p-6 rounded-3xl border border-zinc-800">
        <h2 class="text-zinc-500 font-bold uppercase mb-4 tracking-widest">Raw Google Response</h2>
        <pre class="bg-black p-4 rounded-xl text-zinc-400 overflow-auto max-h-40"><?= htmlspecialchars($apiOutput ?? 'Geen data') ?></pre>
    </div>

    <div class="mt-10 text-center">
        <a href="login.php" class="inline-block px-8 py-4 bg-blue-600 rounded-2xl font-bold uppercase tracking-widest">Nieuwe Handshake</a>
    </div>
</body>
</html>