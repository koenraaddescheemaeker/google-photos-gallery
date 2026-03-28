<?php
/** * FORCEKES - admin.php (Master Console) */
require_once 'config.php';

// Harde check: Alleen Koen mag hier zijn
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'koen@lauwe.com') {
    header("Location: index.php");
    exit;
}

$statusMessage = "";

// ACTIE: Foto Toevoegen
if (isset($_POST['action']) && $_POST['action'] === 'add_photo') {
    $payload = [
        'id' => bin2hex(random_bytes(8)), // Unieke ID genereren
        'image_url' => $_POST['url'],
        'category' => $_POST['category'],
        'captured_at' => date('c'),
        'mime_type' => 'image/jpeg'
    ];
    
    $res = supabaseRequest("album_photos", "POST", $payload);
    $statusMessage = "Item succesvol toegevoegd aan " . ucfirst($_POST['category']);
}

// ACTIE: Foto Verwijderen
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    supabaseRequest("album_photos?id=eq.$id", "DELETE");
    $statusMessage = "Item verwijderd uit de database.";
}

// Data ophalen voor het overzicht
$museumCount = supabaseRequest("album_photos?category=eq.museum&select=id", 'GET');
$jorisCount = supabaseRequest("album_photos?category=eq.joris&select=id", 'GET');
$recentItems = supabaseRequest("album_photos?select=*&order=captured_at.desc&limit=5", 'GET');

$countM = is_array($museumCount) ? count($museumCount) : 0;
$countJ = is_array($jorisCount) ? count($jorisCount) : 0;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-32">
        <header class="mb-12 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.2em] mt-2">Systeemstatus & Content</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-3 bg-blue-600/20 border border-blue-600/50 rounded-2xl text-blue-500 text-[10px] font-black uppercase tracking-widest animate-pulse">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-8">
                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-lg font-bold mb-8 flex items-center">
                        <span class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-4 text-sm">+</span>
                        Nieuwe Media Toevoegen
                    </h3>
                    
                    <form action="admin.php" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="add_photo">
                        
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-3 ml-4">Afbeelding of Video URL</label>
                            <input type="url" name="url" required placeholder="https://..." 
                                   class="w-full bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm focus:border-blue-600 outline-none transition">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-3 ml-4">Categorie</label>
                                <select name="category" class="w-full bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm outline-none">
                                    <option value="museum">Het Museum</option>
                                    <option value="joris">Joris</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest transition">
                                    Opslaan in Portaal
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-lg font-bold mb-8">Recent Toegevoegd</h3>
                    <div class="space-y-4">
                        <?php if (is_array($recentItems)): foreach ($recentItems as $item): ?>
                            <div class="flex items-center justify-between p-4 bg-zinc-900/50 rounded-2xl border border-white/5">
                                <div class="flex items-center space-x-4">
                                    <img src="<?= $item['image_url'] ?>" class="w-12 h-12 object-cover rounded-xl border border-white/10">
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-white tracking-widest"><?= $item['category'] ?></p>
                                        <p class="text-[9px] text-zinc-600"><?= date('d-m-Y H:i', strtotime($item['captured_at'])) ?></p>
                                    </div>
                                </div>
                                <a href="admin.php?delete=<?= $item['id'] ?>" class="text-zinc-700 hover:text-red-500 transition px-4 py-2 text-[10px] font-black uppercase">Verwijder</a>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-8">
                <div class="p-10 glass rounded-[3rem]">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 mb-6">Database Inhoud</h3>
                    <div class="space-y-6">
                        <div class="flex justify-between items-end border-b border-white/5 pb-4">
                            <span class="text-sm font-bold">Museum</span>
                            <span class="text-3xl font-black text-blue-600"><?= $countM ?></span>
                        </div>
                        <div class="flex justify-between items-end border-b border-white/5 pb-4">
                            <span class="text-sm font-bold">Joris</span>
                            <span class="text-3xl font-black text-blue-600"><?= $countJ ?></span>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-blue-600/10 rounded-[2.5rem] border border-blue-600/20">
                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-500 leading-loose">
                        Tip: Gebruik bij het toevoegen van foto's bij voorkeur direct links van je eigen server of een stabiele host voor de beste laadsnelheid.
                    </p>
                </div>
            </div>

        </div>
    </main>
</body>
</html>