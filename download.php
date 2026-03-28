<?php
/** * FORCEKES - download.php (Production Proxy) */
require_once 'config.php';

$fileUrl = $_GET['file'] ?? '';

// Controleer of het bestand van onze vertrouwde Supabase komt
if (empty($fileUrl) || strpos($fileUrl, 'supa.forcekes.be') === false) {
    die("Fout: Ongeldig bestand of bron niet toegestaan.");
}

$path = parse_url($fileUrl, PHP_URL_PATH);
$fileName = basename($path);

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

if (ob_get_length()) ob_clean();
flush();

readfile($fileUrl);
exit;