<?php
/**
 * Intro splash — se muestra una vez por sesión (sessionStorage).
 * Logo grande + nombre + tagline + barra animada → fade out.
 */
?>
<div id="brand-splash" aria-hidden="true">
    <div class="splash-mono">
        <?php $monogramSize = 96; $monogramAnimate = true;
              require __DIR__ . '/monogram.php'; ?>
    </div>
    <h1 class="splash-name">InkManager</h1>
    <p class="splash-tag">Digital Studio System</p>
    <div class="splash-bar"></div>
</div>

<script>
(function () {
    const splash = document.getElementById('brand-splash');
    if (!splash) return;

    // Solo una vez por sesión
    if (sessionStorage.getItem('inkmanager_splash_shown') === '1') {
        splash.remove();
        return;
    }

    // Bloquear scroll mientras está
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        splash.classList.add('splash-out');
        setTimeout(() => {
            splash.remove();
            document.body.style.overflow = '';
            sessionStorage.setItem('inkmanager_splash_shown', '1');
        }, 700);
    }, 1700);
})();
</script>
