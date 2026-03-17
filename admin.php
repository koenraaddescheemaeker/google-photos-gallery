<?php
require_once 'config.php';

$accessToken = getValidAccessToken();
if (!$accessToken) {
    header('Location: google-auth.php');
    exit;
}

// 1. Haal albums op bij Google Photos
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$googleResponse = json_decode(curl_exec($ch), true);
curl_close($ch);

$albums = $googleResponse['albums'] ?? [];

// 2. Verwerk opslaan
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_album_id'])) {
    $res = supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['selected_album_id'],
        'active_album_title' => $_POST['selected_album_title']
    ]);
    $message = "✅ Portaal succesvol bijgewerkt!";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Familie Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen p-6 md:p-12">

    <div class="max-w-3xl mx-auto">
        <header class="mb-12">
            <h1 class="text-4xl font-semibold text-slate-900 tracking-tight">Beheer Portaal</h1>
            <p class="text-slate-500 mt-2">Kies het Google Photos album voor de hoofdpagina.</p>
        </header>

        <?php if ($message): ?>
            <div class="mb-8 p-4 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-2xl animate-pulse">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="grid gap-4">
            <?php foreach ($albums as $album): ?>
                <label class="group relative flex items-center p-5 bg-white rounded-3xl border border-slate-200 hover:border-blue-400 hover:shadow-xl transition-all cursor-pointer">
                    <input type="radio" name="selected_album_id" value="<?php echo $album['id']; ?>" class="w-5 h-5 text-blue-600 focus:ring-blue-500 border-slate-300" required>
                    <input type="hidden" name="selected_album_title" value="<?php echo htmlspecialchars($album['title']); ?>">
                    
                    <div class="ml-6 flex items-center flex-1">
                        <img src="<?php echo $album['coverPhotoBaseUrl']; ?>=w150-h150-c" class="w-16 h-16 rounded-2xl object-cover shadow-sm group-hover:scale-105 transition-transform" alt="">
                        <div class="ml-5">
                            <span class="block font-semibold text-slate-800 text-lg"><?php echo htmlspecialchars($album['title']); ?></span>
                            <span class="text-slate-400 text-sm"><?php echo $album['mediaItemsCount'] ?? 0; ?> items</span>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>

            <button type="submit" class="mt-8 w-full bg-slate-900 text-white py-5 rounded-3xl font-bold text-lg hover:bg-black transition-all shadow-2xl active:scale-[0.98]">
                Album Activeren op Portaal
            </button>
        </form>

        <footer class="mt-12 text-center border-t border-slate-200 pt-8">
            <a href="google-auth.php" class="text-slate-400 hover:text-blue-600 transition-colors text-sm">Google Account Opnieuw Koppelen</a>
        </footer>
    </div>

</body>
</html>