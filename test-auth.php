<?php
require_once 'config.php';
$url = rtrim($supabaseUrl, '/') . "/auth/v1/settings";

echo "<body style='background:#000;color:#fff;font-family:sans-serif;padding:50px;'>";
echo "<h2>🔍 Diagnostische Verbindingstest</h2>";
echo "Verbinding maken met: <code>$url</code><br><br>";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["apikey: $supabaseKey"],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);

$res = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

if ($status === 200) {
    echo "<div style='color:#4ade80;'>✅ STATUS 200: Supabase Auth is bereikbaar! De verbinding is in orde.</div>";
} else {
    echo "<div style='color:#f87171;'>❌ FOUT STATUS $status: De PHP-container kan Supabase niet bereiken.</div>";
    if ($error) echo "CURL ERROR: $error<br>";
    echo "<br><b>Oplossing:</b> Controleer of <code>SERVICE_URL_SUPABASEKONG</code> in je Coolify .env klopt.";
}
echo "</body>";