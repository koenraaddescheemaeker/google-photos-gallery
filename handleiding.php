<?php
/** * FORCEKES - handleiding.php (Fase 22: Uitgebreide Gids - Gekeurd door Manu) */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gids | Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .step-card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 3.5rem; position: relative; overflow: hidden; }
        .instruction-tag { background: #3b82f6; color: #fff; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.2em; padding: 0.6rem 1.2rem; border-radius: 0.75rem; display: inline-block; margin-bottom: 2rem; }
        .step-num { font-size: 120px; font-weight: 900; color: rgba(59, 130, 246, 0.03); position: absolute; top: -1rem; right: 1rem; line-height: 1; }
        h2 { font-size: 2.5rem; line-height: 1.2; margin-bottom: 1.5rem; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-6xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="serif-italic text-6xl md:text-8xl italic mb-8">De Gids</h1>
            <p class="text-zinc-600 text-[11px] uppercase tracking-[0.5em]">Uw vlijmscherpe wegwijzer in het Forcekes Portaal</p>
        </header>

        <div class="space-y-20">
            
            <section class="step-card">
                <span class="step-num">01</span>
                <span class="instruction-tag">Identiteit</span>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
                    <div>
                        <h2>De poort <br>openen</h2>
                        <p class="text-zinc-400 leading-relaxed mb-6">Privacy is onze hoogste prioriteit. Daarom werkt het portaal alleen met persoonlijke accounts. Heeft u nog geen toegang?</p>
                        <ul class="space-y-4 text-sm">
                            <li class="flex gap-4"><span class="text-blue-500 font-black">1.</span> <span>Klik op <strong>Toegang</strong> en daarna op <strong>Registreer</strong>.</span></li>
                            <li class="flex gap-4"><span class="text-blue-500 font-black">2.</span> <span>Kies een <strong>Roepnaam</strong> (bijv. 'Opa' of 'Tante Els').</span></li>
                            <li class="flex gap-4"><span class="text-blue-500 font-black">3.</span> <span>Bevestig uw e-mailadres via de link in uw mailbox.</span></li>
                        </ul>
                    </div>
                    <div class="bg-blue-600/5 p-8 rounded-3xl border border-blue-600/10 italic text-sm text-blue-400">
                        <p class="font-black uppercase tracking-widest text-[10px] mb-4">De Wachtkamer</p>
                        "Zodra u zich registreert, krijgt beheerder Koen een seintje. Pas na zijn persoonlijke goedkeuring gaat de deur voor u open. Zo houden we vreemden buiten ons familiearchief."
                    </div>
                </div>
            </section>

            <section class="step-card">
                <span class="step-num">02</span>
                <span class="instruction-tag">Verkennen</span>
                <h2>Vlijmscherp <br>rondkijken</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                    <div class="p-8 bg-black/40 rounded-3xl border border-white/5">
                        <h3 class="font-bold text-white mb-3">Verkenner</h3>
                        <p class="text-xs text-zinc-500 leading-relaxed">De zijbalk aan de rechterkant. Ideaal om direct naar een specifiek jaar of album te springen zonder te scrollen.</p>
                    </div>
                    <div class="p-8 bg-black/40 rounded-3xl border border-white/5">
                        <h3 class="font-bold text-white mb-3">Bezoek</h3>
                        <p class="text-xs text-zinc-500 leading-relaxed">Wilt u enkel de foto's van een specifiek familielid zien? In het menu 'Bezoek' kiest u wiens 'kamer' u wilt binnengaan.</p>
                    </div>
                    <div class="p-8 bg-black/40 rounded-3xl border border-white/5">
                        <h3 class="font-bold text-white mb-3">Zwaaikamer</h3>
                        <p class="text-xs text-zinc-500 leading-relaxed">Onze eigen beveiligde video-ruimte. Klik hier voor live familie-momenten zonder tussenkomst van grote tech-bedrijven.</p>
                    </div>
                </div>
            </section>

            <section class="step-card">
                <span class="step-num">03</span>
                <span class="instruction-tag">Bijdragen</span>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                    <div>
                        <h2>Uw eigen <br>cockpit</h2>
                        <p class="text-zinc-400 leading-relaxed mb-8">U bent niet alleen een kijker, u bent een archivaris. Onder <strong>Mijn Profiel</strong> kunt u zelf herinneringen toevoegen via Google Photos.</p>
                        <div class="space-y-6">
                            <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-black shrink-0">1</div>
                                <p class="text-sm">Maak een album in Google Photos en kies <strong>Gedeelde Link</strong>.</p>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-black shrink-0">2</div>
                                <p class="text-sm">Plak deze link in uw cockpit onder 'Nieuw Album'.</p>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="w-10 h-10 rounded-full bg-white text-black flex items-center justify-center font-black shrink-0">3</div>
                                <p class="text-sm">Geef het album een naam (bijv. <em>Zomer in Menen</em>).</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-zinc-900 p-8 rounded-3xl border border-white/5">
                            <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-4">De Media-Uil aan het werk</p>
                            <p class="text-xs text-zinc-500 leading-relaxed">Nadat u de link heeft geplaatst, hoeft u niets meer te doen. Onze systemen halen de foto's automatisch op, verkleinen ze voor snelheid, en plaatsen ze vlijmscherp in uw persoonlijke 'Bezoek'-kamer.</p>
                        </div>
                        <div class="aspect-video bg-blue-600/10 rounded-3xl border border-blue-600/20 flex flex-col items-center justify-center p-8 text-center">
                            <p class="serif-italic text-lg italic mb-2">Video Instructie</p>
                            <p class="text-[9px] uppercase tracking-widest text-zinc-600">Hoe maak ik een gedeelde link?</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-20 border-t border-white/5">
                <h2 class="serif-italic italic text-4xl mb-12">Veelgestelde vragen</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <h4 class="font-bold text-blue-500 mb-2 uppercase text-[10px] tracking-widest">Waarom zie ik mijn foto's niet direct?</h4>
                        <p class="text-zinc-500 text-sm">Onze "Media Engine" werkt in batches. Het kan tot 15 minuten duren voordat alle foto's volledig verwerkt en zichtbaar zijn.</p>
                    </div>
                    <div>
                        <h4 class="font-bold text-blue-500 mb-2 uppercase text-[10px] tracking-widest">Is mijn data veilig?</h4>
                        <p class="text-zinc-500 text-sm">Ja. Uw foto's worden van Google gekopieerd naar onze eigen beveiligde kluis. Alleen ingelogde en goedgekeurde familieleden kunnen ze zien.</p>
                    </div>
                </div>
            </section>

        </div>
        
        <footer class="mt-32 text-center">
            <a href="index.php" class="px-12 py-5 bg-white text-black rounded-full font-black uppercase text-[11px] tracking-[0.4em] hover:bg-blue-600 hover:text-white transition-all shadow-2xl">Start de ervaring</a>
        </footer>
    </main>
</body>
</html>