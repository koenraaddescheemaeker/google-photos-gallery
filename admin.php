<?php
/** * FORCEKES - admin.php (Fase 18: Master Control - Gekeurd door Manu) */
require_once 'config.php';

// 1. STRIKTE AUTHENTICATIE (Alleen Koen heeft de sleutels)
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') {
    header("Location: index.php");
    exit;
}

$statusMsg = "";

// --- 2. ACTION HANDLERS (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Album Toevoegen of Bijwerken (Slug, Google Link, Prio)
    if (isset($_POST['upsert_album'])) {
        $slug = strtolower(trim($_POST['slug']));
        $googleLink = trim($_POST['google_link']);
        $priority = (int)$_POST['priority'];
        
        supabaseRequest("album_settings", "POST", [
            "slug" => $slug,
            "google_link" => $googleLink,
            "priority" => $priority,
            "is_visible" => true,
            "created_by" => $userEmail
        ], "upsert=true");
        
        $statusMsg = "Album '$slug' vlijmscherp opgeslagen.";
    }

    // B. Nieuw Lid Handmatig Toevoegen
    if (isset($_POST['add_member'])) {
        $email = strtolower(trim($_POST['email']));
        $nickname = trim($_POST['nickname']);
        $role = $_POST['role'] ?? 'member';
        
        supabaseRequest("members", "POST", [
            "email" => $email, 
            "nickname" => $nickname, 
            "role" => $role,
            "is_approved" => true // Handmatig toegevoegd = direct goedgekeurd
        ], "upsert=true");
        
        $statusMsg = "Lid '$nickname' toegevoegd en goedgekeurd.";
    }
}

// --- 3. ACTION HANDLERS (GET - Goedkeuringen & Verwijderen) ---
if (isset($_GET['approve'])) {
    $approveEmail = $_GET['approve'];
    supabaseRequest("members?email=eq.$approveEmail", "PATCH", ["is_approved" => true]);
    header("Location: admin.php?status=approved");
    exit;
}

if (isset($_GET['delete_member'])) {
    $deleteEmail = $_GET['delete_member'];
    if ($deleteEmail !== 'koen@lauwe.com') {
        supabaseRequest("members?email=eq.$deleteEmail", "DELETE");
    }
    header("Location: admin.php");
    exit;
}

// --- 4. DATA OPHALEN ---
$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');
$members = supabaseRequest("members?order=role.asc", 'GET');

// Splits leden in 'Wachtend' en 'Actief'
$pendingMembers = array_filter($members, fn($m) => ($m['is_approved'] ?? false) == false);
$activeMembers = array_filter($members, fn($m) => ($m['is_approved'] ?? false) == true);

if (is_array($albums)) {
    usort($albums, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Control | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 2.5rem; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1rem; font-size: 13px; outline: none; width: 100%; }
        input:focus { border-color: #3b82f6; background: rgba(255,255,255,0.08); }
        .btn-premium { background: #fff; color: #000; font-weight: 900; text-transform: uppercase; font-size: 11px; letter-spacing: 0.1em; padding: 1rem 2rem; border-radius: 1.25rem; transition: all 0.3s; cursor: pointer; }
        .btn-premium:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); }
        .label-blue { color: #3b82f6; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3em; margin-bottom: 1rem; display: block; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        
        <header class="mb-20 flex justify-between items-end">
            <div>
                <h1 class="serif-italic text-5xl md:text-6xl italic">Master Control</h1>
                <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em] mt-4">Beheer van het digitale familiearchief</p>
            </div>
            <?php if($statusMsg): ?> 
                <div class="bg-blue-600/20 text-blue-400 px-6 py-3 rounded-2xl border border-blue-600/20 text-[10px] font-black uppercase tracking-widest animate-pulse">
                    <?= $statusMsg ?>
                </div> 
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            
            <?php if (!empty($pendingMembers)): ?>
            <section class="card lg:col-span-3 border-blue-600/30 bg-blue-600/5">
                <span class="label-blue">Wachtkamer (Nieuwe Aanvragen)</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($pendingMembers as $pm): ?>
                        <div class="flex justify-between items-center bg-black/40 p-6 rounded-3xl border border-white/5">
                            <div>
                                <p class="font-bold text-white"><?= htmlspecialchars($pm['nickname'] ?? 'Naamloos') ?></p>
                                <p class="text-[10px] text-zinc-500 font-mono"><?= $pm['email'] ?></p>
                            </div>
                            <a href="admin.php?approve=<?= $pm['email'] ?>" class="btn-premium py-3 px-6">Goedkeuren</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <section class="card lg:col-span-2">
                <span class="label-blue">Album Toevoegen & Importeren</span>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[9px] text-zinc-500 mb-2 block uppercase font-bold tracking-widest">Google Album Link</label>
                            <input type="url" name="google_link" placeholder="https://photos.app.goo.gl/..." required>
                            <p class="text-[9px] text-zinc-700 mt-2 italic">Tip: Gebruik de 'Gedeelde Link' van Google Photos</p>
                        </div>
                        <div>
                            <label class="text-[9px] text-zinc-500 mb-2 block uppercase font-bold tracking-widest">Album Naam (Slug)</label>
                            <input type="text" name="slug" placeholder="bijv: kerstfeest-2025" required>
                        </div>
                    </div>
                    <div class="flex items-end gap-6">
                        <div class="w-32">
                            <label class="text-[9px] text-zinc-500 mb-2 block uppercase font-bold tracking-widest">Positie</label>
                            <input type="number" name="priority" value="999" required>
                        </div>
                        <button type="submit" name="upsert_album" class="btn-premium flex-1">Opslaan in Archief</button>
                    </div>
                </form>

                <hr class="my-12 border-white/5">

                <span class="label-blue">Huidige Albums</span>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php foreach ($albums as $a): ?>
                            <tr>
                                <td class="py-5">
                                    <span class="font-bold block"><?= ucfirst($a['category_name']) ?></span>
                                    <span class="text-[9px] text-zinc-600 uppercase font-black tracking-widest"><?= (int)$a['photo_count'] ?> Bestanden</span>
                                </td>
                                <td class="py-5 text-center">
                                    <span class="bg-blue-600/10 text-blue-500 px-4 py-1 rounded-full text-[10px] font-black"><?= $a['priority'] ?></span>
                                </td>
                                <td class="py-5 text-right">
                                    <button onclick="document.getElementsByName('slug')[0].value='<?= $a['category_name'] ?>'; document.getElementsByName('priority')[0].value='<?= $a['priority'] ?>';" class="text-[10px] font-black uppercase text-zinc-600 hover:text-white transition">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="space-y-10">
                <div class="card">
                    <span class="label-blue">Systeem</span>
                    <div class="space-y-4">
                        <a href="sync-media.php" class="block w-full text-center py-4 bg-zinc-900 border border-white/10 rounded-2xl text-[10px] font-black uppercase hover:bg-white hover:text-black transition">Start Media Sync</a>
                        <p class="text-[9px] text-zinc-700 text-center italic">Verplaatst foto's van Google naar Supabase</p>
                    </div>
                </div>

                <div class="card">
                    <span class="label-blue">Lid Toevoegen</span>
                    <form method="POST" class="space-y-4">
                        <input type="text" name="nickname" placeholder="ROEPNAAM" required>
                        <input type="email" name="email" placeholder="E-MAILADRES" required>
                        <select name="role">
                            <option value="member">Familie (Member)</option>
                            <option value="pro">Pro (Payers)</option>
                            <option value="admin">Admin</option>
                        </select>
                        <button type="submit" name="add_member" class="btn-premium w-full mt-2">Toevoegen</button>
                    </form>
                </div>
            </section>

            <section class="card lg:col-span-3">
                <span class="label-blue">Geautoriseerde Leden (Bezoek-Lijst)</span>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($activeMembers as $m): ?>
                        <div class="bg-white/5 p-6 rounded-3xl border border-white/5 flex justify-between items-center group">
                            <div>
                                <p class="text-sm font-bold"><?= htmlspecialchars($m['nickname'] ?? 'Lid') ?></p>
                                <p class="text-[9px] text-zinc-600 font-mono"><?= $m['email'] ?></p>
                                <span class="text-[8px] uppercase tracking-widest text-blue-500/50 mt-2 block"><?= $m['role'] ?></span>
                            </div>
                            <?php if ($m['email'] !== 'koen@lauwe.com'): ?>
                                <a href="admin.php?delete_member=<?= $m['email'] ?>" onclick="return confirm('Lid definitief verwijderen?')" class="opacity-0 group-hover:opacity-100 text-red-900 hover:text-red-500 transition">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </div>
    </main>
</body>
</html>