<?php
// google-callback.php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Koppelen...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-xl max-w-sm w-full text-center">
        <?php
        if (!isset($_GET['code'])) exit;

        $ch = curl_init("https://oauth2.googleapis.com/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id'     => $googleClientID,
            'client_secret' => $googleClientSecret,
            'redirect_uri'  => $googleRedirectUri,
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code']
        ]));
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($response['access_token'])) {
            // BOUW DE DATA OP
            $dbData = [
                'id'           => 1,
                'access_token' => $response['access_token'],
                'expires_at'   => date('Y-m-d H:i:s', time() + $response['expires_in'])
            ];

            // VOEG REFRESH TOKEN ALLEEN TOE ALS GOOGLE HEM STUURT
            if (!empty($response['refresh_token'])) {
                $dbData['refresh_token'] = $response['refresh_token'];
            }

            // UPSERT (PostgREST zal bestaande velden behouden als ze niet in $dbData zitten, 
            // mits correct geconfigureerd, maar wij sturen nu alleen wat we hebben)
            supabaseRequest('google_tokens', 'UPSERT', $dbData);

            echo "<div class='text-4xl mb-4'>✅</div>";
            echo "<h1 class='text-2xl font-bold'>Gekoppeld!</h1>";
            echo "<p class='text-slate-500'>Je wordt nu doorgestuurd...</p>";
            echo "<script>setTimeout(() => { window.location.href = 'admin.php'; }, 1500);</script>";
        } else {
            echo "<h1 class='text-red-500 font-bold'>Fout bij Google</h1>";
            echo "<pre class='text-xs text-left bg-slate-100 p-2 mt-2'>" . print_r($response, true) . "</pre>";
        }
        ?>
    </div>
</body>
</html>