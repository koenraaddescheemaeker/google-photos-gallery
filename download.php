<?php
/** * FORCEKES - download.php (Forced Download Proxy - FIXED) */
require_once 'config.php';

// Haal de URL op uit de parameters
$fileUrl = $_GET['file'] ?? '';

// Basis check of het een valide Forcekes-link is
if (empty($fileUrl) || strpos($fileUrl, 'supa.forcekes.be') === false) {
    die("Fout: Ongeldig bestand of bron niet toegestaan.");
}

// FIX: PHP_URL_PATH is de juiste constante om de bestandsnaam uit de URL te vissen
$path = parse_url($fileUrl, PHP_URL_PATH);
$fileName = basename($path);

// Zet de headers om de browser te dwingen het bestand op te slaan
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream'); // Forceert 'download' ipv 'openen'
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Schoon de output buffer op om corrupte bestanden te voorkomen
if (ob_get_length()) ob_clean();
flush();

// Stream het bestand van de Supabase storage direct naar de gebruiker
readfile($fileUrl);
exit;