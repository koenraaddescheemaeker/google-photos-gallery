<?php
/** * FORCEKES - auth-handler.php (Robust Auth Master) */

// 1. Zorg dat we geen fouten op het scherm spugen die de headers verpesten
error_reporting(E_ALL & ~E_DEPRECATED); 
ini_set('display_errors', 0);

require_once 'config.php';

// 2. Veiligheid: check of de URL's wel bestaan
$rawUrl = getenv('SUPABASE_URL') ?: (defined('SUPABASE_URL') ? SUPABASE_URL : '');
$supabaseUrl = rtrim((string)$rawUrl, '/'); // Forceer naar string om rtrim-null error te voorkomen

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    // Hier komt je login-logica (bv. doorsturen naar Google of Supabase Auth)
    // Voor nu sturen we de gebruiker naar de juiste plek
    
    if (empty($supabaseUrl)) {
        die("Fout: SUPABASE_URL is niet geconfigureerd in je omgeving.");
    }

    $authUrl = $supabaseUrl . "/auth/v1/authorize?provider=google&redirect_to=" . urlencode(SITE_URL . "/auth-handler.php?action=callback");
    
    header("Location: $authUrl");
    exit;
}

if ($action === 'callback') {
    // Hier vang je het antwoord van Supabase op
    // Sla de sessie op in een cookie of session en stuur naar de homepage
    header("Location: " . SITE_URL . "/index.php");
    exit;
}

if ($action === 'logout') {
    // Clear cookies/session
    setcookie("supabase-auth", "", time() - 3600, "/");
    header("Location: " . SITE_URL . "/index.php");
    exit;
}