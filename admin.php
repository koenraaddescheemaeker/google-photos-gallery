<?php
/**
 * FORCEKES - admin.php (Debug Mode)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "";

$token = getValidAccessToken();

if (!$token) {
    echo "Fout: Geen token gevonden in database. <a href='login.php?pw=admin123'>Log opnieuw in</a>";
    exit;
}

echo "";

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return ['status' => 'CURL_ERROR', 'raw' => curl_error($ch)];
    }
    
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode($response, true), 'raw' => $response];
}

// We proberen de albums op te halen
$res = callPhotosAPI("albums?pageSize=10", $token);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin Debug</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-10 font-mono text-xs">
    <h1 class="text-blue-500 text-xl font-black mb-6 italic uppercase">Admin Debug Console</h1>
    
    <div class="space-y-4">
        <div class="bg-zinc-900 p-4 rounded-xl border border-zinc-800">
            <p class="text-zinc-500 uppercase font-bold mb-2">API Status Code:</p>
            <p class="<?= $res['status'] == 200 ? 'text-green-500' : 'text-red-500' ?> font-bold">
                <?= $res['status'] ?>
            </p>
        </div>

        <div class="bg-zinc-900 p-4 rounded-xl border border-zinc-800">
            <p class="text-zinc-500 uppercase font-bold mb-2">Raw Response van Google:</p>
            <pre class="bg-black p-4 rounded-lg overflow-auto max-h-64 text-blue-400"><?= htmlspecialchars($res['raw']) ?></pre>
        </div>

        <?php if ($res['status'] == 403): ?>
            <div class="bg-red-900/20 border border-red-500 p-6 rounded-2xl text-red-500">
                <p class="font-bold uppercase mb-2 text-sm">Analyse:</p>
                <p>Status 403 is logisch! We hebben net ingelogd met de "Isolatie-test" die <strong>geen</strong> foto-rechten vroeg. Google weigert dus de lijst.</p>
                <p class="mt-4 font-bold italic text-white underline">ACTIE: Nu we weten dat de verbinding werkt, kunnen we de foto-scopes weer aanzetten in login.php!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>