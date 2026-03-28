<?php
/** * FORCEKES - login.php (Premium Dark Edition) */
require_once 'config.php';

// Als er al een sessie is, stuur ze direct door naar het museum
if (isset($_COOKIE['sb-access-token'])) {
    header("Location: index.php");
    exit;
}

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Inloggen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md px-8 py-12 glass rounded-[3rem] shadow-2xl">
        <header class="text-center mb-10">
            <h1 class="text-2xl font-black italic uppercase tracking-tighter mb-2">
                FORCEKES<span class="text-blue-600">PORTAAL</span>
            </h1>
            <p class="text-zinc-500 text-xs font-bold uppercase tracking-widest">Toegang voor familie</p>
        </header>

        <?php if ($error === 'invalid_credentials'): ?>
            <div class="mb-6 p-4 bg-red-900/20 border border-red-900/50 rounded-2xl text-red-500 text-xs font-bold text-center">
                E-mail of wachtwoord onjuist. Probeer het opnieuw.
            </div>
        <?php endif; ?>

        <form action="auth-handler.php?action=login" method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 mb-2 ml-4">E-mailadres</label>
                <input type="email" name="email" required 
                       class="w-full bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-blue-600 transition">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 mb-2 ml-4">Wachtwoord</label>
                <input type="password" name="password" required 
                       class="w-full bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm focus:outline-none focus:border-blue-600 transition">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase text-[10px] tracking-[0.2em] py-5 rounded-2xl transition-all shadow-lg shadow-blue-600/20">
                Inloggen
            </button>
        </form>

        <footer class="mt-12 text-center">
            <p class="text-[10px] text-zinc-600 font-bold uppercase tracking-widest leading-loose">
                Geen toegang? <br> Vraag het even aan de beheerder.
            </p>
        </footer>
    </div>

</body>
</html>