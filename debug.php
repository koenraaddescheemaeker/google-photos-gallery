<?php
require_once 'config.php';
$res = supabaseRequest('google_tokens?select=*');
echo "<h1>Database inhoud:</h1><pre>";
print_r($res);
echo "</pre>";