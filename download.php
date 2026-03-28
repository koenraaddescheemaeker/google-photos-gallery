<?php
/** * FORCEKES - download.php (Forced Download Proxy) */
require_once 'config.php';

$fileUrl = $_GET['file'] ?? '';

if (empty($fileUrl) || strpos($fileUrl, 'supa.forcekes.be') === false) {
    die("Ongeldig bestand.");
}

// Haal de bestandsnaam op
$fileName = basename(parse_url($fileUrl, PHP_PATH));

// Zet headers om download te forceren
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Stream het bestand van Supabase naar de gebruiker
readfile($fileUrl);
exit;