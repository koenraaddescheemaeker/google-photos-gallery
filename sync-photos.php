<?php
/** FORCEKES - sync-photos.php (The Scraper Edition) */
require_once 'config.php';

header('Content-Type: text/plain');
echo "🕵️ GOOGLE PHOTOS SCRAPER STARTING...\n";

// 1. Haal de pagina-configs op
$pages = supabaseRequest('page_configs', 'GET');

foreach ($pages as $page) {
    $slug = $page['page_slug'];
    $sharedUrl = $page['google_album_id']; // Hier plak je nu de VOLLEDIGE gedeelde link

    if (!filter_var($sharedUrl, FILTER_VALIDATE_URL)) {
        echo "⚠️ Skipping $slug: Geen geldige URL gevonden.\n";
        continue;
    }

    echo "📂 Scrapen van '$slug' via link...\n";

    // 2. Haal de HTML op van de gedeelde link
    $ch = curl_init($sharedUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    // 3. De "Magic" Regex: We zoeken naar de patronen van Google's fotolinks
    // We zoeken naar URLs die beginnen met https://lh3.googleusercontent.com/pw/
    preg_match_all('/(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-0\-_]*)/', $html, $matches);

    if (!empty($matches[0])) {
        // Verwijder duplicaten (Google zet ze vaak meerdere keren in de broncode)
        $uniquePhotos = array_unique($matches[0]);
        
        echo "📦 " . count($uniquePhotos) . " foto's gevonden. Database bijwerken...\n";

        // Wis oude cache voor deze categorie
        supabaseRequest("album_photos?category=eq.$slug", 'DELETE');

        foreach ($uniquePhotos as $baseUrl) {
            // Google Foto's Scraper Truc:
            // Voeg =w2048-h1024 toe voor HD kwaliteit
            // Voeg =w400-h400 toe voor de thumbnail
            supabaseRequest('album_photos', 'POST', [
                'google_id'     => md5($baseUrl), // We maken een unieke hash als ID
                'image_url'     => $baseUrl . "=w2048-h1024",
                'thumbnail_url' => $baseUrl . "=w400-h400",
                'category'      => $slug
            ]);
        }
        echo "✅ Sync voor $slug voltooid.\n";
    } else {
        echo "❌ Geen foto's gevonden in de HTML. Is het album wel openbaar gedeeld?\n";
    }
}
echo "---------------------------\nDONE.";