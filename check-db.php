<?php
/**
 * check-db.php - De Inspecteur
 * Doel: Controleren of de tokens correct in Supabase staan.
 */
require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='nl'>
<head>
    <meta charset='UTF-8'>
    <title>Database Inspectie - Familie Forcekes</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-slate-900 text-slate-100 p-8 font-sans'>";

echo "<div class='max-w-2xl mx-auto bg-slate-800 p-6 rounded-3xl shadow-2xl border border-slate-700'>";
echo "<h1 class='text-2xl font-bold mb-6 flex items-center gap-2'>
        <span class='text-blue-400'>🔍</span> Database Inspectie
      </h1>";

// Haal record ID 1 op
$res = supabaseRequest('google_tokens?id=eq.1&select=*');

if (empty($res)) {
    echo "<div class='bg-red-500/10 border border-red-500/50 p-4 rounded-2xl text-red-400 mb-4'>
            <strong>❌ Geen gegevens gevonden!</strong><br>
            Er staat geen record met ID 1 in de tabel 'google_tokens'. De callback heeft waarschijnlijk niets opgeslagen.
          </div>";
} else {
    $data = $res[0];
    $hasRefreshToken = !empty($data['refresh_token']);
    $expiresAt = $data['expires_at'] ?? 'Onbekend';
    
    echo "<div class='space-y-4'>";
    
    // Status overzicht
    echo "<div class='grid grid-cols-2 gap-4 text-sm'>";
    echo "<div class='bg-slate-700/50 p-3 rounded-xl'><strong>ID:</strong> " . $data['id'] . "</div>";
    echo "<div class='bg-slate-700/50 p-3 rounded-xl'><strong>Vervalt op:</strong> " . $expiresAt . "</div>";
    echo "</div>";

    // Refresh Token Check (De belangrijkste!)
    if ($hasRefreshToken) {
        echo "<div class='bg-green-500/10 border border-green-500/50 p-4 rounded-2xl text-green-400'>
                <strong>✅ Refresh Token aanwezig</strong><br>
                Dit is goed! De app kan nu zelfstandig nieuwe toegang aanvragen.
              </div>";
    } else {
        echo "<div class='bg-yellow-500/10 border border-yellow-500/50 p-4 rounded-2xl text-yellow-400'>
                <strong>⚠️ Refresh Token ontbreekt!</strong><br>
                Google stuurt deze alleen de eerste keer. Gebruik 'Truncate' in SQL en log opnieuw in.
              </div>";
    }

    // Ruwe data voor de arend-blik
    echo "<h2 class='text-sm font-semibold text-slate-400 mt-6 mb-2 uppercase tracking-wider'>Ruwe Data</h2>";
    echo "<pre class='bg-black/50 p-4 rounded-xl text-xs overflow-x-auto text-blue-300 border border-slate-700'>";
    print_r($data);
    echo "</pre>";
    
    echo "</div>";
}

echo "<div class='mt-8 pt-6 border-t border-slate-700 flex gap-4'>
        <a href='google-auth.php' class='bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-sm transition'>Opnieuw Inloggen</a>
        <a href='index.php' class='bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-xl text-sm transition'>Naar Homepage</a>
      </div>";

echo "</div></body></html>";