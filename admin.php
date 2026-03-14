<?php
require_once 'config.php';

// 1. Verwerk nieuwe Pagina/Persoon
if (isset($_POST['add_pagina'])) {
    $naam = $_POST['naam'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $naam)));
    supabaseRequest('familie_paginas', 'POST', ['naam' => $naam, 'slug' => $slug]);
}

// 2. Verwerk nieuw Google Album
if (isset($_POST['add_album'])) {
    $titel = $_POST['titel'];
    $url = $_POST['google_url'];
    supabaseRequest('google_albums', 'POST', ['titel' => $titel, 'google_url' => $url]);
}

// 3. Koppel Album aan Pagina
if (isset($_POST['link_album'])) {
    supabaseRequest('pagina_albums', 'POST', [
        'pagina_id' => $_POST['pagina_id'],
        'album_id' => $_POST['album_id']
    ]);
}

// Haal alle data op voor de overzichten
$paginas = supabaseRequest('familie_paginas?select=*&order=naam');
$albums = supabaseRequest('google_albums?select=*&order=created_at.desc');
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin - Familie Portaal</title>
</head>
<body class="bg-gray-50 font-sans">
    <?php include 'menu.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-black mb-12 text-gray-900">Beheerpaneel</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3">👤</span> Personen
                </h2>
                <form method="POST" class="flex gap-2 mb-6">
                    <input type="hidden" name="add_pagina" value="1">
                    <input type="text" name="naam" placeholder="Naam (bijv. Jan)" class="flex-grow border border-gray-200 p-2 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl hover:bg-indigo-700 transition">Voeg toe</button>
                </form>
                <div class="space-y-2">
                    <?php foreach ($paginas as $p): ?>
                        <div class="p-3 bg-gray-50 rounded-lg text-sm font-medium text-gray-700"><?= htmlspecialchars($p['naam']) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-green-100 text-green-600 p-2 rounded-lg mr-3">📸</span> Nieuw Album
                </h2>
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="add_album" value="1">
                    <input type="text" name="titel" placeholder="Album Titel" class="w-full border border-gray-200 p-2 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                    <input type="url" name="google_url" placeholder="Google Photos Link" class="w-full border border-gray-200 p-2 rounded-xl focus:ring-2 focus:ring-green-500 outline-none" required>
                    <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-xl hover:bg-green-700 transition">Album Opslaan</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 md:col-span-2">
                <h2 class="text-xl font-bold mb-4 flex items-center">
                    <span class="bg-purple-100 text-purple-600 p-2 rounded-lg mr-3">🔗</span> Album toewijzen aan persoon
                </h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input type="hidden" name="link_album" value="1">
                    <select name="pagina_id" class="border border-gray-200 p-2 rounded-xl outline-none">
                        <?php foreach ($paginas as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['naam']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="album_id" class="border border-gray-200 p-2 rounded-xl outline-none">
                        <?php foreach ($albums as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['titel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-purple-600 text-white py-2 rounded-xl hover:bg-purple-700 transition">Koppeling Maken</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>