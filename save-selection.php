<?php
/**
 * FORCEKES - save-selection.php
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['album_id'])) {
    $albumId = $_POST['album_id'];
    $title = $_POST['title'];

    // We slaan dit op in een tabel genaamd 'settings' of 'active_albums'
    // Voor nu gebruiken we een simpele update in Supabase
    $res = supabaseRequest('settings?id=eq.1', 'PATCH', [
        'selected_album_id' => $albumId,
        'selected_album_title' => $title
    ]);

    header("Location: admin.php?saved=true");
    exit;
}