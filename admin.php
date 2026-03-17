<?php
require_once 'config.php';
$accessToken = getValidAccessToken();
if (!$accessToken) { header('Location: google-auth.php'); exit; }

// Haal albums op
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$googleResponse = json_decode(curl_exec($ch), true);
curl_close($ch);
$albums = $googleResponse['albums'] ?? [];

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_album_id'])) {
    $res = supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['selected_album_id'],
        'active_album_title' => $_POST['selected_album_title']
    ]);
    $message = "✅ Album geactiveerd!";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Admin - Familie Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 p-8 font-['Inter']">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Beheer Foto's</h1>
        <p class="text-slate-500 mb-8">Kies welk Google Photos album getoond moet worden.</p>
        <?php if ($message) echo "<div class='mb-6 p-4 bg-green-100 text-green-700 rounded-lg'>$message</div>"; ?>
        <form method="POST" class="bg-white rounded-2xl shadow-sm border p-6 space-y-4">
            <?php foreach ($albums as $album): ?>
                <label class="flex items-center p-4 border rounded-xl hover:bg-slate-50 cursor-pointer transition-all">
                    <input type="radio" name="selected_album_id" value="<?php echo $album['id']; ?>" class="w-4 h-4 text-blue-600" required>
                    <input type="hidden" name="selected_album_title" value="<?php echo htmlspecialchars($album['title']); ?>">
                    <img src="<?php echo $album['coverPhotoBaseUrl']; ?>=w100-h100-c" class="w-12 h-12 rounded-lg object-cover ml-4">
                    <div class="ml-4">
                        <div class="font-semibold text-slate-800"><?php echo htmlspecialchars($album['title']); ?></div>
                        <div class="text-xs text-slate-400"><?php echo $album['mediaItemsCount'] ?? 0; ?> foto's</div>
                    </div>
                </label>
            <?php endforeach; ?>
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-semibold hover:bg-black transition-all shadow-lg">Album Activeren</button>
        </form>
    </div>
</body>
</html>