<?php
require_once 'config.php';
$token = getValidAccessToken();
if (!$token) { header('Location: google-auth.php'); exit; }

// Haal albums op met de meest pure headers
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
$albums = $res['albums'] ?? [];
$error = $res['error']['message'] ?? null;
?>
<!DOCTYPE html><html lang="nl"><head><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-slate-950 text-white p-10 font-sans">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-black italic mb-10 border-b border-slate-800 pb-4">FORCEKES ADMIN</h1>
        
        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 p-6 rounded-2xl text-red-400 mb-8">
                <p class="font-bold">Google API zegt nog steeds Nee:</p>
                <p class="text-sm"><?= $error ?></p>
                <p class="mt-4 text-xs italic opacity-60">Heb je de toegang eerst verwijderd in je Google Account instellingen?</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($albums as $a): ?>
                <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800">
                    <img src="<?= $a['coverPhotoBaseUrl'] ?>=w400" class="w-full h-40 object-cover rounded-2xl mb-4">
                    <h3 class="font-bold mb-4"><?= htmlspecialchars($a['title']) ?></h3>
                    <form method="POST" action="index.php"> <input type="hidden" name="aid" value="<?= $a['id'] ?>">
                        <button class="w-full bg-blue-600 py-3 rounded-xl font-bold uppercase text-xs">Activeer</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body></html>