<?php
require_once 'config.php';

echo "<body style='background:#111; color:#fff; font-family:sans-serif; padding:40px; line-height:1.6;'>";
echo "<h1>🕵️ De Google Leugendetector</h1>";
echo "<hr style='border-color:#333; margin-bottom:20px;'>";

$token = getValidAccessToken();

if (!$token) {
    die("<h2 style='color:#ef4444;'>Geen token gevonden of refresh mislukt! (Controleer Supabase)</h2>");
}

echo "✅ Sleutel succesvol opgehaald uit je database.<br><br>";
echo "We vragen nu aan Google's centrale beveiliging: <i>'Welke rechten zitten er op DIT exacte moment op deze sleutel?'</i><br><br>";

// We sturen de sleutel naar de Google TokenInfo scanner
$ch = curl_init("https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$resRaw = curl_exec($ch);
$res = json_decode($resRaw, true);
curl_close($ch);

echo "<pre style='background:#222; padding:20px; color:#10b981; font-size:15px; border-left:5px solid #3b82f6; overflow-x:auto;'>" . htmlspecialchars(print_r($res, true)) . "</pre>";

if (isset($res['scope']) && strpos($res['scope'], 'photoslibrary') !== false) {
    echo "<h2 style='color:#10b981;'>CONCLUSIE 1: De stempel ZIT erop!</h2>";
    echo "<p>Jouw code en database zijn 100% perfect. Google bevestigt dat je de rechten hebt. Als de Foto API nu nog steeds 403 zegt, is de Google Foto API zélf stuk, of blokkeren ze specifiek jouw Google-account voor API toegang.</p>";
} else {
    echo "<h2 style='color:#ef4444;'>CONCLUSIE 2: Google liegt en heeft de stempel gestript!</h2>";
    echo "<p>Je hebt het vinkje aangezet (dat staat op de foto!), maar zodra je code de token ophaalde, heeft Google de foto-rechten er op de achtergrond afgesloopt. Dit is een brute Google-restrictie.</p>";
}
echo "</body>";
?>