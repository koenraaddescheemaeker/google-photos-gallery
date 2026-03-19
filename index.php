<?php
// index.php
require_once 'config.php';
$res = supabaseRequest('google_tokens?id=eq.1&select=*');
$db = $res[0] ?? null;
$token = getValidAccessToken();

$photos = [];
if ($token && isset($db['active_album_id'])) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["albumId" => $db['active_album_id']]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    $photos = $res['mediaItems'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Familie Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-6">
    <header class="text-center mb-12">
        <h1 class="text-5xl font-black italic">FORCEKES</h1>
        <p class="text-slate-500 uppercase tracking-tighter"><?= $db['active_album_title'] ?? 'Selecteer een album' ?></p>
    </header>
    <div class="columns-2 md:columns-4 gap-4">
        <?php foreach($photos as $p): ?>
            <img src="<?= $p['baseUrl'] ?>=w800" class="w-full mb-4 rounded-xl shadow-2xl">
        <?php endforeach; ?>
    </div>
</body>
</html>