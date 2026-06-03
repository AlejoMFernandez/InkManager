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
        <p class="brand-tagline px-3 mb-2"><?= __('nav.section_main') ?></p>

        <a href="<?= $base ?>/dashboard"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= $dashActive ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <?= __('nav.dashboard') ?>
        </a>

        <a href="<?= $base ?>/clientes"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/clientes') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <?= __('nav.clients') ?>
        </a>

        <a href="<?= $base ?>/turnos"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/turnos') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <?= __('nav.appointments') ?>
        </a>

        <a href="<?= $base ?>/presupuestos"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/presupuestos') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <?= __('nav.quotes') ?>
        </a>

        <a href="<?= $base ?>/caja"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/caja') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342
                         1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375
                         c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414
                         .336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125
                         1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75
                         0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0
                         01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15
                         10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5z
                         m-12 0h.008v.008H6V10.5z"/>
            </svg>
            <?= __('nav.caja') ?>
        </a>

        <a href="<?= $base ?>/galeria"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/galeria') ?>">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5
                         1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5
                         0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5
                         1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0
                         1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
            <?= __('nav.galeria') ?>
        </a>

        <div class="pt-4">
            <p class="brand-tagline px-3 mb-2"><?= __('nav.section_stats') ?></p>
            <a href="<?= $base ?>/reportes"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/reportes') ?>">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <?= __('nav.reports') ?>
            </a>
        </div>

        <?php $userRol = $user['rol'] ?? 'staff'; ?>
        <div class="pt-4">
            <p class="brand-tagline px-3 mb-2">Sistema</p>

            <?php if ($userRol === 'owner'): ?>
            <!-- Staff — owner only -->
            <a href="<?= $base ?>/staff"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/staff') ?>">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0
                             0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <?= __('nav.staff') ?>
            </a>
            <?php endif; ?>

            <!-- Settings — owner and admin -->
            <?php if (in_array($userRol, ['owner', 'admin'], true)): ?>
            <a href="<?= $base ?>/configuracion"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all <?= navActive('/configuracion') ?>">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0
                             002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0
                             001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0
                             00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0
                             00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0
                             00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0
                             00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0
                             001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07
                             2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <?= __('nav.settings') ?>
            </a>
            <?php endif; ?>
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
               title="<?= htmlspecialchars(__('auth.logout')) ?>"
               class="text-gray-600 hover:text-red-400 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</aside>
