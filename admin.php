<?php
/** FORCEKES - admin.php (Scope Auditor) */
require_once 'config.php';

$token = getValidAccessToken();
$scopeAudit = null;
$apiResult = null;

if ($token) {
    // 1. Controleer wat er ECHT in de token zit
    $ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $scopeAudit = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // 2. Probeer de API aan te roepen
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $apiResult = json_decode(curl_exec($ch), true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12 font-mono text-[10px]">
    <h1 class="text-blue-500 text-2xl font-black mb-10 italic uppercase">Audit <span class="text-white">Protocol</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <div class="bg-zinc-900 p-8 rounded-[2rem] border border-zinc-800">
            <h2 class="text-blue-400 font-bold mb-4 uppercase tracking-widest text-xs">Token Inhoud (Google Server)</h2>
            <p class="mb-4">Scopes die Google aan jou heeft toegekend:</p>
            <pre class="bg-black p-4 rounded-xl text-green-400 overflow-auto whitespace-pre-wrap">
<?= isset($scopeAudit['scope']) ? str_replace(' ', "\n", $scopeAudit['scope']) : 'GEEN SCOPES GEVONDEN' ?>
            </pre>
            <?php if (isset($scopeAudit['scope']) && strpos($scopeAudit['scope'], 'photoslibrary') === false): ?>
                <p class="mt-4 text-red-500 font-bold">❌ FOUT: De 'photoslibrary' scope ontbreekt in je token!</p>
            <?php endif; ?>
        </div>

        <div class="bg-zinc-900 p-8 rounded-[2rem] border border-zinc-800">
            <h2 class="text-blue-400 font-bold mb-4 uppercase tracking-widest text-xs">API Response</h2>
            <p class="mb-2">HTTP Code: <span class="font-bold"><?= $httpCode ?? 'N/A' ?></span></p>
            <pre class="bg-black p-4 rounded-xl text-zinc-500 overflow-auto h-48"><?= print_r($apiResult, true) ?></pre>
        </div>
    </div>

    <div class="mt-10 text-center">
        <p class="text-zinc-600 mb-6 italic">Als 'photoslibrary' niet in het linkerblokje staat, weigert Google de scope ondanks je vinkje.</p>
        <a href="login.php" class="px-10 py-4 bg-zinc-800 hover:bg-white hover:text-black rounded-xl font-bold uppercase tracking-widest transition-all">Nieuwe Handshake</a>
    </div>
</body>
</html>