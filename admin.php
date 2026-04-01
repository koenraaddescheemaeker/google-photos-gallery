<?php
/** * FORCEKES - admin.php (Fase 15: Album Steering - Gekeurd door Manu) */
require_once 'config.php';

// Auth check
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$status = "";

// ACTION HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Album Toevoegen of Volgorde Wijzigen
    if (isset($_POST['save_album'])) {
        $slug = strtolower(trim($_POST['slug']));
        $prio = (int)$_POST['priority'];
        
        // We slaan de instelling op in de Master-tabel
        supabaseRequest("album_settings", "POST", [
            "slug" => $slug,
            "priority" => $prio,
            "is_visible" => true
        ], "upsert=true");
        
        $status = "Album '$slug' staat nu op positie $prio.";
    }

    // 2. Leden toevoegen
    if (isset($_POST['add_member'])) {
        $email = strtolower(trim($_POST['email']));
        $role = $_POST['role'];
        supabaseRequest("members", "POST", ["email" => $email, "role" => $role]);
        $status = "Lid toegevoegd: $email";
    }
}

// Data ophalen via de nieuwe RPC
$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');
$members = supabaseRequest("members?order=role.asc", 'GET');

if (is_array($albums)) {
    usort($albums, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Admin | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2rem; }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0.75rem 1rem; border-radius: 0.75rem; font-size: 13px; outline: none; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        
        <header class="mb-12 flex justify-between items-center">
            <h1 style="font-family:'Playfair Display', serif;" class="text-4xl italic">Beheer</h1>
            <?php if($status): ?> <p class="text-blue-500 text-[10px] font-black uppercase tracking-widest"><?= $status ?></p> <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <section class="card lg:col-span-2">
                <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em] mb-8">Albums</h2>
                
                <form method="POST" class="flex flex-wrap gap-4 mb-10 p-6 bg-white/5 rounded-2xl">
                    <input type="text" name="slug" placeholder="ALBUM NAAM" required class="flex-1">
                    <input type="number" name="priority" placeholder="POSITIE" required class="w-24">
                    <button type="submit" name="save_album" class="bg-white text-black px-6 py-2 rounded-xl text-[10px] font-black uppercase">Opslaan</button>
                </form>

                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-zinc-600 text-[10px] uppercase border-b border-white/5">
                            <th class="pb-4">Naam</th>
                            <th class="pb-4 text-center">Fotos</th>
                            <th class="pb-4 text-center">Prio</th>
                            <th class="pb-4 text-right">Actie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($albums as $a): ?>
                        <tr>
                            <td class="py-4 font-bold"><?= ucfirst($a['category_name']) ?></td>
                            <td class="py-4 text-center text-zinc-500"><?= $a['photo_count'] ?></td>
                            <td class="py-4 text-center font-black text-blue-500"><?= $a['priority'] ?></td>
                            <td class="py-4 text-right">
                                <button onclick="document.getElementsByName('slug')[0].value='<?= $a['category_name'] ?>'; document.getElementsByName('priority')[0].value='<?= $a['priority'] ?>';" class="text-[9px] font-black uppercase text-zinc-400 hover:text-white">Aanpassen</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="space-y-8">
                <div class="card">
                    <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em] mb-6">Synchronisatie</h2>
                    <a href="sync-media.php" class="block w-full text-center py-4 bg-white/5 border border-white/10 rounded-xl text-[10px] font-black uppercase hover:bg-white hover:text-black transition">Start Sync</a>
                </div>

                <div class="card">
                    <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em] mb-6">Nieuw Lid</h2>
                    <form method="POST" class="space-y-4">
                        <input type="email" name="email" placeholder="EMAIL" class="w-full" required>
                        <select name="role" class="w-full">
                            <option value="member">Familie</option>
                            <option value="pro">Pro (Payers)</option>
                            <option value="admin">Admin</option>
                        </select>
                        <button type="submit" name="add_member" class="w-full py-3 bg-blue-600 text-white rounded-xl text-[10px] font-black uppercase">Toevoegen</button>
                    </form>
                </div>
            </section>

        </div>
    </main>
</body>
</html>