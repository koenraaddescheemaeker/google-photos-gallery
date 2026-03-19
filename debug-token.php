<?php
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) die("Geen token gevonden. Log eerst in.");

// Vraag Google om de details van de sleutel
$url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token;
$info = json_decode(file_get_contents($url), true);

echo "<body style='background:#000; color:#fff; font-family:monospace; padding:20px;'>";
echo "<h1>🔍 Super-Röntgen Diagnose</h1>";

echo "<h3>1. Identiteit van de Sleutel</h3>";
$tokenClientId = $info['issued_to'] ?? 'ONBEK<?php
/**
 * super-debug.php - De Ultieme Röntgen Diagnose
 */
require_once 'config.php';

// Forceer foutrapportage voor dit script
error_reporting(E_ALL);
ini_set('display_errors', 1);

$token = getValidAccessToken();

echo "<body style='background:#020617; color:#f1f5f9; font-family:ui-monospace,monospace; padding:40px; line-height:1.6;'>";
echo "<h1 style='color:#3b82f6; border-bottom:1px solid #1e293b; padding-bottom:10px;'>🔍 Super-Röntgen Diagnose</h1>";

if (!$token) {
    echo "<p style='color:#ef4444;'>❌ <strong>Geen token gevonden!</strong><br>Log eerst in via <a href='google-auth.php' style='color:#fff;'>google-auth.php</a>.</p>";
    exit;
}

// 1. Token Info bij Google opvragen
$url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token;
$infoRaw = @file_get_contents($url);
$info = json_decode($infoRaw, true);

echo "<section style='margin-bottom:40px;'>";
echo "<h3 style='color:#94a3b8; text-transform:uppercase; font-size:12px; letter-spacing:2px;'>1. Identiteit van de Sleutel</h3>";

if ($info && isset($info['issued_to'])) {
    $tokenClientId = $info['issued_to'];
    $envClientId = getenv('GOOGLE_CLIENT_ID');

    echo "Token uitgegeven aan Client ID:<br><code style='background:#1e293b; padding:4px 8px; border-radius:6px; display:inline-block; margin:8px 0;'>$tokenClientId</code><br><br>";
    echo "ID in jouw Coolify .env:<br><code style='background:#1e293b; padding:4px 8px; border-radius:6px; display:inline-block; margin:8px 0;'>$envClientId</code><br><br>";

    if (trim($tokenClientId) === trim($envClientId)) {
        echo "<b style='color:#10b981;'>✅ DE IDENTITEIT MATCHT</b>";
    } else {
        echo "<b style='color:#f59e0b;'>⚠️ IDENTITEIT MISMATCH!</b><br>";
        echo "<span style='font-size:13px; color:#94a3b8;'>Je app gebruikt een sleutel die hoort bij een andere Client ID. Waarschijnlijk staat er nog een oude token in je database.</span>";
    }
    
    echo "<div style='margin-top:20px;'>Scopes in dit token:<br>";
    if (isset($info['scope'])) {
        $scopes = explode(" ", $info['scope']);
        foreach($scopes as $s) {
            $isPhotos = ($s == 'https://www.googleapis.com/auth/photoslibrary.readonly');
            echo ($isPhotos ? "<span style='color:#10b981;'>[✅]</span> " : "<span style='color:#64748b;'>[ ]</span> ") . $s . "<br>";
        }
    }
    echo "</div>";
} else {
    echo "<p style='color:#ef4444;'>Kon token info niet ophalen bij Google. Response: " . htmlspecialchars($infoRaw) . "</p>";
}
echo "</section>";

// 2. Directe API Test
echo "<section>";
echo "<h3 style='color:#94a3b8; text-transform:uppercase; font-size:12px; letter-spacing:2px;'>2. Live Photos API Test</h3>";

$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=1");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json",
    "User-Agent: Forcekes-Diagnostic-Tool"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: <b style='color:".($httpCode == 200 ? '#10b981' : '#f59e0b').";'>$httpCode</b><br><br>";

if ($httpCode == 200) {
    echo "<b style='color:#10b981;'>🎉 DE DEUR IS OPEN!</b><br>Google Photos laat ons binnen. De admin zou nu moeten werken.";
} else {
    echo "<b style='color:#ef4444;'>🚫 DE DEUR BLIJFT DICHT.</b><br>";
    echo "Google zegt:<br><pre style='background:#0f172a; border:1px solid #1e293b; padding:20px; border-radius:12px; margin-top:10px; color:#94a3b8; overflow-x:auto;'>";
    echo htmlspecialchars(json_encode(json_decode($raw), JSON_PRETTY_PRINT));
    echo "</pre>";
}
echo "</section>";
echo "</body>";END';
$envClientId = getenv('GOOGLE_CLIENT_ID');

echo "Token uitgegeven aan ID: <br><span style='color:#3b82f6'>$tokenClientId</span><br><br>";
echo "ID in je Coolify .env: <br><span style='color:#3b82f6'>$envClientId</span><br><br>";

if (trim($tokenClientId) === trim($envClientId)) {
    echo "<b style='color:green'>✅ DE IDENTITEIT MATCHT</b>";
} else {
    echo "<b style='color:red'>❌ IDENTITEIT MISMATCH!</b><br>Je app gebruikt een sleutel van een ander project.";
}

echo "<h3>2. API Toegangstest</h3>";
// We proberen een simpelere API call om te zien of het de Photos API is of Google in het algemeen
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=1");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "User-Agent: Forcekes-App"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Photos API Response Code: <b>$httpCode</b><br>";
if ($httpCode == 403) {
    echo "De deur blijft dicht. <br><b>Raw Response:</b><pre style='background:#222; padding:10px;'>" . htmlspecialchars($raw) . "</pre>";
} else {
    echo "<b style='color:green'>DE DEUR IS OPEN!</b>";
}

echo "</body>";