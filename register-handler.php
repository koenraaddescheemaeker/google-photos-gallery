<?php
/** * FORCEKES - register-handler.php */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $nickname = trim($_POST['nickname']);

    // 1. Maak gebruiker aan in onze eigen tabel
    supabaseRequest("members", "POST", [
        "email" => $email,
        "nickname" => $nickname,
        "is_approved" => false // Standaard niet goedgekeurd
    ]);

    // 2. Optioneel: Stuur een mail naar Koen dat er een nieuwe aanvraag is
    // (Voor nu gaan we ervan uit dat je dit in het Admin paneel checkt)

    header("Location: register.php?msg=success");
    exit;
}