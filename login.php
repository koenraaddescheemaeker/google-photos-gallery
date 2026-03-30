<?php
/** * FORCEKES - login.php (Fase 11: Ritual of Entry + Error Handling) */
require_once 'config.php';

// Check of er een foutmelding moet worden getoond
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Toegang | Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        .video-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.2; filter: blur(10px) grayscale(100%); z-index: -1; }
        .ritual-card { background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(40px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        input { background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; text-align: center; }
        input:focus { border-color: #3b82f6 !important; background: rgba(255,255,255,0.1) !important; }
        .shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
        @keyframes shake { 10%, 90% { transform: translate3d(-1px, 0, 0); } 20%, 80% { transform: translate3d(2px, 0, 0); } 30%, 50%, 70% { transform: translate3d(-4px, 0, 0); } 40%, 60% { transform: translate3d(4px, 0, 0); } }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    
    <video autoplay muted loop playsinline class="video-bg">
        <source src="https://www.w3schools.com/howto/rain.mp4" type="video/mp4">
    </video>

    <div class="ritual-card max-w-md w-full p-12 md:p-16 rounded-[3.5rem] md:rounded-[4rem] text-center shadow-2xl <?= $error ? 'shake' : '' ?>">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter mb-4">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-xl text-zinc-400">Identificeer uzelf</p>
        </header>

        <?php if ($error === 'invalid'): ?>
            <p class="text-red-500 text-[10px] font-black uppercase tracking-widest mb-8">Onjuiste gegevens. Probeer het opnieuw.</p>
        <?php endif; ?>

        <form action="auth-handler.php" method="POST" class="space-y-8">
            <input type="email" name="email" required placeholder="E-MAILADRES" class="w-full py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none transition-all">
            <input type="password" name="password" required placeholder="WACHTWOORD" class="w-full py-5 rounded-2xl text-[10px] font-black uppercase tracking-widest outline-none transition-all">
            <button type="submit" class="w-full py-6 bg-white text-black rounded-2xl text-[10px] font-black uppercase tracking-[0.4em] hover:bg-blue-600 hover:text-white transition-all shadow-xl shadow-white/5">
                Betreed het Archief
            </button>
        </form>

        <footer class="mt-12">
            <a href="index.php" class="text-[8px] font-black uppercase tracking-widest text-zinc-600 hover:text-white transition">Keer terug naar het portaal</a>
        </footer>
    </div>
</body>
</html>