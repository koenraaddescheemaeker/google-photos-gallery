<?php
/** * FORCEKES - auth-handler.php (Fase 11: Bulletproof Redirect) */
ob_start(); // Voorkom 'headers al verstuurd' fouten
session_start();
require_once 'config.php';

// Foutrapportage even AAN zetten om te zien wat er gebeurt bij het witte scherm
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Eenvoudige verificatie (Later uitbreiden met DB check)
    if (!empty($email) && !empty($password)) {
        
        // Gebruiker opslaan in sessie
        $_SESSION['user_email'] = $email;
        
        // Forceer redirect via PHP
        header("Location: index.php");
        
        // Back-up redirect via JavaScript (voor het geval de PHP header faalt)
        echo '<script>window.location.href="index.php";</script>';
        exit;
    } else {
        header("Location: login.php?error=empty");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
ob_end_flush();