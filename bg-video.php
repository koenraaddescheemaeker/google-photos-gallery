<?php
/** * FORCEKES - bg-video.php (Gekeurd door Manu) */
// Manu: We wijzen direct naar de publieke URL in je eigen Supabase bucket
$localVideoUrl = "https://supa.forcekes.be/storage/v1/object/public/familie-media/assets/bg-atmosphere.mp4";
?>
<style>
    .global-video-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.12; /* Manu: Iets lager voor extra focus op de content */
        filter: grayscale(100%) brightness(0.8);
        z-index: -1;
        pointer-events: none;
    }
</style>
<video autoplay muted loop playsinline class="global-video-bg">
    <source src="<?= $localVideoUrl ?>" type="video/mp4">
</video>