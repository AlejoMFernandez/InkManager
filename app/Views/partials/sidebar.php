<?php
use App\Core\Auth;
$user       = Auth::user();
$currentUri = strtok($_SERVER['REQUEST_URI'], '?');
$base       = BASE_URL;

function navActive(string $segment): string {
    $uri = strtok($_SERVER['REQUEST_URI'], '?');
    return str_contains($uri, $segment)
        ? 'bg-red-600/15 text-red-400 nav-active-glow'
        : 'text-gray-400 hover:text-white hover:bg-gray-700/40 hover:translate-x-0.5';
}

$dashActive     = (str_ends_with($currentUri, '/') || str_ends_with($currentUri, '/dashboard'))
    ? 'bg-red-600/15 text-red-400 nav-active-glow'
    : 'text-gray-400 hover:text-white hover:bg-gray-700/40 hover:translate-x-0.5';
?>
<!-- Mobile overlay -->
<div id="sidebar-overlay"
     class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm hidden lg:hidden"
     onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col
              bg-gray-950/90 backdrop-blur-md border-r border-gray-800/60
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out">

    <!-- Logo / Brand block -->
    <a href="<?= $base ?>/dashboard"
       class="flex items-center gap-3 px-6 py-5 border-b border-gray-800
              hover:bg-gray-800/30 transition-colors group">
        <div class="flex-shrink-0">
            <?php $monogramSize = 40; $monogramAnimate = true;
                  require __DIR__ . '/monogram.php'; ?>
        </div>
        <div class="min-w-0">
            <p class="brand-wordmark text-base leading-none">InkManager</p>
            <p class="brand-tagline mt-1">Studio System</p>
        </div>
    </a>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <p class="brand-tagline px-3 mb-2">Principal</p>

        <a href="<?= $base ?>/dashboard"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $dashActive ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="<?= $base ?>/clientes"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/clientes') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Clientes
        </a>

        <a href="<?= $base ?>/turnos"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/turnos') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Turnos
        </a>

        <div class="pt-4">
            <p class="brand-tagline px-3 mb-2">Estadísticas</p>
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all text-gray-600 cursor-not-allowed">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reportes <span class="ml-auto text-xs bg-gray-700 text-gray-500 px-1.5 py-0.5 rounded">Pronto</span>
            </a>
        </div>
    </nav>

    <!-- User info + logout -->
    <div class="px-3 py-4 border-t border-gray-800">
        <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-800">
            <div class="w-8 h-8 rounded-full bg-red-600/20 border border-red-600/40
                        flex items-center justify-center text-red-400 text-sm font-bold flex-shrink-0">
                <?= htmlspecialchars(strtoupper(substr($user['nombre'] ?? 'A', 0, 1))) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">
                    <?= htmlspecialchars($user['nombre'] ?? '') ?>
                </p>
                <p class="text-gray-500 text-xs truncate">
                    <?= htmlspecialchars($user['email'] ?? '') ?>
                </p>
            </div>
            <a href="<?= $base ?>/logout"
               title="Cerrar sesión"
               class="text-gray-600 hover:text-red-400 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</aside>
