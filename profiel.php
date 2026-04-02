<?php
/** * FORCEKES - profiel.php (Member Cockpit) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

// Verwerking Account Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $updates = [
        "nickname" => $_POST['nickname'],
        "email" => $_POST['email']
    ];
    if (!empty($_POST['password'])) {
        // Hier zou normaal de Supabase Auth password update komen
    }
    supabaseRequest("members?email=eq.$userEmail", "PATCH", $updates);
    $_SESSION['user_email'] = $_POST['email']; // Update session if email changed
}

// Verwerking Album Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_album'])) {
    $slug = $_POST['original_slug'];
    supabaseRequest("album_settings?slug=eq.$slug", "PATCH", [
        "priority" => (int)$_POST['priority'],
        "is_visible" => $_POST['visibility'] === 'true'
    ]);
}

$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0];
$myAlbums = supabaseRequest("album_settings?created_by=eq.$userEmail", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Mijn Cockpit | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0.8rem; border-radius: 1rem; width: 100%; font-size: 13px; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-6xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-16">
            <h1 class="serif-italic text-6xl italic mb-4">Mijn Cockpit</h1>
            <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em]">Beheer uw identiteit en momenten</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <section class="card h-fit">
                <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-widest mb-8">Account</h2>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Roepnaam</label>
                        <input type="text" name="nickname" value="<?= $member['nickname'] ?>">
                    </div>
                    <div>
                        <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Email</label>
                        <input type="email" name="email" value="<?= $member['email'] ?>">
                    </div>
                    <div>
                        <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Nieuw Wachtwoord</label>
                        <input type="password" name="password" placeholder="Leeg laten om te behouden">
                    </div>
                    <button type="submit" name="update_account" class="w-full py-4 bg-white text-black rounded-xl font-black uppercase text-[10px]">Gegevens Opslaan</button>
                </form>
            </section>

            <section class="lg:col-span-2 card">
                <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-widest mb-8">Mijn Momenten</h2>
                <div class="space-y-6">
                    <?php foreach($myAlbums as $a): ?>
                        <form method="POST" class="bg-white/5 p-6 rounded-2xl flex flex-wrap gap-6 items-end border border-white/5">
                            <input type="hidden" name="original_slug" value="<?= $a['slug'] ?>">
                            <div class="flex-1 min-w-[200px]">
                                <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Album Naam</label>
                                <p class="text-lg font-bold"><?= ucfirst($a['slug']) ?></p>
                            </div>
                            <div class="w-24">
                                <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Volgorde</label>
                                <input type="number" name="priority" value="<?= $a['priority'] ?>">
                            </div>
                            <div class="w-32">
                                <label class="text-[9px] text-zinc-500 uppercase font-bold mb-2 block">Status</label>
                                <select name="visibility">
                                    <option value="true" <?= $a['is_visible'] ? 'selected' : '' ?>>Zichtbaar</option>
                                    <option value="false" <?= !$a['is_visible'] ? 'selected' : '' ?>>Verborgen</option>
                                </select>
                            </div>
                            <button type="submit" name="update_album" class="bg-zinc-800 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase hover:bg-blue-600 transition">Update</button>
                        </form>
                    <?php endforeach; ?>
                    <?php if(empty($myAlbums)): ?>
                        <p class="text-zinc-600 italic">U heeft nog geen eigen feesten toegevoegd.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</body>
</html>