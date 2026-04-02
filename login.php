<?php
require_once 'config.php';
if(!empty($_SESSION['user_email'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>TOEGANG | Forcekes</title><script src="https://cdn.tailwindcss.com"></script><style>@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap'); body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }</style></head>
<body class="flex items-center justify-center min-h-screen bg-black">
<div class="max-w-md w-full p-12 bg-white/5 rounded-[3rem] border border-white/10 text-center">
<h1 style="font-family:'Playfair Display', serif;" class="text-4xl italic mb-10">Identificatie</h1>
<form action="auth-handler.php" method="POST" class="space-y-6">
<input type="email" name="email" placeholder="E-MAILADRES" class="w-full p-4 bg-white/5 border border-white/10 rounded-2xl text-center outline-none focus:border-blue-500" required>
<input type="password" name="password" placeholder="WACHTWOORD" class="w-full p-4 bg-white/5 border border-white/10 rounded-2xl text-center outline-none focus:border-blue-500" required>
<button type="submit" class="w-full py-5 bg-white text-black rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 hover:text-white transition">Betreed het Portaal</button>
</form>
</div>
</body>
</html>