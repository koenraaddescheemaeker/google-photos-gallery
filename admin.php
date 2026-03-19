<?php
/**
 * admin.php - De 'Geen Gezeur' Editie
 */
require_once 'config.php';

// 1. Haal de token op
$token = getValidAccessToken();

if (!$token) {
    echo "<body style='background:#000;color:#fff;font-family:sans-serif;padding:50px;'>";
    echo "<h2 style='color:#ef4444;'>Geen geldige sleutel gevonden in de database.</h2>";
    echo "<p>De database is leeg of de verbinding is verbroken.</p>";
    echo "<a href='google-auth.php' style='background:#3b82f6;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;'>Klik hier om opnieuw te verbinden</a>";
    exit;
}

// 2. Test de sleutel direct bij Google
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
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

        <?php if ($httpCode === 403): ?>
            <div class="bg-red-500/20 border border-red-500 p-8 rounded-3xl text-red-400 mb-10">
                <h2 class="text-xl font-bold mb-2">🚫 Google weigert de toegang (403)</h2>
                <p class="mb-4">Je bent ingelogd, maar je hebt <strong>geen vinkje</strong> gezet tijdens het inloggen.</p>
                <ol class="list-decimal ml-5 space-y-2 text-sm opacity-80">
                    <li>Ga naar <a href="https://myaccount.google.com/permissions" target="_blank" class="underline">Google Account Instellingen</a> en verwijder de toegang voor "forcekes.be".</li>
                    <li>Voer <code>TRUNCATE google_tokens;</code> uit in je Supabase SQL editor.</li>
                    <li>Klik op de blauwe knop hieronder en <strong>vink het vakje aan</strong> bij Google.</li>
                </ol>
                <div class="mt-8">
                    <a href="google-auth.php" class="bg-red-600 text-white px-6 py-3 rounded-xl font-bold uppercase text-xs tracking-widest">Nu echt goed inloggen</a>
                </div>
            </div>
        <?php elseif ($httpCode === 200): ?>
            <div class="grid grid-cols-2 gap-6">
                <?php foreach (($res['albums'] ?? []) as $album): ?>
                    <div class="bg-zinc-900 p-4 rounded-3xl border border-zinc-800">
                        <img src="<?= $album['coverPhotoBaseUrl'] ?>=w400" class="w-full h-40 object-cover rounded-2xl mb-4">
                        <h3 class="font-bold"><?= htmlspecialchars($album['title']) ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-800 p-6 rounded-2xl">
                <p>Status: <?= $httpCode ?></p>
                <pre class="text-xs mt-4 text-zinc-500"><?= print_r($res, true) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>