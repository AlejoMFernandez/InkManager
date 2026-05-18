<!doctype html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — InkManager</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="<?= PUBLIC_URL ?>/assets/css/brand.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background:
                radial-gradient(ellipse 800px 500px at 75% 10%,  rgba(220,38,38,0.18), transparent 60%),
                radial-gradient(ellipse 600px 400px at 10% 90%,  rgba(76,29,149,0.12),  transparent 60%),
                #060608;
        }
        .ink-glow { box-shadow: 0 0 60px rgba(220,38,38,.20), 0 0 120px rgba(220,38,38,.06); }

        @keyframes loginIn {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }
        .login-enter { animation: loginIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) 0.3s both; }

        @keyframes leftPanelIn {
            from { opacity: 0; transform: translateX(-30px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .left-panel-enter { animation: leftPanelIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }

        @keyframes scanLine {
            0%   { transform: translateY(-100%); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }
        .scan-line {
            position: fixed; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(239,68,68,0.6), transparent);
            animation: scanLine 6s linear infinite;
            pointer-events: none;
        }
    </style>
</head>
<body class="h-full min-h-screen relative overflow-hidden">

<?php require APP_PATH . '/Views/partials/splash.php'; ?>

<?php
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
use App\Core\Auth;
$csrf = Auth::csrfToken();
?>

<!-- Decorative animated scan line -->
<div class="scan-line"></div>

<!-- Brand grid background -->
<div class="absolute inset-0 brand-grid pointer-events-none"></div>

<div class="relative min-h-screen flex flex-col lg:flex-row">

    <!-- ═════════════ LEFT PANEL — BRAND HERO ═════════════ -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 left-panel-enter">

        <!-- Top: brand mark -->
        <div class="flex items-center gap-3">
            <?php $monogramSize = 44; $monogramAnimate = true;
                  require APP_PATH . '/Views/partials/monogram.php'; ?>
            <div>
                <p class="brand-wordmark text-lg leading-none">InkManager</p>
                <p class="brand-tagline mt-1">Digital Studio System</p>
            </div>
        </div>

        <!-- Middle: hero text -->
        <div class="max-w-md">
            <p class="brand-tagline mb-4">// Version 1.0 — Studio Edition</p>
            <h1 class="font-display text-6xl text-white leading-none tracking-wider uppercase">
                Ink<br>
                <span class="text-red-500">Manager.</span>
            </h1>
            <p class="text-gray-400 text-sm mt-6 leading-relaxed">
                El sistema operativo para estudios de tatuaje modernos.
                Clientes, turnos y un body map 3D para marcar cada tatuaje
                en su lugar exacto.
            </p>

            <!-- Feature ticks -->
            <ul class="mt-8 space-y-2 text-xs text-gray-500">
                <li class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                    Body Map 3D con marcadores persistentes
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                    Calendario drag &amp; drop · reagendado en vivo
                </li>
                <li class="flex items-center gap-2">
                    <span class="w-1 h-1 bg-red-500 rounded-full"></span>
                    CRM de clientes con historial de tatuajes
                </li>
            </ul>
        </div>

        <!-- Bottom: meta -->
        <div class="flex items-center justify-between text-xs">
            <span class="brand-tagline">/01 — Access Terminal</span>
            <span class="status-pill">
                <span class="dot"></span>
                <span>System Online</span>
            </span>
        </div>
    </div>

    <!-- ═════════════ RIGHT PANEL — LOGIN FORM ═════════════ -->
    <div class="flex-1 flex items-center justify-center px-4 py-8 relative">

        <div class="w-full max-w-sm login-enter">

            <!-- Mobile logo (visible solo en mobile) -->
            <div class="lg:hidden flex flex-col items-center mb-8">
                <?php $monogramSize = 64; $monogramAnimate = true;
                      require APP_PATH . '/Views/partials/monogram.php'; ?>
                <h1 class="font-display text-3xl text-white tracking-widest uppercase mt-3">InkManager</h1>
                <p class="brand-tagline mt-1">Digital Studio System</p>
            </div>

            <!-- Flash error -->
            <?php foreach ($flash as $type => $msg): ?>
            <div class="flash-enter flex items-center gap-2 px-4 py-3 mb-4 rounded-lg border
                        bg-red-500/10 border-red-500/30 text-red-400 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endforeach; ?>

            <!-- Card -->
            <div class="corner-brackets bg-gray-900/60 backdrop-blur-xl border border-gray-800
                        rounded-2xl p-8 ink-glow">
                <span class="br-tl"></span><span class="br-br"></span>

                <div class="mb-6">
                    <p class="brand-tagline mb-2">/// Access</p>
                    <h2 class="font-display text-2xl text-white tracking-wider uppercase">Iniciar sesión</h2>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/login" class="space-y-4">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-400 mb-1.5
                                                   uppercase tracking-wider">
                            Email
                        </label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               required autocomplete="email"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700
                                      rounded-lg text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50
                                      focus:border-red-600/50 transition-all"
                               placeholder="admin@inkmanager.com">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium text-gray-400 mb-1.5
                                                      uppercase tracking-wider">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                   required autocomplete="current-password"
                                   class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700
                                          rounded-lg text-white text-sm placeholder-gray-600
                                          focus:outline-none focus:ring-2 focus:ring-red-600/50
                                          focus:border-red-600/50 pr-10 transition-all"
                                   placeholder="••••••••">
                            <button type="button" onclick="togglePwd()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                             9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full mt-2 py-3 px-4 font-display tracking-[0.25em] text-base
                                   bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                                   text-white rounded-lg uppercase
                                   focus:outline-none focus:ring-2 focus:ring-red-500/50
                                   active:scale-[0.98] transition-all
                                   shadow-lg shadow-red-900/40 hover:shadow-red-800/60">
                        Entrar al estudio →
                    </button>
                </form>
            </div>

            <p class="text-center text-gray-700 text-xs mt-6 brand-tagline">
                © <?= date('Y') ?> InkManager · All Rights Reserved
            </p>
        </div>
    </div>
</div>

<script>
    function togglePwd() {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>
