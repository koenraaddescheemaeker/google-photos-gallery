function supabase($table) {
    $url = "https://supa.forcekes.be/rest/v1/" . $table;

    $headers = [
        "apikey: " . getenv('SUPABASE_ANON_KEY'),
        "Authorization: Bearer " . getenv('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJzdXBhYmFzZSIsImlhdCI6MTc3MzQ4MzM2MCwiZXhwIjo0OTI5MTU2OTYwLCJyb2xlIjoiYW5vbiJ9.LXIJo7fsXhJIQsSi2jIfoqrwV8axI57_6B733vKwCXs'),
        "Content-Type: application/json"
    ];

    return [$url, $headers];
}