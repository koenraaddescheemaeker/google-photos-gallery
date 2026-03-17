<?php
require_once 'config.php';

// 1. Haal de Google Token op (automatische vernieuwing via onze config helper)
$accessToken = getValidAccessToken();

if (!$accessToken) {
    header('Location: google-auth.php');
    exit;
}

// 2. Haal albums op bij Google Photos
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
$googleResponse = json_decode(curl_exec($ch), true);
curl_close($ch);

$albums = $googleResponse['albums'] ?? [];

// 3. Verwerk opslaan (als het formulier is verzonden)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_album_id'])) {
    $albumId = $_POST['selected_album_id'];
    $albumTitle = $_POST['selected_album_title'];

    // Sla de keuze op in de 'settings' tabel (of maak een nieuwe tabel 'active_album')
    // We gebruiken UPSERT zodat we altijd maar 1 actief album hebben voor nu
    $res = supabaseRequest('settings?id=eq.1', 'PATCH', [
        'active_album_id' => $albumId,
        'active_album_title' => $albumTitle
    ]);
    
    $message = "✅ Album succesvol gekoppeld!";
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
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .premium-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body class="p-8">

    <div class="max-w-2xl mx-auto">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-semibold text-slate-800 tracking-tight">Beheer Portaal</h1>
                <p class="text-slate-500 mt-1">Selecteer het album dat op de startpagina moet verschijnen.</p>
            </div>
            <div class="h-10 w-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">K</div>
        </header>

        <?php if ($message): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="premium-card p-6">
            <h2 class="text-lg font-medium text-slate-700 mb-4">Google Photos Albums</h2>
            
            <?php if (empty($albums)): ?>
                <p class="text-slate-400 italic">Geen albums gevonden of geen toegang...</p>
            <?php else: ?>
                <form method="POST" class="space-y-4">
                    <div class="grid gap-3">
                        <?php foreach ($albums as $album): ?>
                            <label class="relative flex items-center p-4 cursor-pointer rounded-xl border border-slate-200 hover:bg-slate-50 transition-colors">
                                <input type="radio" name="selected_album_id" value="<?php echo $album['id']; ?>" class="h-4 w-4 text-blue-600 border-slate-300 focus:ring-blue-500" required>
                                <input type="hidden" name="selected_album_title" value="<?php echo htmlspecialchars($album['title']); ?>">
                                <div class="ml-4 flex items-center space-x-4">
                                    <?php if (isset($album['coverPhotoBaseUrl'])): ?>
                                        <img src="<?php echo $album['coverPhotoBaseUrl']; ?>=w100-h100-c" class="w-12 h-12 rounded-lg object-cover" alt="">
                                    <?php endif; ?>
                                    <span class="font-medium text-slate-800"><?php echo htmlspecialchars($album['title']); ?></span>
                                    <span class="text-sm text-slate-400">(<?php echo $album['mediaItemsCount'] ?? 0; ?> foto's)</span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="w-full mt-6 bg-slate-900 text-white py-3 px-4 rounded-xl font-medium hover:bg-slate-800 transition-all shadow-lg active:scale-[0.98]">
                        Geselecteerd album activeren
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <footer class="mt-10 text-center">
            <a href="google-auth.php" class="text-sm text-slate-400 hover:text-blue-600 underline">Opnieuw verbinden met Google</a>
        </footer>
    </div>

</body>
</html>