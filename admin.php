<?php
/**
 * FORCEKES - admin.php
 * Master Console voor beheer van Museum en Joris albums.
 * De functies worden nu centraal vanuit config.php geladen.
 */

require_once 'config.php';

// 1. STRIKTE TOEGANGSCONTROLE
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';

if ($currentUser !== 'koen@lauwe.com') {
    header("Location: index.php?auth_error=not_authorized");
    exit;
}

$statusMessage = "";
$statusType = "info";

// 2. ACTIE: NIEUWE FOTO/VIDEO TOEVOEGEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_photo') {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $category = $_POST['category'];
    
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $payload = [
            'id' => bin2hex(random_bytes(12)),
            'image_url' => $url,
            'category' => $category,
            'captured_at' => date('c'),
            'mime_type' => (strpos($url, '.mp4') !== false || strpos($url, '.webm') !== false) ? 'video/mp4' : 'image/jpeg'
        ];
        
        $res = supabaseRequest("album_photos", "POST", $payload);
        
        if (isset($res['error'])) {
            $statusMessage = "Fout bij opslaan.";
            $statusType = "error";
        } else {
            $statusMessage = "Item succesvol toegevoegd aan " . ucfirst($category);
        }
    }
}

// 3. ACTIE: ITEM VERWIJDEREN
if (isset($_GET['delete'])) {
    $id = htmlspecialchars($_GET['delete']);
    supabaseRequest("album_photos?id=eq.$id", "DELETE");
    $statusMessage = "Item definitief verwijderd.";
}

// 4. DATA OPHALEN
$museumData = supabaseRequest("album_photos?category=eq.museum&select=id", 'GET');
$jorisData = supabaseRequest("album_photos?category=eq.joris&select=id", 'GET');
$recentItems = supabaseRequest("album_photos?select=*&order=captured_at.desc&limit=8", 'GET');

$countM = is_array($museumData) ? count($museumData) : 0;
$countJ = is_array($jorisData) ? count($jorisData) : 0;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Beheerpaneel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-32">
        <header class="mb-16 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Ingelogd als: <?= $currentUser ?></p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest border <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-10">
                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Nieuwe Media</h3>
                    <form action="admin.php" method="POST" class="space-y-8">
                        <input type="hidden" name="action" value="add_photo">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-4">Media URL</label>
                            <input type="url" name="url" required class="w-full bg-zinc-900 border border-white/5 rounded-3xl px-8 py-5 text-sm outline-none focus:border-blue-600 transition">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <select name="category" class="w-full bg-zinc-900 border border-white/5 rounded-3xl px-8 py-5 text-sm outline-none focus:border-blue-600 transition">
                                <option value="museum">Het Museum</option>
                                <option value="joris">Joris</option>
                            </select>
                            <button type="submit" class="w-full bg-blue-600 py-5 rounded-3xl font-black uppercase text-[11px] tracking-widest hover:bg-blue-500 transition shadow-2xl shadow-blue-600/20">Opslaan</button>
                        </div>
                    </form>
                </section>

                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Laatste Wijzigingen</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <?php if (is_array($recentItems)): foreach ($recentItems as $item): ?>
                            <div class="group flex items-center justify-between p-5 bg-zinc-900/30 rounded-3xl border border-white/5">
                                <div class="flex items-center space-x-6">
                                    <img src="<?= $item['image_url'] ?>" class="w-16 h-16 rounded-2xl object-cover">
                                    <div><p class="text-[10px] font-black uppercase text-blue-500"><?= $item['category'] ?></p></div>
                                </div>
                                <a href="admin.php?delete=<?= $item['id'] ?>" class="bg-zinc-800 hover:bg-red-600 px-5 py-2 rounded-full text-[9px] font-black uppercase tracking-widest">Wis</a>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-10">
                <div class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500 mb-10">Status</h3>
                    <div class="space-y-10">
                        <div><span class="block text-[10px] font-black uppercase text-zinc-600 mb-1">Museum</span><span class="text-6xl font-black text-white"><?= $countM ?></span></div>
                        <div><span class="block text-[10px] font-black uppercase text-zinc-600 mb-1">Joris</span><span class="text-6xl font-black text-blue-600"><?= $countJ ?></span></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>