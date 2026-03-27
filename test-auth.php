<?php
/** * FORCEKES - test-auth.php (Verbeterde Diagnose)
 */
require_once 'config.php';

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:50px;line-height:1.6;'>";
echo "<h2>🔍 Diagnostische Verbindingstest</h2>";

// Check of variabelen gevuld zijn
echo "SUPABASE_URL: " . ($supabaseUrl ? "✅ Gevonden" : "❌ NIET GEVONDEN") . "<br>";
echo "SUPABASE_KEY: " . ($supabaseKey ? "✅ Gevonden" : "❌ NIET GEVONDEN") . "<br><br>";

if (!$supabaseUrl || !$supabaseKey) {
    echo "<div style='color:#f87171;'>STOP: PHP ziet de omgevingsvariabelen niet. Controleer of ze in Coolify als 'Build Variable' OF 'Service Variable' staan.</div>";
    exit;
}

$url = rtrim($supabaseUrl, '/') . "/auth/v1/settings";
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
curl_close($ch);

if ($status === 200) {
    echo "<div style='color:#4ade80; font-weight:bold;'>✅ SUCCES! Status 200. De PHP-app kan praten met Supabase Auth.</div>";
    echo "<pre style='background:#111;padding:10px;margin-top:10px;'>Settings: " . htmlspecialchars($res) . "</pre>";
} else {
    echo "<div style='color:#f87171; font-weight:bold;'>❌ FOUT: Status $status.</div>";
    if ($error) echo "CURL ERROR: $error<br>";
    echo "Respons van server: " . htmlspecialchars($res);
}
echo "</body>";