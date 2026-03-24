<?php
/**
 * FORCEKES - google-callback.php (Debug Edition)
 */
require_once 'config.php';

if (!isset($_GET['code'])) {
    die("<h1>Fout</h1><p>Geen code ontvangen van Google. Probeer opnieuw in te loggen via login.php.</p>");
}

// 1. Wissel de autorisatiecode in voor tokens
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

echo "<body style='background:#111; color:#eee; font-family:sans-serif; padding:40px; line-height:1.6;'>";
echo "<h1 style='color:#3b82f6;'>STAP 1: Google Response</h1>";

if (isset($res['access_token'])) {
    echo "<p style='color:#4ade80;'>✅ Access Token ontvangen!</p>";
    if (isset($res['refresh_token'])) {
        echo "<p style='color:#4ade80;'>✅ Refresh Token ontvangen!</p>";
    } else {
        echo "<p style='color:#fbbf24;'>⚠️ Geen Refresh Token ontvangen. (Google stuurt deze alleen bij de eerste keer of bij 'prompt=consent').</p>";
    }

    $expiresAt = date('Y-m-d H:i:sO', time() + $res['expires_in']);
    
    // 2. Data voorbereiden voor Supabase
    $dataToUpdate = [
        'access_token' => $res['access_token'],
        'expires_at'   => $expiresAt
    ];
    
    if (isset($res['refresh_token'])) {
        $dataToUpdate['refresh_token'] = $res['refresh_token'];
    }

    echo "<h1 style='color:#3b82f6;'>STAP 2: Supabase Update</h1>";
    echo "<p>We proberen nu ID=1 bij te werken in Supabase...</p>";

    $sbResult = supabaseRequest('google_tokens?id=eq.1', 'PATCH', $dataToUpdate);

    echo "<div style='background:#222; border:1px solid #444; padding:20px; border-radius:10px;'>";
    echo "<strong>Response van Supabase:</strong><br><pre>";
    print_r($sbResult);
    echo "</pre></div>";

    echo "<br><br><a href='admin.php' style='display:inline-block; background:#3b82f6; color:white; padding:15px 30px; border-radius:12px; text-decoration:none; font-weight:bold;'>GA NAAR HET DASHBOARD</a>";
} else {
    echo "<h1 style='color:#ef4444;'>❌ Google Error</h1>";
    echo "<pre>"; print_r($res); echo "</pre>";
}
echo "</body>";