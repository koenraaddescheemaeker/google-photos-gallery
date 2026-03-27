<?php
/** FORCEKES - sync-photos.php (Multi-Page Edition) */
require_once 'config.php';

echo "🚀 MULTI-PAGE SYNC STARTING...\n";

// 1. Haal alle actieve pagina-configuraties op
$pages = supabaseRequest('page_configs', 'GET');

foreach ($pages as $page) {
    $slug = $page['page_slug'];
    $albumId = $page['google_album_id'];
    
    echo "📂 Syncing $slug ($albumId)...\n";

    $token = getValidAccessToken();
    // API aanroep (zoals we eerder deden)...
    // [Hier komt de curl aanroep naar Google Photos]

    if (isset($data['mediaItems'])) {
        // Wis alleen de foto's van DEZE categorie
        supabaseRequest("album_photos?category=eq.$slug", 'DELETE');

        foreach ($data['mediaItems'] as $item) {
            supabaseRequest('album_photos', 'POST', [
                'google_id'     => $item['id'],
                'image_url'     => $item['baseUrl'] . "=w2048-h1024",
                'thumbnail_url' => $item['baseUrl'] . "=w300-h300",
                'category'      => $slug // HIER koppelen we de foto aan de pagina
            ]);
        }
    }
}
echo "✅ ALL PAGES SYNCED.\n";