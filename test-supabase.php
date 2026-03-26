<?php
/**
 * FORCEKES - test-supabase.php
 * Test of de PHP app kan schrijven naar de database.
 */
require_once 'config.php';

echo "<h1>Supabase Verbindingstest</h1>";

// We proberen een test-waarde te schrijven naar de access_token kolom van ID 1
$testData = ['access_token' => 'TEST_VAN_APP_' . date('H:i:s')];
$res = supabaseRequest('google_tokens?id=eq.1', 'PATCH', $testData);

echo "<h2>Resultaat van PATCH aanvraag:</h2>";
echo "<pre>";
print_r($res);
echo "</pre>";

if (isset($res[0]['access_token'])) {
    echo "<h3 style='color:green;'>✅ SUCCESS! De app kan schrijven naar Supabase.</h3>";
} else {
    echo "<h3 style='color:red;'>❌ FAILURE! De app krijgt geen antwoord van Supabase.</h3>";
    echo "<p>Check of <strong>RLS (Row Level Security)</strong> uitstaat op de tabel 'google_tokens' in Supabase!</p>";
}