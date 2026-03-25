<?php
/**
 * FORCEKES - admin.php (The X-Ray Edition)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
$token = trim(getValidAccessToken());

if (!$token) {
    die("Geen token gevonden. <a href='login.php?pw=admin123'>Log opnieuw in</a>");
}

// 1. Vraag aan Google wat deze token PRECIES mag (TokenInfo)
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

// 2. Probeer de albums op te halen met extra headers
function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $token,
        "Accept: application/json",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode($response, true), 'raw' => $response];
}

$res = callPhotosAPI("albums?pageSize=10", $token);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | X-Ray Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12 font-mono text-xs">
    <h1 class="text-blue-500 text-2xl font-black mb-8 italic uppercase italic">Technical <span class="text-white">X-Ray</span></h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-zinc-900 p-8 rounded-[2.5rem] border border-zinc-800">
            <h2 class="text-blue-400 font-bold mb-4 uppercase tracking-widest">Inhoud van de Token</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-zinc-500 mb-1">Geldig tot:</p>
                    <p class="text-white"><?= isset($tokenInfo['expires_in']) ? $tokenInfo['expires_in'] . " seconden" : "Verlopen" ?></p>
                </div>
                <div>
                    <p class="text-zinc-500 mb-1">Actieve Scopes (Dit is cruciaal!):</p>
                    <pre class="bg-black p-4 rounded-xl text-green-400 overflow-auto whitespace-pre-wrap"><?= $tokenInfo['scope'] ?? 'GEEN SCOPES GEVONDEN' ?></pre>
                </div>
            </div>
        </div>

        <div class="bg-zinc-900 p-8 rounded-[2.5rem] border border-zinc-800">
            <h2 class="text-blue-400 font-bold mb-4 uppercase tracking-widest">Photos API Response</h2>
            <p class="mb-4">Status: <span class="<?= $res['status'] == 200 ? 'text-green-500' : 'text-red-500' ?> font-bold"><?= $res['status'] ?></span></p>
            <pre class="bg-black p-4 rounded-xl text-zinc-400 overflow-auto h-64 text-[10px]"><?= htmlspecialchars($res['raw']) ?></pre>
        </div>
    </div>

    <?php if (strpos($tokenInfo['scope'] ?? '', 'photoslibrary') === false): ?>
        <div class="mt-8 p-8 bg-red-900/20 border border-red-500 rounded-[2.5rem] text-red-500">
            <h3 class="font-bold mb-2">DIAGNOSE:</h3>
            <p>Google zegt dat je token <strong>geen</strong> foto-rechten heeft. Hoewel je het vinkje hebt gezet, is het niet in de token terechtgekomen. Dit gebeurt vaak als de 'Client ID' in de code niet exact matcht met het project waar de API aanstaat.</p>
        </div>
    <?php endif; ?>
</body>
</html>