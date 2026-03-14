<?php
require_once 'config.php';

// Verwerk formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['naam'])) {
    $naam = $_POST['naam'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $naam)));
    
    supabaseRequest('familie_paginas', 'POST', [
        'naam' => $naam,
        'slug' => $slug
    ]);
    echo "<p class='bg-green-500 p-2 text-white'>Pagina voor $naam aangemaakt!</p>";
}

// Haal bestaande pagina's op
$paginas = supabaseRequest('familie_paginas?select=*');
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin - Familie Portaal</title>
</head>
<?php include 'menu.php'; ?>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
        <h1 class="text-2xl font-bold mb-6">Beheer Familie Pagina's</h1>
        
        <form method="POST" class="mb-8 flex gap-2">
            <input type="text" name="naam" placeholder="Naam (bijv. Jan)" class="border p-2 flex-grow rounded" required>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">Toevoegen</button>
        </form>

        <h2 class="font-bold mb-4">Bestaande Pagina's:</h2>
        <ul>
            <?php foreach ($paginas as $p): ?>
                <li class="border-b py-2 flex justify-between">
                    <span><?= htmlspecialchars($p['naam']) ?></span>
                    <span class="text-gray-400 text-sm">/<?= $p['slug'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>