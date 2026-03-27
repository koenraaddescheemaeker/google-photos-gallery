<?php
/** FORCEKES - admin.php (Secure Admin) */
require_once 'config.php';

// Strikte controle op jouw e-mail
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'koen@lauwe.com') {
    header("Location: index.php?view=login&msg=Geen toegang.");
    exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $slug = $_POST['page_slug'] ?? null;
    $albumUrl = trim($_POST['google_album_id'] ?? '');
    if ($slug && !empty($albumUrl)) {
        supabaseRequest("page_configs?page_slug=eq.$slug", 'PATCH', ['google_album_id' => $albumUrl]);
        $msg = "Opgeslagen!";
    }
}

$pages = supabaseRequest('page_configs?select=*&order=id.asc', 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Beheer | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12">
    <?php include 'menu.php'; ?>
    <div class="max-w-4xl mx-auto mt-20">
        <h1 class="text-4xl font-black italic uppercase mb-8">Admin <span class="text-blue-600">Beheer</span></h1>
        <?php if($msg): ?>
            <div class="mb-6 p-4 bg-green-500/10 text-green-500 rounded-xl text-xs font-bold"><?= $msg ?></div>
        <?php endif; ?>
        
        <div class="space-y-6">
            <?php if(is_array($pages)) foreach ($pages as $p): ?>
                <form method="POST" class="bg-zinc-900 p-8 rounded-[2rem] border border-white/5 flex flex-col md:flex-row gap-6 items-end">
                    <input type="hidden" name="page_slug" value="<?= htmlspecialchars($p['page_slug']) ?>">
                    <div class="flex-grow w-full">
                        <label class="block text-[10px] font-black uppercase text-zinc-500 mb-2"><?= htmlspecialchars($p['display_name']) ?></label>
                        <input type="text" name="google_album_id" value="<?= htmlspecialchars($p['google_album_id'] ?? '') ?>" class="w-full bg-black border border-white/10 rounded-xl px-4 py-3 text-sm">
                    </div>
                    <button type="submit" name="save_config" class="bg-blue-600 px-8 py-3 rounded-xl text-xs font-black uppercase">Save</button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>