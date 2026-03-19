<?php
/**
 * admin.php - De Definitieve Album Selector
 */
require_once 'config.php';

$token = getValidAccessToken();
if (!$token) {
    header('Location: google-auth.php');
    exit;
}

$error = null;
$albums = [];

// 1. Haal de albums op (met extra strikte headers)
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json",
    "User-Agent: Forcekes-App-v1" // Google houdt van een naam
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($raw, true);
curl_close($ch);

if ($httpCode === 200) {
    $albums = $res['albums'] ?? [];
} else {
    $error = "Google API Fout ($httpCode): " . ($res['error']['message'] ?? 'Onbekende fout');
}

// 2. Verwerk activering van een album
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aid'])) {
    supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['aid'],
        'active_album_title' => $_POST['atitle']
    ]);
    $msg = "✅ Album '" . htmlspecialchars($_POST['atitle']) . "' is geactiveerd!";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#020617] text-white p-10 font-sans">
    <div class="max-w-5xl mx-auto">
        <header class="flex justify-between items-center mb-10 border-b border-slate-800 pb-6">
            <h1 class="text-3xl font-black italic">FORCEKES <span class="text-blue-500 not-italic text-sm">ADMIN</span></h1>
            <div class="flex gap-3">
                <a href="super-debug.php" class="text-xs bg-slate-800 px-3 py-2 rounded-lg">Diagnose</a>
                <a href="google-auth.php" class="text-xs bg-blue-600 px-3 py-2 rounded-lg">Ververs Inlog</a>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500 p-6 rounded-2xl text-red-400 mb-8">
                <strong>Blokkade:</strong> <?= $error ?>
                <p class="text-xs mt-2 opacity-70">Check in Google Console of je e-mail bij 'Test Users' staat.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($albums as $a): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden hover:border-blue-500 transition shadow-2xl">
                <img src="<?= $a['coverPhotoBaseUrl'] ?>=w400" class="w-full h-40 object-cover opacity-80">
                <div class="p-6">
                    <h3 class="font-bold text-lg mb-4 truncate"><?= htmlspecialchars($a['title']) ?></h3>
                    <form method="POST">
                        <input type="hidden" name="aid" value="<?= $a['id'] ?>">
                        <input type="hidden" name="atitle" value="<?= htmlspecialchars($a['title']) ?>">
                        <button class="w-full bg-white text-black py-3 rounded-xl font-black text-xs uppercase tracking-widest active:scale-95 transition">Selecteer</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>