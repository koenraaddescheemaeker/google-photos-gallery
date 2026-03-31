<?php
/** * FORCEKES - auth-handler.php (Fase 11: Definitieve Fix) */
session_start();
require_once 'config.php';

// Buffering om 'headers already sent' te voorkomen
ob_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = $_POST['password'] ?? '';

    // Voorlopige login (koen@lauwe.com)
    if ($email === 'koen@lauwe.com' && !empty($password)) {
        $_SESSION['user_email'] = $email;
        header("Location: index.php");
        exit;
    } else {
        // Andere familieleden (tijdelijk)
        if (!empty($email) && !empty($password)) {
            $_SESSION['user_email'] = $email;
            header("Location: index.php");
            exit;
        }
    }
}

// Als we hier komen, is er iets mis
header("Location: login.php?error=1");
exit;
ob_end_flush();