<?php
require_once 'config.php';

// Haal alle pagina's en hun gekoppelde albums op met een join
// We gebruiken de REST API van Supabase
$menuData = supabaseRequest('familie_paginas?select=*,pagina_albums(google_albums(titel,google_url))');
?>

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex items-center space-x-8">
                <a href="index.php" class="flex-shrink-0 flex items-center">
                    <span class="text-2xl font-black text-indigo-600 tracking-tighter">FAMILIE.</span>
                </a>

                <div class="hidden md:flex space-x-4 h-full">
                    <?php foreach ($menuData as $pagina): ?>
                        <div class="relative group h-full flex items-center">
                            <button class="text-gray-700 group-hover:text-indigo-600 px-3 py-2 text-sm font-medium transition flex items-center">
                                <?= htmlspecialchars($pagina['naam']) ?>
                                <svg class="ml-1 w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div class="absolute top-full left-0 w-64 bg-white shadow-xl rounded-b-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 translate-y-2 group-hover:translate-y-0">
                                <div class="p-4">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Albums</p>
                                    <div class="space-y-1">
                                        <?php if (empty($pagina['pagina_albums'])): ?>
                                            <p class="text-gray-400 text-xs italic">Nog geen albums.</p>
                                        <?php else: ?>
                                            <?php foreach ($pagina['pagina_albums'] as $link): ?>
                                                <?php if (isset($link['google_albums'])): ?>
                                                    <a href="index.php?album=<?= urlencode($link['google_albums']['google_url']) ?>" 
                                                       class="block px-3 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition">
                                                        <?= htmlspecialchars($link['google_albums']['titel']) ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <a href="zwaaikamer.php" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium self-center">
                        Zwaaikamer 🎥
                    </a>
                </div>
            </div>

            <div class="flex items-center">
                <a href="admin.php" class="p-2 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </a>
            </div>
        </div>
    </div>
</nav>