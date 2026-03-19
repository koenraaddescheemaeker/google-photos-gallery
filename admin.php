<?php
// admin.php
require_once 'config.php';
$token = getValidAccessToken();
if (!$token) header('Location: google-auth.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aid'])) {
    supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['aid'],
        'active_album_title' => $_POST['atitle']
    ]);
}

$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
$albums = $res['albums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Admin - Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Album Selectie</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach($albums as $a): ?>
            <div class="bg-slate-900 p-6 rounded-3xl border border-slate-800">
                <img src="<?= $a['coverPhotoBaseUrl'] ?>=w300" class="w-full h-40 object-cover rounded-2xl mb-4">
                <h3 class="font-bold mb-4"><?= htmlspecialchars($a['title']) ?></h3>
                <form method="POST">
                    <input type="hidden" name="aid" value="<?= $a['id'] ?>">
                    <input type="hidden" name="atitle" value="<?= htmlspecialchars($a['title']) ?>">
                    <button class="bg-blue-600 px-4 py-2 rounded-xl text-sm w-full">Activeer dit album</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>