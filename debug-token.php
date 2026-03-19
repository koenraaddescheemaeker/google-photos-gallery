<?php
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    die("Geen token gevonden. Log eerst in via google-auth.php");
}

$url = "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token;
$res = json_decode(file_get_contents($url), true);

echo "<h1>Token Analyse</h1><pre>";
if (isset($res['scope'])) {
    $scopes = explode(" ", $res['scope']);
    echo "De sleutel heeft toegang tot:\n";
    foreach($scopes as $s) {
        echo ($s == 'https://www.googleapis.com/auth/photoslibrary.readonly' ? "✅ " : "❌ ") . $s . "\n";
    }
} else {
    print_r($res);
}
echo "</pre>";