<?php
echo "<h2>Docker Netwerk Scan</h2>";

// De meest voorkomende namen in Coolify Supabase stacks
$targets = [
    'supabase-force',
    'supabase-force-kong',
    'supabase_kong',
    'kong',
    'supabase-kong'
];

foreach ($targets as $host) {
    echo "Checken van <b>$host</b>... ";
    $ip = gethostbyname($host);
    
    if ($ip === $host) {
        echo "<span style='color:gray'>Niet gevonden via DNS.</span><br>";
    } else {
        echo "<span style='color:blue'>Gevonden op IP: $ip</span>. ";
        
        // Test poort 8000
        $connection = @fsockopen($host, 8000, $errno, $errstr, 2);
        if (is_resource($connection)) {
            echo "<b style='color:green'>POORT 8000 IS OPEN!</b> ✅<br>";
            fclose($connection);
        } else {
            echo "<b style='color:red'>POORT 8000 DICHT.</b> ($errstr)<br>";
        }
    }
}