<?php
/**
 * FORCEKES - Centrale Configuratie
 * * Dit bestand beheert de verbindingen met Supabase en de globale instellingen.
 * Gebruikt robuuste detectie voor Coolify Environment Variables.
 */

// 1. DOMEIN INSTELLINGEN
define('SITE_URL', 'https://forcekes.be');

// 2. SUPABASE URL DETECTIE
$envUrl = getenv('SUPABASE_URL') ?: getenv('NEXT_PUBLIC_SUPABASE_URL') ?: 'https://supa.forcekes.be';
define('SUPABASE_URL', rtrim((string)$envUrl, '/'));

// 3. SUPABASE SLEUTEL DETECTIE (ANON KEY)
// We checken alle mogelijke aliassen die Coolify of Supabase kunnen gebruiken
$envKey = getenv('SUPABASE_KEY') ?: 
          getenv('SERVICE_SUPABASEANON_KEY') ?: 
          getenv('SUPABASE_ANON_KEY') ?: 
          getenv('NEXT_PUBLIC_SUPABASE_ANON_KEY');

define('SUPABASE_KEY', (string)$envKey);

// 4. SERVICE ROLE KEY (Enkel voor de scraper/admin taken)
define('SUPABASE_SERVICE_KEY', (string)getenv('SUPABASE_SERVICE_ROLE_KEY'));

/**
 * Centrale functie voor veilige Supabase API requests (REST)
 * * @param string $endpoint Het API eindpunt (bv. 'album_photos?select=*')
 * @param string $method GET, POST, PATCH, DELETE
 * @param array|null $data De payload voor POST/PATCH
 * @return array De gedecodeerde JSON respons of een foutmelding
 */
function supabaseRequest($endpoint, $method = 'GET', $data = null) {
    if (!SUPABASE_KEY || SUPABASE_KEY === "") {
        return [
            'error' => true,
            'message' => 'Configuratie fout: Geen geldige SUPABASE_KEY gevonden. Controleer de Environment Variables in Coolify.'
        ];
    }

    $url = SUPABASE_URL . '/rest/v1/' . $endpoint;
    $ch = curl_init($url);
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && ($method === 'POST' || $method === 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    // SSL verificatie is belangrijk voor productie op forcekes.be
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['error' => true, 'message' => 'CURL Fout: ' . $curlError];
    }

    $decodedResponse = json_decode($response, true);

    // Als de bouncer (Supabase) klaagt over credentials
    if ($httpCode === 401 || $httpCode === 403) {
        return [
            'error' => true, 
            'message' => 'Supabase authenticatie geweigerd. Controleer of de ANON KEY nog geldig is.',
            'debug_code' => $httpCode
        ];
    }

    return $decodedResponse;
}

// 5. SESSION SETUP (Voor inloggen later)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}