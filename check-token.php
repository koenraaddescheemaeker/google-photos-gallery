<?php
require_once 'config.php';
$token = getValidAccessToken();
$ch = curl_init("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = json_decode(curl_exec($ch), true);
echo "<h3>Jouw token heeft deze rechten:</h3><pre>";
print_r($res['scope']);
echo "</pre>";
?>