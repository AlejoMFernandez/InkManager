<?php
/**
 * Monograma de marca — SVG inline reutilizable.
 * Variables opcionales:
 *   $monogramSize    (default 40)
 *   $monogramAnimate (default true)
 */
$size    = $monogramSize    ?? 40;
$animate = $monogramAnimate ?? true;
$cls     = $animate ? 'monogram' : '';
?>
<span class="<?= $cls ?>" style="width:<?= (int)$size ?>px; height:<?= (int)$size ?>px;">
    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <!-- gradient defs -->
        <defs>
            <linearGradient id="mg-grad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%"   stop-color="#ef4444"/>
                <stop offset="100%" stop-color="#7f1d1d"/>
            </linearGradient>
            <linearGradient id="mg-shine" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%"   stop-color="#fca5a5"/>
                <stop offset="100%" stop-color="#ef4444"/>
            </linearGradient>
        </defs>

        <!-- background rounded square -->
        <rect x="2" y="2" width="60" height="60" rx="14"
              fill="url(#mg-grad)"
              stroke="rgba(255,255,255,0.08)" stroke-width="1"/>

        <!-- "I" stem (the monogram core) -->
        <path d="M22 16 L22 48"
              class="stroke" style="--len:32"
              stroke="url(#mg-shine)" stroke-width="3.5"
              stroke-linecap="round"/>

        <!-- "M" zig-zag (needle line) -->
        <path d="M30 48 L30 16 L36 28 L42 16 L42 48"
              class="stroke" style="--len:80"
              stroke="#fff" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round"
              fill="none"/>

        <!-- Ink drop -->
        <circle cx="22" cy="54" r="2.5"
                class="drop"
                fill="#fff"/>
    </svg>
</span>
