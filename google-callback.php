<?php
/**
 * FORCEKES - google-callback.php
 */
require_once 'config.php';

if (!isset($_GET['code'])) { die("Geen code ontvangen."); }

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => getenv('GOOGLE_CLIENT_ID'),
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
    'code'          => $_GET['code'],
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'https://new.forcekes.be/google-callback.php'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:40px;'>";
if (isset($res['access_token'])) {
    $expiresAt = date('Y-m-d H:i:sO', time() + $res['expires_in']);
    
    // Opslaan in Supabase (id=1)
    $updateData = ['access_token' => $res['access_token'], 'expires_at' => $expiresAt];
    if (isset($res['refresh_token'])) { $updateData['refresh_token'] = $res['refresh_token']; }
    supabaseRequest('google_tokens?id=eq.1', 'PATCH', $updateData);

    echo "<h1 style='color:#4ade80;'>✅ TOKEN ONTVANGEN</h1>";
    echo "<p>Kopieer deze token voor de debug-test:</p>";
    echo "<textarea readonly style='width:100%; height:100px; background:#000; color:#3b82f6; border:1px solid #444; padding:15px; border-radius:12px; font-family:monospace;'>" . $res['access_token'] . "</textarea>";
    echo "<br><br><a href='admin.php' style='display:inline-block; background:#3b82f6; color:white; padding:15px 30px; border-radius:12px; text-decoration:none; font-weight:bold;'>GA NAAR HET DASHBOARD</a>";
} else {
    echo "<h1 style='color:#ef4444;'>❌ FOUT BIJ GOOGLE</h1><pre>"; print_r($res); echo "</pre>";
}
echo "</body>";