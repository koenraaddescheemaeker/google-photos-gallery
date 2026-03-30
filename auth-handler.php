<?php
/** * FORCEKES - auth-handler.php (Fase 11: De Verkeerstoren) */
require_once 'config.php';

// De uil controleert of er wel echt geprobeerd is in te loggen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // TIJDELIJKE LOGICA (Vervang dit later door een database check als je dat wilt)
    // Voor nu: we laten Koen toe en zetten de sessie.
    if (!empty($email) && !empty($password)) {
        
        // We slaan de email op in de sessie om te weten wie er binnen is
        $_SESSION['user_email'] = $email;
        
        // De uil geeft groen licht: Stuur door naar de homepagina
        header("Location: index.php");
        exit;
    } else {
        // Foutje bedankt: Terug naar login met een foutmelding
        header("Location: login.php?error=invalid");
        exit;
    }
} else {
    // Iemand probeert rechtstreeks naar dit bestand te surfen? Terug naar af.
    header("Location: login.php");
    exit;
}