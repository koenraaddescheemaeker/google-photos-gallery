<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Koppelen...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white p-8 rounded-3xl shadow-xl max-w-sm w-full text-center">
        <?php
        if (!isset($_GET['code'])) {
            echo "<h1 class='text-red-500 font-bold'>Fout</h1><p>Geen autorisatiecode ontvangen.</p>";
            exit;
        }

        // Token ophalen
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
            supabaseRequest('google_tokens', 'UPSERT', [
                'id'            => 1,
                'access_token'  => $response['access_token'],
                'refresh_token' => $response['refresh_token'] ?? null,
                'expires_at'    => date('Y-m-d H:i:s', time() + $response['expires_in'])
            ]);
            echo "<div class='animate-bounce text-4xl mb-4'>✅</div>";
            echo "<h1 class='text-2xl font-semibold text-slate-800'>Gelukt!</h1>";
            echo "<p class='text-slate-500 mt-2'>Je tokens zijn veilig opgeslagen.</p>";
            echo "<script>setTimeout(() => { window.location.href = 'admin.php'; }, 2000);</script>";
        } else {
            echo "<h1 class='text-red-500 font-bold text-xl'>Google weigert toegang</h1>";
            echo "<p class='text-sm text-slate-400 mt-2'>Check of je Client Secret in config.php klopt.</p>";
            echo "<pre class='mt-4 p-2 bg-slate-100 rounded text-left text-xs'>" . print_r($response, true) . "</pre>";
        }
        ?>
    </div>
</body>
</html>