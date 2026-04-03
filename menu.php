<?php
require_once 'config.php';
$uEmail = $_SESSION['user_email'] ?? '';
$isAdm = ($uEmail === 'koen@lauwe.com');
$feesten = supabaseRequest("rpc/get_album_dashboard", 'GET');
$familie = supabaseRequest("members?is_approved=eq.true&nickname=not.is.null", 'GET');
?>
<style>
.glass-nav { background: rgba(0,0,0,0.85); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.05); }
.nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #fff; cursor: pointer; }
.sidebar { position: fixed; top: 0; right: 0; bottom: 0; width: 350px; background: #020202; z-index: 1000; transform: translateX(100%); transition: transform 0.4s ease; border-left: 1px solid rgba(255,255,255,0.1); padding: 3rem; }
.sidebar.visible { transform: translateX(0); }
.side-item { display: block; padding: 1rem 0; font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.8rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.05); text-decoration: none; }
</style>
<nav class="fixed top-0 left-0 right-0 z-[100] glass-nav">
<div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
<a href="index.php" class="text-lg font-black text-white">Force<span class="text-blue-600">kes</span></a>
<div class="hidden md:flex items-center space-x-10">
<a href="index.php" class="nav-link">HOME</a>
<a href="zwaaikamer.php" class="nav-link">ZWAAIKAMER</a>
<button onclick="toggleSidebar()" class="nav-link text-blue-500">MENU</button>
</div>
</div>
</nav>
<aside id="main-sidebar" class="sidebar">
<button onclick="toggleSidebar()" class="text-zinc-500 mb-10">SLUITEN</button>
<nav>
<a href="index.php" class="side-item">HOME</a>
<a href="zwaaikamer.php" class="side-item">ZWAAIKAMER</a>
<a href="handleiding.php" class="side-item">HANDLEIDING</a>
<?php if($isAdm): ?><a href="admin.php" class="side-item text-blue-900">ADMIN</a><?php endif; ?>
<a href="login.php" class="side-item text-zinc-600"><?= $uEmail ? 'LOGOUT' : 'TOEGANG' ?></a>
</nav>
</aside>
<script>
function toggleSidebar() { document.getElementById('main-sidebar').classList.toggle('visible'); }
</script>