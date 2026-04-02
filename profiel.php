<?php
/** * FORCEKES - profiel.php (Fase 23: Member Categorisatie) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_album'])) {
    $slug = strtolower(trim($_POST['slug']));
    $link = trim($_POST['google_link']);
    $parent = strtolower(trim($_POST['parent_category']));
    
    supabaseRequest("album_settings", "POST", [
        "slug" => $slug, "google_link" => $link, "created_by" => $userEmail, 
        "parent_category" => $parent, "priority" => 999, "is_visible" => true
    ], "upsert=true");
    $status = "Album '$slug' is klaargezet onder '$parent'.";
}

$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0] ?? null;
$myAlbums = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $userEmail]);
$myAlbums = is_array($myAlbums) ? $myAlbums : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profiel | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1.25rem; font-size: 13px; outline: none; width: 100%; }
        .btn-blue { background: #3b82f6; color: #fff; font-weight: 900; text-transform: uppercase; font-size: 10px; padding: 1rem; border-radius: 1.25rem; width: 100%; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-16">
            <h1 style="font-family:'Playfair Display', serif;" class="text-5xl italic">Mijn Cockpit</h1>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <section class="card lg:col-span-2">
                <h2 class="text-blue-500 font-black text-[10px] uppercase tracking-widest mb-8">Nieuw Album</h2>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="slug" placeholder="NAAM (bijv: kerst-2025)" required>
                        <select name="parent_category" required>
                            <option value="familie">Onder 'Familie'</option>
                            <option value="feesten">Onder 'Feesten'</option>
                            <option value="museum">Onder 'Museum'</option>
                        </option>
                        <input type="url" name="google_link" placeholder="GOOGLE PHOTOS LINK" class="md:col-span-2" required>
                    </div>
                    <button type="submit" name="add_album" class="btn-blue">Album Toevoegen</button>
                </form>
                <?php if($status): ?> <p class="mt-4 text-blue-500 text-[10px] font-bold uppercase"><?= $status ?></p> <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>