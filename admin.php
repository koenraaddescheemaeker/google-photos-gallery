<?php
/** FORCEKES - admin.php (The Final Boss Edition) */
require_once 'config.php';

$token = getValidAccessToken();
$projectID = '483664701477'; // Jouw projectnummer uit de error
$results = [];

if ($token) {
    // We testen twee verschillende endpoints
    $endpoints = [
        'albums' => 'https://photoslibrary.googleapis.com/v1/albums?pageSize=20',
        'fotos'  => 'https://photoslibrary.googleapis.com/v1/mediaItems?pageSize=20'
    ];

    foreach ($endpoints as $key => $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $token",
                "Accept: application/json",
                "X-Goog-User-Project: $projectID" // Dwingt Google om naar DIT project te kijken
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $results[$key] = ['code' => $code, 'data' => json_decode($res, true)];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12 font-mono text-[10px]">
    <h1 class="text-blue-500 text-3xl font-black mb-10 italic uppercase tracking-tighter">System <span class="text-white">Override</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <?php foreach ($results as $key => $res): ?>
            <div class="bg-zinc-900 p-8 rounded-[3rem] border <?= $res['code'] == 200 ? 'border-green-500/50' : 'border-red-500/50' ?>">
                <h2 class="text-blue-400 font-bold mb-4 uppercase tracking-widest text-xs">Endpoint: <?= strtoupper($key) ?></h2>
                <p class="mb-4">Status: <span class="font-bold <?= $res['code'] == 200 ? 'text-green-500' : 'text-red-500' ?>"><?= $res['code'] ?></span></p>
                <pre class="bg-black p-4 rounded-xl text-zinc-500 overflow-auto h-64"><?= print_r($res['data'], true) ?></pre>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($results['albums']['code'] == 200): ?>
        <div class="mt-12 p-8 bg-green-500/10 border border-green-500 rounded-[3rem] text-green-500 text-center">
            <h2 class="text-xl font-black uppercase italic">We zijn binnen! 🥂</h2>
            <p class="mt-2 uppercase text-[10px] font-bold tracking-[0.3em]">De kluis is geopend. De cirkel is doorbroken.</p>
        </div>
    <?php endif; ?>
</body>
</html>