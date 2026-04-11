<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// versie 0957//
require_once __DIR__ . '/db.php';

function getPhotos($type = 'museum') {

    if ($type === 'museum') {
        $filters = [
            "category" => "eq.het museum",
            "order" => "id.desc"
        ];
    } else {
        $filters = [
            "category" => "neq.het museum",
            "order" => "id.desc"
        ];
    }

    return supabaseRequest("album_photos", $filters);
}