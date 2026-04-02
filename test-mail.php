<?php
/** * FORCEKES - test-mail.php (Vlijmscherpe Mail Tester - Gekeurd door Manu) */
require_once 'config.php';

// Alleen toegankelijk voor de beheerder
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') {
    die("<body style='background:#000;color:red;font-family:monospace;padding:50px;'>GEEN TOEGANG: Alleen voor de Master Admin.</body>");
}

$status = "";
$testOntvanger = "koen@lauwe.com"; // Pas dit aan naar je eigen adres voor de test

if (isset($_GET['send'])) {
    $onderwerp = "FORCEKES SYSTEM CHECK: Postbode Test";
    $bericht = "Gegroet Koen,\r\n\r\nDit is een vlijmscherpe test van de Forcekes Media Engine.\r\nAls je dit leest, kan de server zelfstandig e-mails versturen.\r\n\r\nMet archivarische groet,\r\nDe Media-Uil";
    $headers = "From: Forcekes Portaal <noreply@forcekes.be>\r\n";
    $headers .= "Reply-To: noreply@forcekes.be\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (mail($testOntvanger, $onderwerp, $bericht, $headers)) {
        $status = "<span style='color:green;'>SUCCESS: De mail is overhandigd aan de postbode. Check je inbox (en spam)!</span>";
    } else {
        $status = "<span style='color:red;'>FAILURE: De server weigert de mail te versturen. Mogelijk ontbreekt een SMTP-configuratie.</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mail Test | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 3rem; text-align: center; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="card max-w-lg w-full">
        <h1 class="text-3xl font-black mb-6 italic uppercase tracking-tighter text-blue-600">Mail Test</h1>
        <p class="text-zinc-500 text-sm mb-10 leading-relaxed">
            We testen nu of de server een notificatie kan sturen naar:<br>
            <strong class="text-white"><?= $testOntvanger ?></strong>
        </p>

        <?php if($status): ?>
            <div class="mb-10 p-4 rounded-xl bg-white/5 border border-white/10 text-xs font-bold uppercase tracking-widest leading-relaxed">
                <?= $status ?>
            </div>
        <?php endif; ?>

        <a href="test-mail.php?send=1" class="inline-block px-10 py-4 bg-white text-black rounded-full font-black uppercase text-[10px] tracking-[0.3em] hover:bg-blue-600 hover:text-white transition">
            Verstuur Testmail
        </a>

        <div class="mt-12 pt-8 border-t border-white/5">
            <a href="admin.php" class="text-[9px] font-black text-zinc-700 uppercase tracking-widest hover:text-white">Terug naar Beheer</a>
        </div>
    </div>
</body>
</html>