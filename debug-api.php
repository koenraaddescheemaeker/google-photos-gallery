<?php
/**
 * FORCEKES ATOMIC DEBUGGER - Safe Edition
 * Gebruik: debug-api.php?token=ya29...
 */

$testToken = $_GET['token'] ?? null;

if (!$testToken) {
    die("<h1>Fout</h1><p>Geen token gevonden in de URL. Gebruik: debug-api.php?token=JOUW_TOKEN</p>");
}

function checkEndpoint($name, $url, $token) {
    echo "--- Testen: $name ---<br>";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Status: $httpCode<br>";
    echo "Response: <pre>" . print_r(json_decode($res, true), true) . "</pre><hr>";
}

echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:20px;'>";
checkEndpoint("Google UserInfo", "https://www.googleapis.com/oauth2/v3/userinfo", $testToken);
checkEndpoint("Photos Albums", "https://photoslibrary.googleapis.com/v1/albums?pageSize=1", $testToken);
echo "</body>";