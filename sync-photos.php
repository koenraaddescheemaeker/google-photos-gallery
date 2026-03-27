<?php
/** FORCEKES - sync-photos.php (The Hybrid Worker) */
require_once 'config.php';

header('Content-Type: text/plain');
echo "🚀 SYNC PROTOCOL GEACTIVEERD\n";
echo "---------------------------\n";

// 1. Haal het actieve album ID op uit settings
$settings = supabaseRequest('settings?key=eq.active_google_album_id', 'GET');
$albumId = $settings[0]['value'] ?? null;

if (!$albumId || $albumId == 'vul_hier_je_album_id_in') {
    die("❌ FOUT: Geen actief Album ID geconfigureerd in Supabase settings.\n");
}

// 2. Haal een geldig token op
$token = getValidAccessToken();
if (!$token) {
    die("❌ FOUT: Kan geen verbinding maken met Google (Token defect).\n");
}

echo "📡 Verbinding met Google zoeken voor album: $albumId...\n";

// 3. Haal foto's op bij Google
$ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['albumId' => $albumId, 'pageSize' => 100]),
    CURLOPT_SSL_VERIFYPEER => false
]);

$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($res, true);
curl_close($ch);

if ($httpCode !== 200) {
    echo "⚠️ Google weigert nog steeds (Status $httpCode). We gebruiken de huidige cache.\n";
    die("Einde protocol.\n");
}

if (isset($data['mediaItems'])) {
    echo "📦 " . count($data['mediaItems']) . " foto's gevonden. Magazijn bijwerken...\n";

    // Oude cache wissen (optioneel, of update on conflict)
    supabaseRequest('album_photos', 'DELETE');

    foreach ($data['mediaItems'] as $item) {
        $photoData = [
            'google_id'     => $item['id'],
            'image_url'     => $item['baseUrl'] . "=w2048-h1024",
            'thumbnail_url' => $item['baseUrl'] . "=w300-h300",
            'metadata'      => json_encode($item['mediaMetadata'] ?? [])
        ];
        supabaseRequest('album_photos', 'POST', $photoData);
    }
    echo "✅ SYNC VOLTOOID. Supabase is up-to-date.\n";
} else {
    echo "Empty album of fout in data.\n";
}