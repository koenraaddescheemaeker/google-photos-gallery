<?php

require_once __DIR__ . '/db.php';

function getPhotos($type = 'museum') {

    if ($type === 'museum') {
        $filters = [
            "category" => "ilike.*museum*",
            "order" => "id.desc"
        ];
    } else {
        $filters = [
            "category" => "not.ilike.*museum*",
            "order" => "id.desc"
        ];
    }

    return supabaseRequest("album_photos", $filters);
}