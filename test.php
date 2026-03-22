<?php
echo "PHP Versie: " . phpversion() . "<br>";
echo "CURL Extensie: " . (function_exists('curl_init') ? '✅ OK' : '❌ MISSING') . "<br>";
echo "JSON Extensie: " . (function_exists('json_decode') ? '✅ OK' : '❌ MISSING') . "<br>";
?>