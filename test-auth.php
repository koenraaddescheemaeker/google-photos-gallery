<?php
/** * FORCEKES - test-auth.php 
 */
require_once 'config.php';

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:50px;line-height:1.6;'>";
echo "<h2 style='color:#3b82f6;'>🔍 Verbindingstest Forcekes Portaal</h2>";

// Debug: laat zien wat PHP ziet (zonder de hele sleutel te tonen voor veiligheid)
echo "Gevonden URL: " . ($supabaseUrl ?: "<span style='color:red;'>NIET GEVONDEN</span>") . "<br>";
echo "Gevonden Key: " . ($supabaseKey ? "✅ Aanwezig (begint met " . substr($supabaseKey, 0, 10) . "...)" : "<span style='color:red;'>NIET GEVONDEN</span>") . "<br><br>";

if (!$supabaseUrl || !$supabaseKey) {
    echo "<div style='background:#450a0a;padding:20px;border-radius:10px;border:1px solid #ef4444;'>";
    echo "<b>HOUDT OP:</b> De variabelen staan in de Supabase-stack, maar niet in je Web-stack.<br><br>";
    echo "<b>Oplossing:</b> Ga in Coolify naar je <u>Web/PHP service</u> > Environment Variables en voeg daar toe:<br>";
    echo "<code>NEXT_PUBLIC_SUPABASE_URL</code> en <code>SERVICE_SUPABASEANON_KEY</code>";
    echo "</div>";
    exit;
}

$url = rtrim($supabaseUrl, '/') . "/auth/v1/settings";
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["apikey: $supabaseKey"],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 10
]);

$res = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status === 200) {
    echo "<div style='color:#4ade80; font-weight:bold;'>✅ SUCCES! Status 200. De brug is geslagen.</div>";
} else {
    echo "<div style='color:#f87171;'>❌ FOUT: Status $status.</div>";
    echo "Check of de URL <code>$supabaseUrl</code> bereikbaar is vanaf deze container.";
}
echo "</body>";