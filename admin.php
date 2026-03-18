<?php
/**
 * admin.php - Debug Editie
 */
require_once 'config.php';

$token = getValidAccessToken();
if (!$token) {
    // Als we hier uitkomen, vindt de app geen tokens in de DB.
    header('Location: google-auth.php');
    exit;
}

// Albums ophalen met extra foutopsporing
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$rawResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$response = json_decode($rawResponse, true);
curl_close($ch);

$albums = $response['albums'] ?? [];
$error = $response['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin Debug - Familie Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-200 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Diagnose Dashboard</h1>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 p-4 rounded-xl mb-6">
                <h2 class="text-red-400 font-bold">Google API Fout (HTTP <?php echo $httpCode; ?>)</h2>
                <pre class="text-xs mt-2"><?php print_r($error); ?></pre>
                <p class="mt-4 text-sm text-red-300">Tip: Controleer of je het vinkje hebt gezet bij de inlogpoging!</p>
            </div>
        <?php endif; ?>

        <?php if (empty($albums) && !$error): ?>
            <div class="bg-yellow-500/20 border border-yellow-500 p-4 rounded-xl mb-6">
                <h2 class="text-yellow-400 font-bold">Geen albums gevonden</h2>
                <p class="text-sm">De verbinding is gelukt, maar de lijst is leeg. Heb je wel eigen albums (geen gedeelde) in Google Photos staan?</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($albums as $album): ?>
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800">
                    <h3 class="font-bold"><?php echo htmlspecialchars($album['title']); ?></h3>
                    <p class="text-xs text-slate-500"><?php echo $album['mediaItemsCount'] ?? '0'; ?> items</p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <hr class="my-10 border-slate-800">
        <h2 class="text-sm uppercase tracking-widest text-slate-500 mb-4">Systeem Status</h2>
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-900 p-3 rounded-lg">Database: ✅ Verbonden</div>
            <div class="bg-slate-900 p-3 rounded-lg">Token Status: ✅ Ontvangen</div>
        </div>
    </div>
</body>
</html>