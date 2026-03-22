<?php
/**
 * FORCEKES ADMIN - Standalone Edition (Bypasses config.php issues)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- CONFIGURATIE ---
$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$supabaseUrl        = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey        = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));
$masterScope        = 'https://www.googleapis.com/auth/photoslibrary';

// --- HELPER FUNCTIES ---
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    global $supabaseUrl, $supabaseKey;
    $url = $supabaseUrl . "/rest/v1/" . $endpoint;
    $ch = curl_init($url);
    $headers = [
        "apikey: $supabaseKey",
        "Authorization: Bearer $supabaseKey",
        "Content-Type: application/json",
        "Prefer: return=representation"
    ];
    if ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

function getValidToken() {
    global $googleClientID, $googleClientSecret, $masterScope;
    $tokens = supabaseRequest('google_tokens?select=*&id=eq.1');
    if (!$tokens || isset($tokens['error']) || empty($tokens[0])) return false;
    
    $row = $tokens[0];
    if (!empty($row['access_token']) && $row['access_token'] !== 'leeg' && strtotime($row['expires_at']) > (time() + 300)) {
        return $row['access_token'];
    }

    if (empty($row['refresh_token'])) return false;

    $ch = curl_init("https://oauth2.googleapis.com/token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $googleClientID,
        'client_secret' => $googleClientSecret,
        'refresh_token' => $row['refresh_token'],
        'grant_type' => 'refresh_token',
        'scope' => $masterScope
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (isset($res['access_token'])) {
        $newExpiry = date('Y-m-d H:i:sO', time() + $res['expires_in']);
        supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
            'access_token' => $res['access_token'],
            'expires_at' => $newExpiry
        ]);
        return $res['access_token'];
    }
    return false;
}

// --- LOGICA ---
$token = getValidToken();

if (!$token) {
    die("<body style='background:#000;color:#fff;padding:50px;'><h1>Sleutel-fout</h1><p>Check je refresh_token in Supabase (ID 1).</p></body>");
}

$ch = curl_init("https://photoslibrary.googleapis.com/v1/sharedAlbums?pageSize=50");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
$sharedAlbums = $res['sharedAlbums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Shared Albums</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; }
    </style>
</head>
<body class="text-zinc-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-5xl font-black italic mb-12 uppercase tracking-tighter text-blue-500">FORCEKES ADMIN</h1>
        
        <?php if (!empty($sharedAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($sharedAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all">
                        <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                        <div class="p-6">
                            <h3 class="font-bold text-xl mb-1"><?= htmlspecialchars($album['title'] ?? 'Naamloos') ?></h3>
                            <p class="text-zinc-500 text-sm mb-4 italic"><?= $album['mediaItemsCount'] ?? 0 ?> foto's</p>
                            <button class="w-full py-3 bg-zinc-800 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-blue-600 transition-colors">Selecteer</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-20 border border-dashed border-zinc-800 rounded-[3rem] text-center">
                <p class="text-zinc-500 italic text-xl">Nog geen gedeelde albums gevonden.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>