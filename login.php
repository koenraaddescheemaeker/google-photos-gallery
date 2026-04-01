<?php
/** * FORCEKES - login.php (Fase 18: Entry Portal) */
require_once 'config.php';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Toegang | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(20px); border-radius: 3rem; padding: 4rem; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1.25rem; border-radius: 1.25rem; width: 100%; outline: none; text-align: center; font-size: 14px; }
        input:focus { border-color: #3b82f6; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <?php include 'bg-video.php'; ?>

    <div class="card max-w-md w-full text-center">
        <header class="mb-12">
            <h1 style="font-family:'Playfair Display', serif;" class="text-4xl italic mb-4">Identificatie</h1>
            <?php if($error == 'not_approved'): ?>
                <p class="text-blue-500 text-[10px] font-black uppercase tracking-widest">Uw account wacht op goedkeuring door de beheerder.</p>
            <?php elseif($error): ?>
                <p class="text-red-500 text-[10px] font-black uppercase tracking-widest">Onjuiste gegevens.</p>
            <?php endif; ?>
        </header>

        <form action="auth-handler.php" method="POST" class="space-y-6">
            <input type="email" name="email" placeholder="E-MAILADRES" required>
            <input type="password" name="password" placeholder="WACHTWOORD" required>
            <button type="submit" class="w-full py-5 bg-white text-black rounded-2xl font-black uppercase text-[10px] tracking-[0.3em] hover:bg-blue-600 hover:text-white transition">
                Betreed het Portaal
            </button>
        </form>

        <footer class="mt-12 space-y-4">
            <a href="register.php" class="block text-[9px] font-black uppercase tracking-widest text-zinc-500 hover:text-white">Nog geen account? Registreer hier</a>
            <a href="forgot-password.php" class="block text-[9px] font-black uppercase tracking-widest text-zinc-700 hover:text-white italic">Wachtwoord vergeten?</a>
        </footer>
    </div>
</body>
</html>