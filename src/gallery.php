<?php

require_once __DIR__ . '/db.php';

function getPhotos($type = 'museum') {
    $pdo = db();

    if ($type === 'museum') {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM album_photos 
            WHERE category = 'het museum' 
            ORDER BY id DESC
        ");
    } else {
        $stmt = $pdo->prepare("
            SELECT * 
            FROM album_photos 
            WHERE category != 'het museum' 
            ORDER BY id DESC
        ");
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}