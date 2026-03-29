<?php
require_once 'config.php';
echo "<h1>Forcekes Debug Mode</h1>";
echo "<p>Sessie Email: " . ($_SESSION['user_email'] ?? 'NIET INGESTELD') . "</p>";
echo "<p>Browser Cookie: " . (isset($_COOKIE['sb-access-token']) ? 'AANWEZIG' : 'ONTBREEKT') . "</p>";
echo "<p>Admin Check resultaat: " . (($_SESSION['user_email'] ?? '') === 'koen@lauwe.com' ? 'SUCCES' : 'GEWEIGERD') . "</p>";
echo "<hr>";
echo "<pre>"; print_r($_SESSION); echo "</pre>";