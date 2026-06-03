<!doctype html>
<html lang="<?= __('layout.html_lang') ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'InkManager') ?> — InkManager</title>

    <!-- Google Fonts: Bebas Neue (display) + Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- Brand identity stylesheet -->
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>/assets/css/brand.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: { 50:'#fef2f2', 500:'#ef4444', 600:'#dc2626', 700:'#b91c1c' }
                    }
                }
            }
        }
    </script>

    <!-- Custom scrollbar, keyframes & micro-animations -->
    <style>
        /* ── Scrollbar ──────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* ── Default transitions (selective — skip layout) ──── */
        a, button,
        [class*="hover:"] {
            transition: color 150ms ease, background-color 150ms ease,
                        border-color 150ms ease, opacity 150ms ease,
                        box-shadow 150ms ease, transform 150ms ease;
        }
        .no-transition, .no-transition * { transition: none !important; }

        /* ── Page-enter animation ───────────────────────────── */
        /* IMPORTANTE: el keyframe "to" NO incluye transform para que
           position:fixed funcione correctamente en hijos (modals, toasts).
           CSS fill:both + transform en "to" crea un stacking context permanente
           que hace que fixed se posicione relativo a <main> en lugar del viewport. */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; }
        }
        .page-enter {
            animation: fadeUp 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* ── Staggered card entrance ────────────────────────── */
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to   { opacity: 1; }
        }
        .card-in {
            animation: cardIn 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .card-in:nth-child(1) { animation-delay: 0ms; }
        .card-in:nth-child(2) { animation-delay: 60ms; }
        .card-in:nth-child(3) { animation-delay: 120ms; }
        .card-in:nth-child(4) { animation-delay: 180ms; }
        .card-in:nth-child(5) { animation-delay: 240ms; }

        /* ── KPI counter roll-up ────────────────────────────── */
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }

        /* ── Glow button ────────────────────────────────────── */
        .btn-glow:hover {
            box-shadow: 0 0 18px rgba(220, 38, 38, 0.4);
        }

        /* ── KPI card hover top-border glow ─────────────────── */
        .kpi-card {
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, transparent 60%, rgba(220,38,38,0.35));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 300ms ease;
            pointer-events: none;
        }
        .kpi-card:hover::before { opacity: 1; }

        /* ── Table row left-accent on hover ─────────────────── */
        /* Uses box-shadow on first td (pseudo won't work on tr in many browsers) */
        .table-row-hover:hover td:first-child {
            box-shadow: inset 2px 0 0 #dc2626;
        }

        /* ── Sidebar nav glow on active ─────────────────────── */
        .nav-active-glow {
            box-shadow: inset 3px 0 0 #ef4444;
        }

        /* ── Shimmer placeholder ────────────────────────────── */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position:  400px 0; }
        }

        /* ── Command palette ────────────────────────────────── */
        @keyframes kpalIn {
            from { opacity: 0; transform: translateY(-10px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)     scale(1);    }
        }
        @keyframes kpalBgIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        #kpal-backdrop      { animation: kpalBgIn 0.14s ease both; }
        #kpal-panel         { animation: kpalIn   0.20s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .kpal-item          { transition: background 80ms ease; }
        .kpal-item:hover,
        .kpal-item-active   { background: rgba(255,255,255,0.045) !important; }

        /* ── Notif panel slide-in ───────────────────────────── */
        @keyframes panelIn {
            from { opacity: 0; transform: translateY(-6px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .notif-panel-open {
            animation: panelIn 0.18s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* ── Toast notifications ────────────────────────────── */
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(calc(100% + 1.5rem)); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes toastOut {
            0%   { opacity: 1; transform: translateX(0);                   max-height: 120px; margin-top: 0; }
            100% { opacity: 0; transform: translateX(calc(100% + 1.5rem)); max-height: 0;     margin-top: calc(-1 * var(--gap, 8px)); }
        }
        @keyframes toastBar {
            from { width: 100%; }
            to   { width: 0; }
        }
        .toast-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 11px 14px 13px;
            border-radius: 14px;
            background: rgba(8, 8, 18, 0.97);
            border: 1px solid var(--t-border, rgba(255,255,255,.1));
            box-shadow: 0 8px 32px rgba(0,0,0,.6), 0 2px 8px rgba(0,0,0,.4);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            position: relative; overflow: hidden;
            width: clamp(220px, 310px, calc(100vw - 2.5rem));
            animation: toastIn .42s cubic-bezier(.22,1,.36,1) both;
            pointer-events: auto;
        }
        .toast-item.toast-leaving {
            animation: toastOut .32s cubic-bezier(.4,0,1,1) forwards;
        }
        .toast-close:hover { opacity: .9 !important; }
        .toast-bar {
            position: absolute; bottom: 0; left: 0;
            height: 2px; border-radius: 0 0 0 14px;
            animation: toastBar linear forwards;
        }
    </style>
    <?= $extraHead ?? '' ?>
</head>
<body class="h-full text-gray-100 antialiased">

    <?php require APP_PATH . '/Views/partials/splash.php'; ?>
    <?php require APP_PATH . '/Views/partials/sidebar.php'; ?>

    <!-- Main wrapper (offset for sidebar on desktop) -->
    <div class="lg:pl-64 flex flex-col min-h-screen">

        <!-- Top bar -->
        <header class="sticky top-0 z-10 flex items-center gap-4 px-4 sm:px-6
                        h-16 bg-gray-950/70 backdrop-blur-md
                        border-b border-gray-800/80
                        shadow-[0_1px_0_0_rgba(220,38,38,0.08)]">
            <!-- Hamburger (mobile) -->
            <button onclick="toggleSidebar()"
                    class="lg:hidden text-gray-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Brand wordmark (mobile) -->
            <div class="lg:hidden flex items-center gap-2">
                <?php $monogramSize = 28; $monogramAnimate = false;
                      require APP_PATH . '/Views/partials/monogram.php'; ?>
                <span class="brand-wordmark">InkManager</span>
            </div>

            <!-- Page title (desktop) -->
            <div class="hidden lg:flex flex-col leading-tight">
                <p class="brand-tagline"><?= __('layout.active_page') ?></p>
                <h1 class="font-display text-lg text-white tracking-wider uppercase">
                    <?= htmlspecialchars($pageTitle ?? 'Studio') ?>
                </h1>
            </div>

            <!-- Right side: live status + date + lang switcher -->
            <div class="ml-auto flex items-center gap-3">
                <div class="hidden sm:flex status-pill">
                    <span class="dot"></span>
                    <span><?= __('layout.status_online') ?></span>
                </div>
                <div class="hidden md:flex flex-col items-end leading-tight">
                    <span class="brand-tagline"><?= __('layout.date_label') ?></span>
                    <span class="text-xs text-gray-300 font-mono"><?= date('d M Y') ?></span>
                </div>

                <!-- Search trigger pill -->
                <button onclick="window.openCommandPalette && window.openCommandPalette()"
                        class="hidden md:flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-lg
                               bg-gray-800 border border-gray-700 hover:border-gray-600
                               text-gray-500 hover:text-gray-300
                               text-xs transition-colors select-none"
                        title="Búsqueda global (Ctrl+K)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <span>Buscar…</span>
                    <kbd class="flex items-center gap-0.5 px-1 py-0.5 rounded
                                border border-gray-700 bg-gray-900/60
                                text-[9px] font-mono text-gray-600 leading-none">
                        ⌘K
                    </kbd>
                </button>

                <!-- Notifications bell -->
                <div class="relative" id="notif-wrap">
                    <button id="notif-btn" aria-label="Notificaciones"
                            class="relative flex items-center justify-center w-9 h-9 rounded-lg
                                   bg-gray-800 border border-gray-700 hover:border-gray-600
                                   text-gray-400 hover:text-white transition-colors select-none">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor"
                             stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118
                                     9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64
                                     3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714
                                     0a3 3 0 11-5.714 0"/>
                        </svg>
                        <!-- Badge -->
                        <span id="notif-badge"
                              class="hidden absolute -top-1.5 -right-1.5
                                     min-w-[17px] h-[17px] px-[3px]
                                     inline-flex items-center justify-center
                                     bg-red-500 text-white text-[9px] font-bold leading-none
                                     rounded-full border-2 border-gray-950
                                     select-none pointer-events-none">0</span>
                    </button>

                    <!-- Dropdown panel -->
                    <div id="notif-panel"
                         class="hidden absolute right-0 mt-1 w-80 rounded-xl
                                border border-gray-700/80 z-50
                                overflow-hidden shadow-2xl shadow-black/60"
                         style="top:calc(100% + 6px);
                                background:rgba(10,11,18,0.97);
                                backdrop-filter:blur(20px);
                                -webkit-backdrop-filter:blur(20px);
                                max-height:calc(100vh - 88px);
                                overflow-y:auto">

                        <!-- Panel header (sticky) -->
                        <div class="sticky top-0 z-10 flex items-center justify-between
                                    px-4 py-3 border-b border-gray-800/80"
                             style="background:rgba(10,11,18,0.98)">
                            <span class="text-sm font-semibold text-white tracking-tight">
                                Notificaciones
                            </span>
                            <span id="notif-total-label"
                                  class="text-[11px] text-gray-600 tabular-nums"></span>
                        </div>

                        <!-- Loading state -->
                        <div id="notif-loading"
                             class="flex flex-col items-center justify-center gap-2.5 py-9 text-gray-600">
                            <div class="w-5 h-5 border-2 border-gray-700 border-t-red-500
                                        rounded-full animate-spin"></div>
                            <span class="text-xs">Cargando…</span>
                        </div>

                        <!-- Groups container -->
                        <div id="notif-groups" class="hidden pb-2"></div>

                        <!-- All-clear state -->
                        <div id="notif-empty"
                             class="hidden flex-col items-center justify-center gap-2 py-9 text-gray-600">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor"
                                 stroke-width="1.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm font-medium">Todo al día</span>
                            <span class="text-xs text-gray-700">Sin notificaciones pendientes</span>
                        </div>
                    </div>
                </div>

                <!-- Language switcher -->
                <a href="<?= BASE_URL ?>/lang/<?= __('lang.other_code') ?>"
                   title="<?= htmlspecialchars(__('lang.switch_title')) ?>"
                   class="flex items-center gap-1 px-2 py-1 rounded-md
                          bg-gray-800 border border-gray-700 hover:border-gray-600
                          text-gray-400 hover:text-white text-xs font-mono font-semibold
                          transition-colors select-none">
                    <?= __('lang.current') ?>
                    <span class="text-gray-600">/</span>
                    <span class="text-gray-600 hover:text-gray-400"><?= __('lang.other') ?></span>
                </a>
            </div>
        </header>

        <!-- Page content -->
        <main class="flex-1 px-4 sm:px-6 py-6 page-enter">
            <?php require APP_PATH . '/Views/partials/flash.php'; ?>
            <?= $content ?>
        </main>

        <footer class="px-6 py-4 border-t border-gray-800/60 flex items-center justify-between
                       text-xs text-gray-700">
            <div class="flex items-center gap-3">
                <?php $monogramSize = 16; $monogramAnimate = false;
                      require APP_PATH . '/Views/partials/monogram.php'; ?>
                <span class="brand-wordmark text-xs">InkManager</span>
                <span class="text-gray-800">·</span>
                <span><?= __('layout.footer_version') ?></span>
            </div>
            <div class="hidden sm:flex items-center gap-2">
                <span class="brand-tagline">© <?= date('Y') ?></span>
            </div>
        </footer>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebar-overlay');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            sidebar.classList.toggle('-translate-x-full', !isHidden);
            overlay.classList.toggle('hidden', !isHidden);
        }
    </script>

    <!-- ── Command palette (⌘K / Ctrl+K) ───────────────────────────────── -->
    <div id="kpal-backdrop"
         class="hidden fixed inset-0 z-[200] flex items-start justify-center pt-[13vh] px-4"
         style="background:rgba(0,0,0,0.72);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px)">

        <div id="kpal-panel"
             class="w-full max-w-xl rounded-2xl border border-gray-700/80 overflow-hidden shadow-2xl shadow-black/70"
             style="background:rgba(8,9,20,0.98);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
                    max-height:80vh;display:flex;flex-direction:column">

            <!-- Search bar -->
            <div class="flex items-center gap-3 px-4 border-b border-gray-800/80"
                 style="padding-top:14px;padding-bottom:14px">
                <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input id="kpal-input" type="text" autocomplete="off" spellcheck="false"
                       placeholder="Buscar clientes, turnos, presupuestos…"
                       class="flex-1 bg-transparent text-white text-sm placeholder-gray-600
                              outline-none caret-red-500 min-w-0">
                <kbd class="hidden sm:flex items-center gap-0.5 px-1.5 py-0.5 rounded
                            border border-gray-700 bg-gray-800/60
                            text-[10px] text-gray-500 font-mono leading-none select-none flex-shrink-0">
                    ESC
                </kbd>
            </div>

            <!-- Results area -->
            <div id="kpal-results" class="overflow-y-auto" style="flex:1;min-height:0">

                <!-- Hint (initial empty state) -->
                <div id="kpal-hint" class="flex flex-col items-center justify-center gap-2 py-10 text-gray-700">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <span class="text-xs">Escribí al menos 2 caracteres para buscar</span>
                </div>

                <!-- Loading -->
                <div id="kpal-loading" class="hidden flex-col items-center justify-center gap-2.5 py-10">
                    <div class="w-5 h-5 border-2 border-gray-700 border-t-red-500 rounded-full animate-spin"></div>
                </div>

                <!-- No results -->
                <div id="kpal-empty" class="hidden flex-col items-center justify-center gap-2 py-10 text-gray-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198
                                 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9
                                 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625
                                 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375
                                 0h.008v.015h-.008V9.75z"/>
                    </svg>
                    <span id="kpal-empty-msg" class="text-sm">Sin resultados</span>
                </div>

                <!-- Groups (rendered by JS) -->
                <div id="kpal-groups" class="hidden pb-2"></div>
            </div>

            <!-- Footer hints -->
            <div class="flex items-center gap-5 px-4 py-2.5 border-t border-gray-800/60
                        text-[10px] text-gray-700 select-none">
                <span class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded border border-gray-700 bg-gray-800/60 font-mono">↑↓</kbd>
                    Navegar
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded border border-gray-700 bg-gray-800/60 font-mono">↵</kbd>
                    Ir
                </span>
                <span class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded border border-gray-700 bg-gray-800/60 font-mono">Esc</kbd>
                    Cerrar
                </span>
                <span class="ml-auto" id="kpal-count"></span>
            </div>
        </div>
    </div>

    <!-- ── Toast container (viewport, no afectado por sidebar offset) ── -->
    <div id="toast-container"
         role="region" aria-label="Notificaciones"
         style="position:fixed;bottom:1.25rem;right:1rem;z-index:9999;
                display:flex;flex-direction:column;gap:8px;
                align-items:flex-end;pointer-events:none;
                max-width:min(320px,calc(100vw - 2rem))">
    </div>

    <script>
    (function () {
        var CONF = {
            success: { bar:'#22c55e', border:'rgba(34,197,94,.28)',  icon:'M5 13l4 4L19 7' },
            error:   { bar:'#ef4444', border:'rgba(239,68,68,.28)',  icon:'M6 18L18 6M6 6l12 12' },
            warning: { bar:'#eab308', border:'rgba(234,179,8,.28)',  icon:'M12 9v3m0 4h.01' },
            info:    { bar:'#3b82f6', border:'rgba(59,130,246,.28)', icon:'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        };

        window.showToast = function (msg, type, ms) {
            type = type || 'info';
            ms   = ms   || 4200;
            var c   = CONF[type] || CONF.info;
            var box = document.getElementById('toast-container');
            if (!box) return;

            var el = document.createElement('div');
            el.className = 'toast-item';
            el.style.setProperty('--t-border', c.border);
            el.innerHTML =
                '<svg style="flex-shrink:0;margin-top:1px" width="16" height="16" viewBox="0 0 24 24" fill="none"' +
                ' stroke="' + c.bar + '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="' + c.icon + '"/></svg>' +
                '<span style="font-size:.8rem;line-height:1.5;color:#e5e7eb;flex:1;word-break:break-word">' + msg + '</span>' +
                '<button class="toast-close" aria-label="Cerrar"' +
                ' style="flex-shrink:0;line-height:1;background:none;border:none;cursor:pointer;opacity:.35;color:#9ca3af;padding:0 0 0 4px;margin-top:1px">' +
                    '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '<div class="toast-bar" style="background:' + c.bar + ';animation-duration:' + ms + 'ms"></div>';

            function bye() {
                if (el._out) return;
                el._out = true;
                el.classList.add('toast-leaving');
                el.addEventListener('animationend', function () { el.remove(); }, { once: true });
            }
            el.querySelector('.toast-close').addEventListener('click', bye);
            box.appendChild(el);
            setTimeout(bye, ms);
        };
    })();
    </script>

    <!-- ── Notification bell ─────────────────────────────────────────────── -->
    <script>
    (function () {
        var btn       = document.getElementById('notif-btn');
        var panel     = document.getElementById('notif-panel');
        var badge     = document.getElementById('notif-badge');
        var wrap      = document.getElementById('notif-wrap');
        var groupsEl  = document.getElementById('notif-groups');
        var emptyEl   = document.getElementById('notif-empty');
        var loadingEl = document.getElementById('notif-loading');
        var totalLbl  = document.getElementById('notif-total-label');

        if (!btn || !panel) return;

        var isOpen   = false;
        var lastData = null;
        var NOTIF_URL = <?= json_encode(BASE_URL . '/api/notificaciones') ?>;

        // ── SVG icons per group type ─────────────────────────────────────
        var ICONS = {
            turnos_hoy: '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>',
            presupuestos: '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>',
            cumpleanos: '<svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-1.5-.454M9 6.75V4.5m3 2.25V4.5m3 2.25V4.5M5.25 19.5h13.5a2.25 2.25 0 002.25-2.25V8.25a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 8.25v9a2.25 2.25 0 002.25 2.25z"/></svg>',
        };
        var COLORS = { blue:'text-blue-400', yellow:'text-yellow-400', pink:'text-pink-400' };

        // ── Safe HTML escaping ───────────────────────────────────────────
        var _escDiv = document.createElement('div');
        function esc(s) { _escDiv.textContent = s || ''; return _escDiv.innerHTML; }

        // ── Render ───────────────────────────────────────────────────────
        function render(data) {
            lastData = data;
            var n = data.total || 0;

            // Badge
            if (n > 0) {
                badge.textContent = n > 99 ? '99+' : n;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
            totalLbl.textContent = n > 0 ? n + ' pendiente' + (n !== 1 ? 's' : '') : '';

            loadingEl.style.display = 'none';

            if (!data.groups || data.groups.length === 0) {
                groupsEl.classList.add('hidden');
                emptyEl.style.display = 'flex';
                emptyEl.classList.remove('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            emptyEl.style.display = '';
            groupsEl.classList.remove('hidden');
            groupsEl.innerHTML = '';

            data.groups.forEach(function (g, gi) {
                var color = COLORS[g.color] || 'text-gray-400';
                var icon  = ICONS[g.type]  || '';

                // Group header
                var hdr = document.createElement('div');
                hdr.className = 'flex items-center gap-2 px-4 pt-3.5 pb-1.5';
                hdr.innerHTML =
                    '<span class="' + color + '">' + icon + '</span>' +
                    '<span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider flex-1">' +
                        esc(g.label) + '</span>' +
                    '<span class="text-[10px] text-gray-700">' + g.count + '</span>';
                groupsEl.appendChild(hdr);

                // Items
                g.items.forEach(function (item) {
                    var a = document.createElement('a');
                    a.href      = item.url;
                    a.className = 'flex items-center gap-3 px-4 py-2.5 ' +
                                  'hover:bg-white/[0.04] transition-colors duration-100 no-transition';

                    var iniLetter = item.label ? esc(item.label.charAt(0).toUpperCase()) : '?';
                    var avatarHtml = item.avatar
                        ? '<img src="' + item.avatar + '" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-800">'
                        : '<div class="w-8 h-8 rounded-full flex-shrink-0 bg-gray-800 border border-gray-700/60' +
                          ' flex items-center justify-center">' +
                          '<span class="text-gray-400 text-xs font-semibold">' + iniLetter + '</span></div>';

                    a.innerHTML = avatarHtml +
                        '<div class="flex-1 min-w-0">' +
                            '<p class="text-[13px] text-gray-200 truncate leading-tight">' + esc(item.label) + '</p>' +
                            '<p class="text-[11px] text-gray-500 truncate mt-0.5">' + esc(item.sublabel) + '</p>' +
                        '</div>' +
                        '<svg class="w-3.5 h-3.5 text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>';
                    groupsEl.appendChild(a);
                });

                // Divider between groups (not after the last)
                if (gi < data.groups.length - 1) {
                    var sep = document.createElement('div');
                    sep.className = 'h-px bg-gray-800/50 mx-4 mt-2.5';
                    groupsEl.appendChild(sep);
                }
            });
        }

        // ── Fetch ────────────────────────────────────────────────────────
        function fetchNotifs() {
            fetch(NOTIF_URL, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) { render(d); })
                .catch(function () {
                    loadingEl.style.display = 'none';
                    if (!lastData) {
                        emptyEl.style.display = 'flex';
                        emptyEl.classList.remove('hidden');
                    }
                });
        }

        // ── Open / close ─────────────────────────────────────────────────
        function openPanel() {
            isOpen = true;
            panel.classList.remove('hidden');
            panel.classList.add('notif-panel-open');
            btn.classList.add('border-gray-600', 'bg-gray-700', 'text-white');
            if (!lastData) fetchNotifs();
        }

        function closePanel() {
            isOpen = false;
            panel.classList.add('hidden');
            panel.classList.remove('notif-panel-open');
            btn.classList.remove('border-gray-600', 'bg-gray-700', 'text-white');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            isOpen ? closePanel() : openPanel();
        });

        document.addEventListener('click', function (e) {
            if (isOpen && !wrap.contains(e.target)) closePanel();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) closePanel();
        });

        // Initial silent fetch (populates badge without opening panel)
        fetchNotifs();

        // Poll every 60 s
        setInterval(fetchNotifs, 60000);
    })();
    </script>

    <!-- ── Command palette JS ───────────────────────────────────────────── -->
    <script>
    (function () {
        var backdrop  = document.getElementById('kpal-backdrop');
        var input     = document.getElementById('kpal-input');
        var groupsEl  = document.getElementById('kpal-groups');
        var hintEl    = document.getElementById('kpal-hint');
        var loadingEl = document.getElementById('kpal-loading');
        var emptyEl   = document.getElementById('kpal-empty');
        var emptyMsg  = document.getElementById('kpal-empty-msg');
        var countEl   = document.getElementById('kpal-count');

        if (!backdrop || !input) return;

        var isOpen    = false;
        var debTimer  = null;
        var active    = -1;
        var flatItems = [];   // [{el, url}, …]
        var SEARCH_URL = <?= json_encode(BASE_URL . '/api/busqueda') ?>;

        // ── Status badge colours ─────────────────────────────────────────
        var SBADGE = {
            agendado:   'bg-blue-500/20 text-blue-300',
            confirmado: 'bg-emerald-500/20 text-emerald-300',
            completado: 'bg-gray-500/20 text-gray-400',
            cancelado:  'bg-red-500/20 text-red-400',
            borrador:   'bg-gray-500/20 text-gray-500',
            enviado:    'bg-blue-500/20 text-blue-300',
            aceptado:   'bg-emerald-500/20 text-emerald-300',
            rechazado:  'bg-red-500/20 text-red-400',
        };

        // ── Group icons ──────────────────────────────────────────────────
        var GICONS = {
            clientes:      '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>',
            turnos:        '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>',
            presupuestos:  '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>',
        };

        // ── HTML escaping ────────────────────────────────────────────────
        var _ed = document.createElement('div');
        function esc(s) { _ed.textContent = s || ''; return _ed.innerHTML; }

        // ── Open / close ─────────────────────────────────────────────────
        function open() {
            isOpen = true;
            backdrop.classList.remove('hidden');
            active    = -1;
            flatItems = [];
            showHint();
            input.value = '';
            countEl.textContent = '';
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(function () { input.focus(); });
        }

        function close() {
            isOpen = false;
            backdrop.classList.add('hidden');
            active    = -1;
            flatItems = [];
            document.body.style.overflow = '';
            if (debTimer) { clearTimeout(debTimer); debTimer = null; }
        }

        // ── States ───────────────────────────────────────────────────────
        function showHint() {
            hintEl.style.display    = 'flex';
            loadingEl.style.display = 'none';
            emptyEl.style.display   = 'none';
            groupsEl.classList.add('hidden');
        }
        function showLoading() {
            hintEl.style.display    = 'none';
            loadingEl.style.display = 'flex';
            emptyEl.style.display   = 'none';
            groupsEl.classList.add('hidden');
        }
        function showEmpty(q) {
            emptyMsg.textContent    = 'Sin resultados para "' + q + '"';
            hintEl.style.display    = 'none';
            loadingEl.style.display = 'none';
            emptyEl.style.display   = 'flex';
            groupsEl.classList.add('hidden');
        }
        function showGroups() {
            hintEl.style.display    = 'none';
            loadingEl.style.display = 'none';
            emptyEl.style.display   = 'none';
            groupsEl.classList.remove('hidden');
        }

        // ── Active-item management ───────────────────────────────────────
        function setActive(idx) {
            flatItems.forEach(function (it) {
                it.el.classList.remove('kpal-item-active');
            });
            active = idx;
            if (idx >= 0 && idx < flatItems.length) {
                var el = flatItems[idx].el;
                el.classList.add('kpal-item-active');
                el.scrollIntoView({ block: 'nearest' });
            }
        }

        function moveActive(dir) {
            if (!flatItems.length) return;
            var next = active + dir;
            if (next < 0)              next = flatItems.length - 1;
            if (next >= flatItems.length) next = 0;
            setActive(next);
        }

        // ── Render results ───────────────────────────────────────────────
        function render(data, q) {
            groupsEl.innerHTML = '';
            flatItems = [];

            if (!data.groups || !data.groups.length) {
                showEmpty(q); return;
            }

            countEl.textContent = data.total + ' resultado' + (data.total !== 1 ? 's' : '');

            data.groups.forEach(function (g) {
                // Group header
                var hdr = document.createElement('div');
                hdr.className = 'flex items-center gap-2 px-4 pt-4 pb-1.5 text-gray-500';
                hdr.innerHTML =
                    '<span>' + (GICONS[g.type] || '') + '</span>' +
                    '<span class="text-[10px] font-semibold uppercase tracking-wider flex-1">' + esc(g.label) + '</span>' +
                    '<span class="text-[10px] text-gray-700">' + g.items.length + '</span>';
                groupsEl.appendChild(hdr);

                // Items
                g.items.forEach(function (item) {
                    var a = document.createElement('a');
                    a.href      = item.url;
                    a.className = 'kpal-item flex items-center gap-3 px-4 py-2.5 cursor-pointer no-transition';

                    // Avatar / initial
                    var avHtml;
                    if (item.avatar) {
                        avHtml = '<img src="' + esc(item.avatar) + '" alt="" ' +
                                 'class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-800">';
                    } else {
                        avHtml = '<div class="w-8 h-8 rounded-full flex-shrink-0 ' +
                                 'bg-gray-800 border border-gray-700/60 ' +
                                 'flex items-center justify-center">' +
                                 '<span class="text-gray-400 text-xs font-semibold">' + esc(item.initial || '?') + '</span>' +
                                 '</div>';
                    }

                    // Badge
                    var badgeHtml = '';
                    if (item.badge) {
                        var bc = SBADGE[item.badge] || 'bg-gray-500/20 text-gray-400';
                        badgeHtml = '<span class="' + bc + ' text-[10px] font-medium px-1.5 py-0.5 rounded-full flex-shrink-0">' +
                                    esc(item.badge) + '</span>';
                    }

                    a.innerHTML = avHtml +
                        '<div class="flex-1 min-w-0">' +
                            '<p class="text-[13px] text-gray-200 truncate leading-tight">' + esc(item.label) + '</p>' +
                            (item.sublabel
                                ? '<p class="text-[11px] text-gray-500 truncate mt-0.5">' + esc(item.sublabel) + '</p>'
                                : '') +
                        '</div>' +
                        badgeHtml +
                        '<svg class="w-3.5 h-3.5 text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>';

                    groupsEl.appendChild(a);
                    flatItems.push({ el: a, url: item.url });

                    // Mouseover highlight (closure captures final flatItems length - 1)
                    (function (idx) {
                        a.addEventListener('mouseenter', function () { setActive(idx); });
                    }(flatItems.length - 1));
                });
            });

            showGroups();
        }

        // ── Fetch ────────────────────────────────────────────────────────
        function doSearch(q) {
            showLoading();
            fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (d) { render(d, q); })
                .catch(function () { showEmpty(q); });
        }

        // ── Input handler with debounce ───────────────────────────────────
        input.addEventListener('input', function () {
            var q = input.value.trim();
            countEl.textContent = '';
            if (debTimer) clearTimeout(debTimer);
            if (q.length < 2) { active = -1; flatItems = []; showHint(); return; }
            debTimer = setTimeout(function () { doSearch(q); }, 250);
        });

        // ── Global keyboard shortcuts ─────────────────────────────────────
        document.addEventListener('keydown', function (e) {
            // Open / toggle
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                isOpen ? close() : open();
                return;
            }
            if (!isOpen) return;

            switch (e.key) {
                case 'Escape':    e.preventDefault(); close(); break;
                case 'ArrowDown': e.preventDefault(); moveActive(1);  break;
                case 'ArrowUp':   e.preventDefault(); moveActive(-1); break;
                case 'Enter':
                    if (active >= 0 && flatItems[active]) {
                        e.preventDefault();
                        window.location.href = flatItems[active].url;
                    }
                    break;
            }
        });

        // ── Close on backdrop click ───────────────────────────────────────
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) close();
        });

        // ── Expose for external triggers (e.g. a search button) ──────────
        window.openCommandPalette = open;
    })();
    </script>

    <?= $extraScripts ?? '' ?>
</body>
</html>
