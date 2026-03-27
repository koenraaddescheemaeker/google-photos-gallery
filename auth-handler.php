<?php
/** FORCEKES - auth-handler.php */
require_once 'config.php';

$action = $_GET['action'] ?? '';

if ($action === 'register') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $name = $_POST['name'];

    // In een echt project gebruik je hier de Supabase Auth API
    // Voor nu maken we een eenvoudige rij aan in een 'users' tabel (optioneel)
    // Maar laten we voor de demo even een sessie starten
    $_SESSION['user_id'] = $email;
    $_SESSION['user_name'] = $name;
    header("Location: index.php?view=dashboard");
}

if ($action === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Demo check (Vervang dit later door Supabase Auth check)
    if (!empty($email) && !empty($password)) {
        $_SESSION['user_id'] = $email;
        $_SESSION['user_name'] = "Familielid";
        header("Location: index.php?view=dashboard");
    } else {
        header("Location: index.php?view=login&error=1");
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php?view=login");
}