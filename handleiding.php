<?php
/** * FORCEKES - handleiding.php (Gebruikersgids - Gekeurd door Manu) */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handleiding | Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .step-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 3rem; transition: all 0.5s; }
        .step-card:hover { border-color: #3b82f6; background: rgba(59, 130, 246, 0.05); }
        .step-num { font-size: 80px; font-weight: 900; color: rgba(59, 130, 246, 0.1); position: absolute; top: 1rem; right: 2rem; z-index: 0; }
        .instruction-tag { background: #3b82f6; color: #fff; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.2em; padding: 0.5rem 1rem; border-radius: 0.5rem; display: inline-block; margin-bottom: 1.5rem; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-5xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-24 text-center">
            <h1 class="serif-italic text-5xl md:text-7xl italic mb-6">De Wegwijzer</h1>
            <p class="text-zinc-500 text-[10px] uppercase tracking-[0.5em]">Hoe u het Forcekes Portaal meester wordt</p>
        </header>

        <div class="space-y-16">
            
            <section class="step-card relative overflow-hidden">
                <span class="step-num">01</span>
                <div class="relative z-10">
                    <span class="instruction-tag">Stap 1: Toegang krijgen</span>
                    <h2 class="text-3xl font-bold mb-6 italic">Registreren & Inloggen</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10 text-zinc-400 text-sm leading-relaxed">
                        <div>
                            <p class="mb-4">Heeft u nog geen account? Klik op <strong class="text-white">Toegang</strong> en daarna op <strong class="text-white italic">Registreer hier</strong>.</p>
                            <p>Vul uw <strong class="text-blue-500">Roepnaam</strong> in (zoals de familie u noemt), uw e-mail en een wachtwoord.</p>
                        </div>
                        <div class="bg-black/40 p-6 rounded-2xl border border-white/5 italic">
                            <p class="text-blue-500 font-bold mb-2 uppercase text-[10px] tracking-widest">De Wachtkamer</p>
                            Na registratie moet de beheerder (Koen) uw account goedkeuren. Dit is een veiligheidsmaatregel voor onze familie-privacy. U krijgt bericht zodra de deur openstaat.
                        </div>
                    </div>
                </div>
            </section>

            <section class="step-card relative overflow-hidden">
                <span class="step-num">02</span>
                <div class="relative z-10">
                    <span class="instruction-tag">Stap 2: De Site Verkennen</span>
                    <h2 class="text-3xl font-bold mb-6 italic">Rondkijken</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <ul class="space-y-4 text-sm text-zinc-400">
                            <li><strong class="text-white uppercase tracking-widest text-[11px]">Home:</strong> Het centrale overzicht van alle recente albums.</li>
                            <li><strong class="text-white uppercase tracking-widest text-[11px]">Bezoek:</strong> Klik hier om de persoonlijke collectie van een specifiek familielid te bekijken.</li>
                            <li><strong class="text-white uppercase tracking-widest text-[11px]">Verkenner:</strong> Een snelle zijbalk om direct naar een specifiek jaartal of evenement te springen.</li>
                            <li><strong class="text-white uppercase tracking-widest text-[11px]">Zwaaikamer:</strong> Onze privé-videochat voor live familie-momenten.</li>
                        </ul>
                        <div class="flex items-center justify-center border border-white/5 rounded-3xl bg-black/20 p-8">
                            <p class="text-[10px] text-zinc-600 uppercase tracking-[0.3em] text-center">Op uw telefoon vindt u deze opties achter de <br><strong class="text-white">drie lijntjes (hamburger)</strong> rechtsboven.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="step-card relative overflow-hidden">
                <span class="step-num">03</span>
                <div class="relative z-10">
                    <span class="instruction-tag">Stap 3: Uw Eigen Cockpit</span>
                    <h2 class="text-3xl font-bold mb-6 italic">Albums Toevoegen</h2>
                    <div class="space-y-8 text-zinc-400 text-sm">
                        <p>Klik op <strong class="text-white">Mijn Profiel</strong> om naar uw persoonlijke cockpit te gaan. Hier gebeurt de magie:</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h3 class="text-white font-bold uppercase text-[10px] tracking-widest">Hoe maak ik een album?</h3>
                                <ol class="list-decimal list-inside space-y-3">
                                    <li>Maak een album in <strong class="text-blue-500">Google Photos</strong>.</li>
                                    <li>Klik op 'Delen' en maak een <strong class="text-blue-500">Gedeelde Link</strong> aan.</li>
                                    <li>Kopieer deze link (bijv. https://photos.app.goo.gl/...).</li>
                                    <li>Plak de link in uw cockpit bij 'Nieuw Album Toevoegen'.</li>
                                    <li>Geef het album een duidelijke naam (bijv: <span class="italic">Trouwfeest-2024</span>).</li>
                                </ol>
                            </div>
                            <div class="bg-blue-600/10 p-8 rounded-3xl border border-blue-600/20">
                                <p class="text-blue-400 font-black uppercase text-[10px] tracking-widest mb-4 italic">Het Grote Voordeel</p>
                                <p class="text-xs leading-relaxed">Zodra u de link opslaat, haalt onze "Media-Uil" de foto's automatisch op en zet ze vlijmscherp in ons eigen archief. U hoeft zelf niets te uploaden!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="text-center py-10">
                <p class="serif-italic text-2xl text-zinc-500 italic mb-8">Vragen? De beheerder staat altijd paraat.</p>
                <a href="index.php" class="px-10 py-4 bg-white text-black rounded-full font-black uppercase text-[10px] tracking-[0.3em] hover:bg-blue-600 hover:text-white transition">Terug naar Home</a>
            </footer>

        </div>
    </main>
</body>
</html>