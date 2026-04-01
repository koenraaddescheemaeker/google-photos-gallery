<?php
/** * FORCEKES - register.php (Fase 18: Registratie-aanvraag) */
require_once 'config.php';
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Registreren | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 3rem; padding: 4rem; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1rem; width: 100%; outline: none; text-align: center; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <?php include 'bg-video.php'; ?>

    <div class="card max-w-md w-full text-center">
        <?php if($msg == 'success'): ?>
            <h1 style="font-family:'Playfair Display', serif;" class="text-3xl italic mb-6">Aanvraag verstuurd</h1>
            <p class="text-zinc-500 text-sm leading-relaxed mb-8">Bevestig eerst uw e-mail via de link in uw mailbox. Daarna zal de beheerder uw toegang goedkeuren.</p>
            <a href="login.php" class="text-[10px] font-black uppercase tracking-widest text-blue-500">Terug naar login</a>
        <?php else: ?>
            <header class="mb-12">
                <h1 style="font-family:'Playfair Display', serif;" class="text-3xl italic mb-4">Nieuw Lidmaatschap</h1>
                <p class="text-zinc-500 text-[10px] uppercase tracking-widest">Maak deel uit van het archief</p>
            </header>

            <form action="register-handler.php" method="POST" class="space-y-4">
                <input type="text" name="nickname" placeholder="ROEPNAAM (voor Bezoek pagina)" required>
                <input type="email" name="email" placeholder="E-MAILADRES" required>
                <input type="password" name="password" placeholder="KIES EEN WACHTWOORD" required>
                <button type="submit" class="w-full py-5 bg-white text-black rounded-2xl font-black uppercase text-[10px] tracking-[0.3em] hover:bg-blue-600 hover:text-white transition">
                    Aanvraag indienen
                </button>
            </form>
            <a href="login.php" class="block mt-8 text-[9px] font-black text-zinc-600 uppercase tracking-widest">Ik heb al een account</a>
        <?php endif; ?>
    </div>
</body>
</html>