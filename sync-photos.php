<?php
/** FORCEKES - sync-photos.php (Enhanced Scraper) */
require_once 'config.php';

header('Content-Type: text/plain');
echo "🕵️ STARTING ENHANCED SCRAPER...\n";

$pages = supabaseRequest('page_configs', 'GET');

foreach ($pages as $page) {
    $slug = $page['page_slug'];
    $url = $page['google_album_id'];

    if (!filter_var($url, FILTER_VALIDATE_URL)) continue;

    echo "📂 Processing $slug...\n";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // Volgt de photos.app.goo.gl redirect
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15
    ]);
    
    $html = curl_exec($ch);
    curl_close($ch);

    // Zoek naar de specifieke Google Usercontent patronen
    preg_match_all('/(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]*)/', $html, $matches);

    if (!empty($matches[0])) {
        $uniquePhotos = array_unique($matches[0]);
        echo "📦 Found " . count($uniquePhotos) . " items for $slug.\n";

        supabaseRequest("album_photos?category=eq.$slug", 'DELETE');

        foreach ($uniquePhotos as $baseUrl) {
            supabaseRequest('album_photos', 'POST', [
                'google_id'     => md5($baseUrl),
                'image_url'     => $baseUrl . "=w2048-h1024",
                'thumbnail_url' => $baseUrl . "=w400-h400",
                'category'      => $slug
            ]);
        }
    } else {
        echo "❌ No photos found for $slug. Check if the link is public.\n";
    }
}
echo "DONE.\n";