<?php
require_once 'config.php';
$currentPage = $_GET['page'] ?? 'museum';

// Haal alleen de foto's van de gevraagde categorie op
$photos = supabaseRequest("album_photos?category=eq.$currentPage&select=*", 'GET');
?>