<?php
require_once 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h1>Debug Engine</h1>";
$res = supabaseRequest("rpc/get_album_dashboard", 'GET');
if (!$res) {
    echo "<p style='color:red;'>[FOUT] Geen respons van Supabase. Check URL/KEY.</p>";
} elseif (isset($res['error'])) {
    echo "<p style='color:orange;'>[DB FOUT] " . $res['error']['message'] . "</p>";
} else {
    echo "<p style='color:green;'>[OK] " . count($res) . " feesten gevonden.</p>";
    print_r($res[0]); // Toon het eerste item
}
?>