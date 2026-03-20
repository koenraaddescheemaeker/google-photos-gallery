<?php
require_once 'config.php';

$token = getValidAccessToken();

if (!$token) {
    echo "<body style='background:#000;color:#fff;font-family:sans-serif;padding:50px;'>";
    echo "<h2 style='color:#ef4444;'>Geen geldige sleutel!</h2>";
    echo "<p>De database is leeg of de Refresh Token is ongeldig. Vul de tokens opnieuw in via de SQL Editor.</p>";
    exit;
}

$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token", 
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($raw, true);
curl_close($ch);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-10">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-black italic mb-8">FORCEKES <span class="text-blue-500">ADMIN</span></h1>

        <?php if ($httpCode === 200): ?>
            <div class="grid grid-cols-2 gap-6">
                <?php foreach (($res['albums'] ?? []) as $album): ?>
                    <div class="bg-zinc-900 p-4 rounded-3xl border border-zinc-800">
                        <?php if(isset($album['coverPhotoBaseUrl'])): ?>
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w400" class="w-full h-40 object-cover rounded-2xl mb-4">
                        <?php else: ?>
                            <div class="w-full h-40 bg-zinc-800 rounded-2xl mb-4 flex items-center justify-center text-zinc-600">Geen cover</div>
                        <?php endif; ?>
                        <h3 class="font-bold"><?= htmlspecialchars($album['title'] ?? 'Naamloos Album') ?></h3>
                        <p class="text-xs text-zinc-500 mt-2"><?= $album['mediaItemsCount'] ?? 0 ?> foto's</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-red-900/20 border border-red-500 p-8 rounded-3xl text-red-400">
                <h2 class="text-xl font-bold mb-4">🚫 Google API Fout (<?= $httpCode ?>)</h2>
                <p class="mb-4">Google weigert het verzoek. Hier is de exacte reden van Google:</p>
                <pre class="bg-black p-4 rounded-xl text-sm overflow-auto text-green-400"><?= htmlspecialchars(print_r($res, true)) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>