<?php
require_once 'config.php';
echo "Testen van schrijven naar Supabase op $supabaseUrl...<br>";

$testData = [
    'id' => 999,
    'access_token' => 'test_verbinding_' . time()
];

$res = supabaseRequest('google_tokens', 'UPSERT', $testData);

if (isset($res['error'])) {
    echo "❌ FOUT: " . $res['error'];
} else {
    echo "✅ SUCCES! De database accepteert nu data. Je kunt nu veilig inloggen.";
}