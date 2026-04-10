<!DOCTYPE html>
<html lang="nl" class="bg-black text-white">
<head>
    <meta charset="UTF-8">
    <title>FORCEKES 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen overflow-hidden">
    <?php include 'navbar.php'; ?>
    <div class="text-center">
        <h1 class="text-[12vw] font-black italic tracking-tighter leading-none opacity-10 select-none absolute inset-0 flex items-center justify-center">FORCEKES</h1>
        <div class="relative z-10">
            <h2 class="text-4xl font-bold uppercase tracking-[0.3em] mb-4">De Kluis</h2>
            <?php 
            try {
                $count = $db->query("SELECT count(*) FROM album_settings")->fetchColumn();
                echo "<p class='text-amber-400 font-mono tracking-widest uppercase text-sm'>Verbinding vlijmscherp: $count albums ontsloten</p>";
            } catch(Exception $e) {
                echo "<p class='text-red-500 font-mono text-xs'>Lasknaat-fout: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
