<!doctype html>
<html lang="es" class="h-full">
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
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .page-enter {
            animation: fadeUp 0.35s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* ── Staggered card entrance ────────────────────────── */
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0)  scale(1); }
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

        /* ── Flash message slide-in ─────────────────────────── */
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .flash-enter { animation: slideDown 0.25s ease both; }
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
                <p class="brand-tagline">Active Page</p>
                <h1 class="font-display text-lg text-white tracking-wider uppercase">
                    <?= htmlspecialchars($pageTitle ?? 'Studio') ?>
                </h1>
            </div>

            <!-- Right side: live status + date -->
            <div class="ml-auto flex items-center gap-4">
                <div class="hidden sm:flex status-pill">
                    <span class="dot"></span>
                    <span>Studio · Online</span>
                </div>
                <div class="hidden md:flex flex-col items-end leading-tight">
                    <span class="brand-tagline">Date</span>
                    <span class="text-xs text-gray-300 font-mono"><?= date('d M Y') ?></span>
                </div>
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
                <span>Digital Studio System v1.0</span>
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
    <?= $extraScripts ?? '' ?>
</body>
</html>
