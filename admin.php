<?php
/** FORCEKES - admin.php (The Minimalist) */
require_once 'config.php';

$token = getValidAccessToken();
$results = ['albums' => null, 'fotos' => null];

if ($token) {
    $endpoints = [
        'albums' => 'https://photoslibrary.googleapis.com/v1/albums?pageSize=50',
        'fotos'  => 'https://photoslibrary.googleapis.com/v1/mediaItems?pageSize=50'
    ];

    foreach ($endpoints as $key => $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"], // GEEN EXTRA HEADERS
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $results[$key] = ['code' => $code, 'data' => json_decode($res, true)];
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12 font-mono text-[10px]">
    <h1 class="text-blue-600 text-3xl font-black mb-10 italic">FORCEKES <span class="text-white">DEBUG</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach ($results as $type => $res): ?>
            <div class="bg-zinc-900 p-8 rounded-[3rem] border <?= $res['code'] == 200 ? 'border-zinc-800' : 'border-red-500/50' ?>">
                <h2 class="text-zinc-500 font-bold mb-4 uppercase tracking-widest text-xs"><?= $type ?></h2>
                <p class="text-2xl font-black mb-4">HTTP <?= $res['code'] ?: 'GEEN DATA' ?></p>
                <pre class="bg-black p-4 rounded-xl text-zinc-500 overflow-auto h-48"><?= print_r($res['data'], true) ?></pre>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-10 p-8 bg-zinc-900 rounded-[3rem] border border-zinc-800">
        <h2 class="text-blue-500 font-bold mb-4 uppercase tracking-widest text-xs">Token Debug</h2>
        <p class="text-zinc-500">Huidige token (eerste 15 tekens): <span class="text-white"><?= substr($token, 0, 15) ?>...</span></p>
    </div>
</body>
</html>