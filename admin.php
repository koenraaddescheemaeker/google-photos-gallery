<?php
require_once 'config.php';

echo "<body style='background:#111; color:#10b981; font-family:monospace; padding:40px; line-height:1.6;'>";
echo "<h1 style='color:#fff;'>🕵️ FORCEKES DIAGNOSTICS</h1>";
echo "<hr style='border-color:#333; margin-bottom:20px;'>";

// STAP 1: CHECK OMGEVINGSVARIABELEN
echo "<h2 style='color:#60a5fa;'>1. Environment Variables Check</h2>";
$clientId = getenv('GOOGLE_CLIENT_ID');
$supabaseUrl = getenv('NEXT_PUBLIC_SUPABASE_URL');
echo "GOOGLE_CLIENT_ID: " . ($clientId ? "✅ Gevonden (" . substr($clientId, 0, 15) . "...)" : "❌ ONTBREEKT!") . "<br>";
echo "SUPABASE_URL: " . ($supabaseUrl ? "✅ Gevonden" : "❌ ONTBREEKT!") . "<br><br>";

// STAP 2: DATABASE CHECK
echo "<h2 style='color:#60a5fa;'>2. Supabase Database Check</h2>";
$tokens = supabaseRequest('google_tokens?select=*&id=eq.1');

if (isset($tokens['error'])) {
    echo "<span style='color:#ef4444;'>❌ Fout bij lezen database! Controleer je Supabase Service Key.</span><br>";
    echo "<pre style='background:#222; padding:10px; color:#ef4444;'>" . print_r($tokens, true) . "</pre>";
    exit;
} elseif (empty($tokens)) {
    echo "<span style='color:#ef4444;'>❌ Database succesvol gelezen, maar er is geen rij met id=1. De INSERT is niet gelukt.</span><br>";
    exit;
}

echo "✅ Database bereikt! Rij met id=1 is gevonden.<br><br>";
$row = $tokens[0];

// STAP 3: GOOGLE REFRESH CHECK
echo "<h2 style='color:#60a5fa;'>3. Google Token Refresh Check</h2>";
echo "We proberen nu de refresh_token in te wisselen voor een verse access_token...<br><br>";

$ch = curl_init("https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => trim(getenv('GOOGLE_CLIENT_ID')),
    'client_secret' => trim(getenv('GOOGLE_CLIENT_SECRET')),
    'refresh_token' => $row['refresh_token'],
    'grant_type'    => 'refresh_token'
]));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resRaw = curl_exec($ch);
$res = json_decode($resRaw, true);

if (isset($res['access_token'])) {
    echo "<span style='color:#10b981; font-weight:bold; font-size:1.2em;'>🎉 SUCCES! Alles werkt perfect.</span><br>";
    echo "Google accepteerde de token. Je kunt deze debug-code nu verwijderen en de originele admin.php terugzetten.";
} else {
    echo "<span style='color:#ef4444; font-weight:bold; font-size:1.2em;'>❌ FOUT! Google weigert de refresh token.</span><br>";
    echo "<p>Dit is de letterlijke reactie van Google:</p>";
    echo "<pre style='background:#222; padding:15px; border-left:4px solid #ef4444; color:#fca5a5;'>" . htmlspecialchars($resRaw) . "</pre>";
    
    if (isset($res['error']) && ($res['error'] === 'invalid_client' || $res['error'] === 'invalid_grant')) {
        echo "<br><div style='background:#3b82f620; padding:15px; border:1px solid #3b82f6; border-radius:8px;'>";
        echo "<b>💡 CONCLUSIE VAN DE UIL:</b><br>";
        echo "Je hebt in de Google Playground waarschijnlijk vergeten op het <b>tandwieltje (rechtsboven)</b> te klikken om je eigen Client ID en Secret in te vullen <i>voordat</i> je op Authorize klikte.<br>";
        echo "Hierdoor is de huidige token gekoppeld aan de Playground, en niet aan Forcekes.";
        echo "</div>";
    }
}
echo "</body>";
?>