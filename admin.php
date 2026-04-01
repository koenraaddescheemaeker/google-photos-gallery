<?php
/** * FORCEKES - admin.php (Master Control - Gekeurd door Manu) */
require_once 'config.php';

// Forceer Admin toegang
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') {
    header("Location: index.php");
    exit;
}

// Data ophalen
$members = supabaseRequest("members?order=role.asc", 'GET');
$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Actie-handler (voor rollen aanpassen of albums toevoegen)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_member'])) {
        $newEmail = strtolower(trim($_POST['email']));
        supabaseRequest("members", "POST", ["email" => $newEmail, "role" => "member"]);
        header("Location: admin.php");
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Master Control | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .admin-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; }
        .btn-premium { background: #fff; color: #000; font-weight: 900; text-transform: uppercase; font-size: 10px; letter-spacing: 0.2em; padding: 1rem 2rem; border-radius: 1rem; transition: all 0.3s; }
        .btn-premium:hover { background: #3b82f6; color: #fff; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        
        <header class="mb-20">
            <h1 style="font-family:'Playfair Display', serif;" class="text-5xl italic mb-4">Master Control</h1>
            <p class="text-zinc-500 text-sm uppercase tracking-widest">Systeembeheer & Familie-autoriteit</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <section class="admin-card space-y-8">
                <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em]">Systeem</h2>
                <div class="flex flex-col gap-4">
                    <a href="sync-media.php" class="btn-premium text-center">Start Synchronisatie</a>
                    <a href="#" class="btn-premium text-center opacity-50 cursor-not-allowed">Backup Archief</a>
                </div>
                <p class="text-[9px] text-zinc-600 leading-relaxed italic">
                    Synchronisatie jaagt op Google-links en haalt ze naar de eigen kluis.
                </p>
            </section>

            <section class="admin-card lg:col-span-2 space-y-8">
                <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em]">Album Overzicht</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="text-zinc-600 text-[10px] uppercase tracking-widest border-b border-white/5">
                                <th class="pb-4 font-normal">Naam</th>
                                <th class="pb-4 font-normal">Items</th>
                                <th class="pb-4 font-normal">Prioriteit</th>
                                <th class="pb-4 font-normal">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($albums as $album): ?>
                                <tr>
                                    <td class="py-4 font-bold"><?= ucfirst($album['category_name']) ?></td>
                                    <td class="py-4 text-zinc-500"><?= $album['photo_count'] ?></td>
                                    <td class="py-4"><?= $album['priority'] ?></td>
                                    <td class="py-4">
                                        <span class="px-3 py-1 bg-green-500/10 text-green-500 text-[8px] font-black uppercase rounded-full">Zichtbaar</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-card lg:col-span-3 space-y-10">
                <div class="flex justify-between items-center">
                    <h2 class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em]">Leden & Levels</h2>
                    <form method="POST" class="flex gap-4">
                        <input type="email" name="email" placeholder="EMAILADRES" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-[10px] w-64 outline-none focus:border-blue-600">
                        <button type="submit" name="add_member" class="btn-premium py-2">Toevoegen</button>
                    </form>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($members as $member): ?>
                        <div class="bg-white/5 p-6 rounded-2xl border border-white/5 flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold"><?= $member['email'] ?></p>
                                <p class="text-[8px] uppercase tracking-widest text-zinc-500 mt-1"><?= $member['role'] ?></p>
                            </div>
                            <div class="flex gap-2">
                                <button class="text-[8px] font-black uppercase text-zinc-600 hover:text-white transition">Edit</button>
                                <button class="text-[8px] font-black uppercase text-red-900 hover:text-red-500 transition">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>
</body>
</html>