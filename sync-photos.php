<?php
/** FORCEKES - sync-photos.php (Multi-Page Engine) */
require_once 'config.php';

header('Content-Type: text/plain');
echo "🚀 MULTI-PAGE SYNC STARTING...\n";

// 1. Haal alle pagina-configs op uit Supabase
$pages = supabaseRequest('page_configs', 'GET');

if (!$pages) die("❌ Geen pagina-configs gevonden.");

$token = getValidAccessToken();
if (!$token) die("❌ Token defect.");

foreach ($pages as $page) {
    $slug = $page['page_slug'];
    $albumId = $page['google_album_id'];
    
    if (!$albumId || $albumId == 'ID_VAN_ALBUM') {
        echo "⚠️ Skipping $slug: Geen geldig Album ID geconfigureerd.\n";
        continue;
    }

    echo "📂 Syncing '$slug' (Album: $albumId)...\n";

    $ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['albumId' => $albumId, 'pageSize' => 50]),
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res['mediaItems'])) {
        // Wis oude foto's van deze specifieke categorie
        supabaseRequest("album_photos?category=eq.$slug", 'DELETE');

        foreach ($res['mediaItems'] as $item) {
            supabaseRequest('album_photos', 'POST', [
                'google_id'     => $item['id'],
                'image_url'     => $item['baseUrl'] . "=w2048-h1024",
                'thumbnail_url' => $item['baseUrl'] . "=w400-h400",
                'category'      => $slug
            ]);
        }
        echo "✅ " . count($res['mediaItems']) . " foto's gesynchroniseerd voor $slug.\n";
    } else {
        echo "❌ Fout bij ophalen $slug (Check of ID klopt).\n";
    }
}
echo "DONE.\n";