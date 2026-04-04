<?php
// Zorg dat de verbinding ALTIJD beschikbaar is voordat we de query doen
require_once 'config.php'; 

if (!isset($db)) {
    // Veiligheidscheck voor de Architect
    die("Fout: De databaseverbinding (\$db) is niet gedefinieerd in config.php!");
}

$navItems = $db->query("SELECT * FROM navigation WHERE is_active = true ORDER BY order_num ASC");
?>

<nav class="fixed top-0 left-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-white/10">
    </nav>