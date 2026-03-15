<?php
require_once 'config.php';

$accessToken = getValidAccessToken();

// 1. Haal Albums op bij Google (als we verbonden zijn)
$googleAlbums = [];
if ($accessToken) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $googleAlbums = $res['albums'] ?? [];
}

// 2. Verwerk acties
if (isset($_POST['link_album'])) {
    supabaseRequest('google_albums', 'POST', [
        'titel' => $_POST['album_titel'],
        'google_url' => $_POST['album_id'] // We slaan het ID op in de URL kolom
    ]);
    // Hierna kun je de koppeling maken in de pagina_albums tabel
}

$paginas = supabaseRequest('familie_paginas?select=*&order=naam');
$savedAlbums = supabaseRequest('google_albums?select=*&order=created_at.desc');
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin - Familie Portaal</title>
</head>
<body class="bg-gray-50 pb-20">
    <?php include 'menu.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-black mb-8">Beheerpaneel</h1>

        <?php if (!$accessToken): ?>
            <div class="bg-amber-50 border border-amber-200 p-6 rounded-2xl mb-8">
                <h2 class="text-amber-800 font-bold mb-2">Google verbinding vereist</h2>
                <p class="text-amber-700 mb-4">Je moet eerst inloggen om je albums te kunnen zien.</p>
                <a href="google-auth.php" class="inline-block bg-amber-600 text-white px-6 py-2 rounded-xl font-bold">Koppel Google Photos</a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">📥</span> Importeer uit Google
                </h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="link_album" value="1">
                    <select name="album_id" class="w-full border p-3 rounded-xl bg-gray-50 outline-none focus:ring-2 focus:ring-blue-500" onchange="document.getElementById('titel_input').value = this.options[this.selectedIndex].text">
                        <option value="">Selecteer een album...</option>
                        <?php foreach ($googleAlbums as $ga): ?>
                            <option value="<?= $ga['id'] ?>"><?= htmlspecialchars($ga['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="album_titel" id="titel_input">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">Voeg toe aan bibliotheek</button>
                </form>
            </div>

            </div>
    </div>
</body>
</html>