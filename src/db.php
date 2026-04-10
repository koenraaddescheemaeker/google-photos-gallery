<?php

function db() {
    static $pdo;

    if (!$pdo) {
        $dsn = "pgsql:host=" . getenv('DB_HOST') . 
               ";port=" . getenv('DB_PORT') . 
               ";dbname=" . getenv('DB_NAME');

        $pdo = new PDO(
            $dsn,
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    return $pdo;
}