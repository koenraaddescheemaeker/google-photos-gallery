<?php

function supabaseRequest($table, $filters = []) {
    $url = "https://supa.forcekes.be/rest/v1/" . $table;

    if (!empty($filters)) {
        $url .= '?' . http_build_query($filters);
    }

    $headers = [
        "apikey: " . getenv('SUPABASE_ANON_KEY'),
        "Authorization: Bearer " . getenv('SUPABASE_ANON_KEY'),
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        die("Supabase request failed: " . curl_error($ch));
    }

    curl_close($ch);

    return json_decode($response, true);
}