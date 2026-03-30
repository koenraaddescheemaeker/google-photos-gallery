<?php
/** * FORCEKES - zwaaikamer.php (Fase 7: Chat & Meeting Edition) */
require_once 'config.php';

// De uil controleert of er al een naam is opgegeven via de URL of Sessie
$userDisplayName = isset($_POST['display_name']) ? htmlspecialchars($_POST['display_name']) : '';
$isJoined = !empty($userDisplayName);

$title = "DE ZWAAIKAMER";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        
        .glass-input { 
            background: rgba(255, 255, 255, 0.05); 
            border: 2px solid rgba(255, 255, 255, 0.1); 
            transition: all 0.3s ease;
        }
        .glass-input:focus { 
            border-color: #3b82f6; 
            background: rgba(255, 255, 255, 0.1); 
            outline: none; 
        }
        .live-dot { animation: blink 1.5s infinite; }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
    </style>
</head>
<body class="bg-black flex flex-col min-h-screen">
    <?php include 'menu.php'; ?>

    <main class="flex-grow flex items-center justify-center px-6 pt-20">

        <?php if (!$isJoined): ?>
            <div class="max-w-md w-full text-center space-y-10 animate-fade-in">
                <header>
                    <h1 class="text-3xl md:text-5xl font-black italic uppercase tracking-tighter mb-4">
                        Deelnemen aan de<br><span class="text-blue-600 text-4xl md:text-6xl">Vergadering</span>
                    </h1>
                    <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.4em]">Zwaaikamer</p>
                </header>

                <form method="POST" class="space-y-6">
                    <div class="relative">
                        <input 
                            type="text" 
                            name="display_name" 
                            required 
                            placeholder="Vul je naam in..." 
                            value="" 
                            class="glass-input w-full rounded-2xl px-6 py-5 text-center text-lg font-bold text-white placeholder:text-zinc-700"
                        >
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase tracking-widest py-5 rounded-2xl transition-all transform hover:scale-[1.02] active:scale-95 shadow-xl shadow-blue-600/20 flex items-center justify-center gap-3">
                        Deelnemen aan de vergadering
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </form>

                <div class="pt-10 flex flex-col items-center gap-4">
                    <div class="flex items-center gap-4 px-6 py-3 glass-input rounded-full">
                        <div class="w-8 h-8 flex items-center justify-center text-zinc-400">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                        </div>
                        <div class="w-8 h-8 flex items-center justify-center text-zinc-400">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                        </div>
                        <div class="w-px h-4 bg-zinc-800"></div>
                        <div class="flex items-center gap-2">
                            <span class="live-dot h-2 w-2 rounded-full bg-green-500"></span>
                            <span class="text-[9px] font-black uppercase text-zinc-400">Microfoon OK</span>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="w-full h-[70vh] glass-input rounded-[3rem] overflow-hidden relative group">
                <div class="absolute inset-0 flex items-center justify-center bg-zinc-950">
                    <div class="text-center">
                        <div class="live-dot w-20 h-20 border-4 border-blue-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="#3b82f6"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                        <h2 class="text-xl font-black italic uppercase tracking-tighter italic">Verbinding maken...</h2>
                        <p class="text-[10px] font-black uppercase text-zinc-500 mt-2 tracking-widest">Ingelogd als: <?= $userDisplayName ?></p>
                    </div>
                </div>

                <div class="absolute top-8 left-8 flex items-center gap-3 glass-input px-4 py-2 rounded-full">
                    <span class="live-dot h-2 w-2 rounded-full bg-red-600"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-red-500">Live</span>
                </div>
                
                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-4 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    <button class="w-14 h-14 bg-zinc-900 rounded-full flex items-center justify-center hover:bg-zinc-800 transition"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/></svg></button>
                    <button class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center hover:bg-red-500 transition shadow-xl shadow-red-600/30" onclick="window.location.reload()">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.69 1 1 0 0 0 1.15-.09l1.81-1.81a1.13 1.13 0 0 1 1.45-.11l1.52.75a1.13 1.13 0 0 1 .64 1.21l-.45 3.32a1.13 1.13 0 0 1-1.12.98 15.93 15.93 0 0 1-14-14 1.13 1.13 0 0 1 .98-1.12l3.32-.45a1.13 1.13 0 0 1 1.21.64l.75 1.52a1.13 1.13 0 0 1-.11 1.45l-1.81 1.81a1 1 0 0 0-.09 1.15 16 16 0 0 0 2.69 3.41z"/></svg>
                    </button>
                    <button class="w-14 h-14 bg-zinc-900 rounded-full flex items-center justify-center hover:bg-zinc-800 transition"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg></button>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <footer class="py-10 text-center">
        <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-700 hover:text-white transition">Verlaat de Zwaaikamer</a>
    </footer>

</body>
</html>