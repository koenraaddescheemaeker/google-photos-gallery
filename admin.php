<?php
require_once 'config.php';
$token = getValidAccessToken();
if (!$token) header('Location: google-auth.php');

// Verwerk opslaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aid'])) {
    supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['aid'],
        'active_album_title' => $_POST['atitle']
    ]);
    $msg = "✅ Album geactiveerd!";
}

// Haal albums op
// Haal albums op met extra precisie
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Length: 0",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($raw, true);
curl_close($ch);

$albums = $res['albums'] ?? [];
$error = $res['error'] ?? null;

// Debug voor jou (verwijder dit later)
if ($httpCode !== 200) {
    // Dit helpt ons zien of de API zelf wel reageert
}
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Admin - Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-12">
            <h1 class="text-3xl font-bold italic tracking-tighter">FORCEKES <span class="text-blue-500 text-sm not-italic ml-2">ADMIN</span></h1>
            <a href="google-auth.php" class="text-xs bg-slate-800 px-3 py-1 rounded-full text-slate-400 hover:text-white">Opnieuw inloggen</a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 p-4 rounded-2xl text-red-400 mb-8">
                <strong>Google Error:</strong> <?= $error['message'] ?>
                <p class="text-xs mt-2 italic text-red-300/70">Waarschijnlijk ontbreken de vinkjes tijdens de login.</p>
            </div>
        <?php endif; ?>

        <?php if (empty($albums) && !$error): ?>
            <p class="text-slate-500 text-center py-20 bg-slate-900/50 rounded-3xl border border-dashed border-slate-800">
                Geen albums gevonden. Maak eerst een album aan in Google Photos.
            </p>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach($albums as $a): ?>
            <div class="bg-slate-900 rounded-3xl overflow-hidden border border-slate-800 hover:border-blue-500 transition shadow-2xl">
                <div class="h-48 bg-slate-800 relative">
                    <img src="<?= $a['coverPhotoBaseUrl'] ?>=w600" class="w-full h-full object-cover opacity-60">
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-1 truncate"><?= htmlspecialchars($a['title']) ?></h3>
                    <p class="text-slate-500 text-xs mb-6 uppercase tracking-widest"><?= $a['mediaItemsCount'] ?? '0' ?> FOTO'S</p>
                    <form method="POST">
                        <input type="hidden" name="aid" value="<?= $a['id'] ?>">
                        <input type="hidden" name="atitle" value="<?= htmlspecialchars($a['title']) ?>">
                        <button class="w-full bg-blue-600 hover:bg-blue-500 py-3 rounded-2xl font-bold transition active:scale-95">Selecteer dit album</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>