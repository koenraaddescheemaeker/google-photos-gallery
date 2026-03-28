<?php
/**
 * FORCEKES - admin.php
 * Master Console voor beheer van Museum en Joris albums.
 * Bevat sessie-beveiliging, handmatige invoer en live database-statistieken.
 */

require_once 'config.php';

// 1. STRIKTE TOEGANGSCONTROLE
// We controleren of de gebruiker is ingelogd en of het e-mailadres exact matcht.
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'koen@lauwe.com') {
    // Indien geen toegang, stuur terug naar index met een reden voor debuggen
    header("Location: index.php?auth_error=not_authorized");
    exit;
}

$statusMessage = "";
$statusType = "info"; // 'info' (blauw) of 'error' (rood)

// 2. ACTIE: NIEUWE FOTO/VIDEO TOEVOEGEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_photo') {
    $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    $category = $_POST['category']; // 'museum' of 'joris'
    
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $payload = [
            'id' => bin2hex(random_bytes(12)), // Genereer een unieke ID
            'image_url' => $url,
            'category' => $category,
            'captured_at' => date('c'), // ISO 8601 formaat voor Postgres
            'mime_type' => (strpos($url, '.mp4') !== false || strpos($url, '.webm') !== false) ? 'video/mp4' : 'image/jpeg'
        ];
        
        // We gebruiken de Service Key (indien nodig) voor schrijfrechten
        $res = supabaseRequest("album_photos", "POST", $payload);
        
        if (isset($res['error'])) {
            $statusMessage = "Fout bij opslaan: " . ($res['message'] ?? 'Onbekende fout');
            $statusType = "error";
        } else {
            $statusMessage = "Item succesvol toegevoegd aan " . ucfirst($category);
            $statusType = "info";
        }
    } else {
        $statusMessage = "Ongeldige URL opgegeven.";
        $statusType = "error";
    }
}

// 3. ACTIE: ITEM VERWIJDEREN
if (isset($_GET['delete'])) {
    $id = htmlspecialchars($_GET['delete']);
    $res = supabaseRequest("album_photos?id=eq.$id", "DELETE");
    
    if (isset($res['error'])) {
        $statusMessage = "Fout bij verwijderen.";
        $statusType = "error";
    } else {
        $statusMessage = "Item definitief verwijderd.";
        $statusType = "info";
    }
}

// 4. DATA OPHALEN VOOR HET DASHBOARD
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
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
        input, select { background: #0a0a0a !important; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-32">
        
        <header class="mb-16 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3 flex items-center">
                    <span class="w-2 h-2 bg-blue-600 rounded-full mr-3 animate-pulse"></span>
                    Systeem actief: <?= $_SESSION['user_email'] ?>
                </p>
            </div>

            <?php if ($statusMessage): ?>
                <div class="px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest border transition-all 
                    <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
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
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-4">Media URL (Directe link)</label>
                            <input type="url" name="url" required placeholder="https://voorbeeld.nl/foto.jpg" 
                                   class="w-full border border-white/5 rounded-3xl px-8 py-5 text-sm focus:border-blue-600 outline-none transition duration-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-4">Bestemming</label>
                                <select name="category" class="w-full border border-white/5 rounded-3xl px-8 py-5 text-sm outline-none appearance-none cursor-pointer focus:border-blue-600 transition">
                                    <option value="museum">Het Museum</option>
                                    <option value="joris">Joris</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-5 rounded-3xl font-black uppercase text-[11px] tracking-[0.2em] transition-all transform hover:scale-[1.02] active:scale-95 shadow-2xl shadow-blue-600/20">
                                    Opslaan in Portaal
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Laatste Wijzigingen</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <?php if (is_array($recentItems) && !empty($recentItems)): foreach ($recentItems as $item): ?>
                            <div class="group flex items-center justify-between p-5 bg-zinc-900/30 rounded-3xl border border-white/5 hover:border-white/10 transition">
                                <div class="flex items-center space-x-6">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden border border-white/10">
                                        <img src="<?= $item['image_url'] ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase text-blue-500 tracking-widest"><?= $item['category'] ?></p>
                                        <p class="text-[9px] text-zinc-600 font-bold mt-1"><?= date('j F Y | H:i', strtotime($item['captured_at'])) ?></p>
                                    </div>
                                </div>
                                <a href="admin.php?delete=<?= $item['id'] ?>" onclick="return confirm('Zeker weten?')" 
                                   class="opacity-0 group-hover:opacity-100 transition-opacity bg-zinc-800 hover:bg-red-600 px-5 py-2 rounded-full text-[9px] font-black uppercase tracking-widest">
                                    Wis
                                </a>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-zinc-600 text-xs italic p-10 text-center">Nog geen media gevonden in de database.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-10">
                <div class="p-10 glass rounded-[3.5rem] border-blue-600/10">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500 mb-10">Database Status</h3>
                    <div class="space-y-10">
                        <div class="relative">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-zinc-600 mb-1">Museum</span>
                            <div class="flex justify-between items-end">
                                <span class="text-6xl font-black tracking-tighter text-white"><?= $countM ?></span>
                                <span class="text-zinc-700 text-[10px] font-bold pb-2 uppercase">Bestanden</span>
                            </div>
                            <div class="w-full h-1 bg-zinc-900 mt-4 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600" style="width: <?= min(100, ($countM/500)*100) ?>%"></div>
                            </div>
                        </div>

                        <div class="relative">
                            <span class="block text-[10px] font-black uppercase tracking-widest text-zinc-600 mb-1">Joris</span>
                            <div class="flex justify-between items-end">
                                <span class="text-6xl font-black tracking-tighter text-blue-600"><?= $countJ ?></span>
                                <span class="text-zinc-700 text-[10px] font-bold pb-2 uppercase">Bestanden</span>
                            </div>
                            <div class="w-full h-1 bg-zinc-900 mt-4 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-600/50" style="width: <?= min(100, ($countJ/500)*100) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-10 glass rounded-[3.5rem] bg-blue-600/5">
                    <p class="text-[10px] font-bold text-zinc-400 leading-loose uppercase tracking-widest">
                        Tip: Gebruik voor video's directe links eindigend op .mp4 of .webm. De galerij herkent deze automatisch.
                    </p>
                </div>
            </div>

        </div>
    </main>
</body>
</html>