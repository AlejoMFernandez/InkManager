<!doctype html>
<html lang="<?= \App\Core\Lang::current() ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear estudio — InkManager</title>

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
            10%  { opacity: 1; } 90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }
        .scan-line {
            position: fixed; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(239,68,68,0.6), transparent);
            animation: scanLine 6s linear infinite; pointer-events: none;
        }
    </style>
</head>
<body class="h-full min-h-screen relative overflow-hidden">

<?php require APP_PATH . '/Views/partials/splash.php'; ?>

<?php
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
use App\Core\Auth;
$csrf = Auth::csrfToken();
?>

<div class="scan-line"></div>
<div class="absolute inset-0 brand-grid pointer-events-none"></div>

<div class="relative min-h-screen flex flex-col lg:flex-row">

    <!-- ═════════════ LEFT PANEL — BRAND HERO ═════════════ -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-12 left-panel-enter">
        <div class="flex items-center gap-3">
            <?php $monogramSize = 44; $monogramAnimate = true;
                  require APP_PATH . '/Views/partials/monogram.php'; ?>
            <div>
                <p class="brand-wordmark text-lg leading-none">InkManager</p>
                <p class="brand-tagline mt-1">Digital Studio System</p>
            </div>
        </div>

        <div class="max-w-md">
            <p class="brand-tagline mb-4">// New Studio — Free Plan</p>
            <h1 class="font-display text-6xl text-white leading-none tracking-wider uppercase">
                Abrí tu<br>
                <span class="text-red-500">Estudio.</span>
            </h1>
            <p class="text-gray-400 text-sm mt-6 leading-relaxed">
                Creá tu estudio en segundos. El plan Free incluye hasta 50 clientes
                y todas las herramientas principales sin costo.
            </p>
            <ul class="mt-8 space-y-3 text-xs">
                <li class="flex items-start gap-3">
                    <span class="w-4 h-4 rounded bg-green-500/20 border border-green-500/30
                                 flex-shrink-0 flex items-center justify-center mt-0.5">
                        <svg class="w-2.5 h-2.5 text-green-400" fill="none" stroke="currentColor"
                             stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="text-gray-400">Hasta <strong class="text-white">50 clientes</strong> en el plan Free</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-4 h-4 rounded bg-green-500/20 border border-green-500/30
                                 flex-shrink-0 flex items-center justify-center mt-0.5">
                        <svg class="w-2.5 h-2.5 text-green-400" fill="none" stroke="currentColor"
                             stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="text-gray-400">Body Map 3D, calendario y CRM <strong class="text-white">completos</strong></span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-4 h-4 rounded bg-green-500/20 border border-green-500/30
                                 flex-shrink-0 flex items-center justify-center mt-0.5">
                        <svg class="w-2.5 h-2.5 text-green-400" fill="none" stroke="currentColor"
                             stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="text-gray-400">Sin tarjeta de crédito</span>
                </li>
            </ul>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="brand-tagline">/01 — New Studio</span>
            <span class="status-pill">
                <span class="dot"></span>
                <span>Free Plan · Open</span>
            </span>
        </div>
    </div>

    <!-- ═════════════ RIGHT PANEL — REGISTER FORM ═════════════ -->
    <div class="flex-1 flex items-center justify-center px-4 py-8 relative">
        <div class="w-full max-w-sm login-enter">

            <!-- Mobile logo -->
            <div class="lg:hidden flex flex-col items-center mb-8">
                <?php $monogramSize = 56; $monogramAnimate = true;
                      require APP_PATH . '/Views/partials/monogram.php'; ?>
                <h1 class="font-display text-3xl text-white tracking-widest uppercase mt-3">InkManager</h1>
                <p class="brand-tagline mt-1">Crear nuevo estudio</p>
            </div>

            <!-- Flash messages -->
            <?php foreach ($flash as $type => $msg): ?>
            <div class="flex items-center gap-2 px-4 py-3 mb-4 rounded-lg border
                        <?= $type === 'error'
                            ? 'bg-red-500/10 border-red-500/30 text-red-400'
                            : 'bg-green-500/10 border-green-500/30 text-green-400' ?>
                        text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="<?= $type === 'error' ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' ?>"/>
                </svg>
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endforeach; ?>

            <!-- Card -->
            <div class="corner-brackets bg-gray-900/60 backdrop-blur-xl border border-gray-800
                        rounded-2xl p-8 ink-glow">
                <span class="br-tl"></span><span class="br-br"></span>

                <div class="mb-6">
                    <p class="brand-tagline mb-2">/// New Studio</p>
                    <h2 class="font-display text-2xl text-white tracking-wider uppercase">
                        Crear estudio
                    </h2>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/registro" class="space-y-4">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <!-- Studio name -->
                    <div>
                        <label for="studio_nombre"
                               class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            Nombre del estudio <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="studio_nombre" name="studio_nombre"
                               value="<?= htmlspecialchars($old['studio_nombre'] ?? '') ?>"
                               required maxlength="100" autocomplete="organization"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700
                                      rounded-lg text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50
                                      focus:border-red-600/50 transition-all"
                               placeholder="Dark Ink Studio">
                    </div>

                    <!-- Separator -->
                    <div class="relative py-1">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-800"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-gray-900/60 px-2 text-xs text-gray-600 brand-tagline">Tu cuenta</span>
                        </div>
                    </div>

                    <!-- User name -->
                    <div>
                        <label for="nombre"
                               class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            Tu nombre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nombre" name="nombre"
                               value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                               required autocomplete="name"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700
                                      rounded-lg text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50
                                      focus:border-red-600/50 transition-all"
                               placeholder="Juan Pérez">
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email"
                               class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                               required autocomplete="email"
                               class="w-full px-4 py-2.5 bg-gray-900/60 border border-gray-700
                                      rounded-lg text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50
                                      focus:border-red-600/50 transition-all"
                               placeholder="vos@estudio.com">
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="password"
                                   class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                       required minlength="8" autocomplete="new-password"
                                       class="w-full px-3 py-2.5 bg-gray-900/60 border border-gray-700
                                              rounded-lg text-white text-sm placeholder-gray-600
                                              focus:outline-none focus:ring-2 focus:ring-red-600/50
                                              focus:border-red-600/50 pr-9 transition-all"
                                       placeholder="········">
                                <button type="button" onclick="togglePwd('password')"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                         stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268
                                                 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                                                 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="password_confirm"
                                   class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                                Confirmar <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="password_confirm" name="password_confirm"
                                   required minlength="8" autocomplete="new-password"
                                   class="w-full px-3 py-2.5 bg-gray-900/60 border border-gray-700
                                          rounded-lg text-white text-sm placeholder-gray-600
                                          focus:outline-none focus:ring-2 focus:ring-red-600/50
                                          focus:border-red-600/50 transition-all"
                                   placeholder="········">
                        </div>
                    </div>

                    <p class="text-xs text-gray-700">
                        Mínimo 8 caracteres · Al registrarte aceptás los
                        <a href="#" class="text-gray-500 hover:text-gray-400 underline">Términos de uso</a>.
                    </p>

                    <button type="submit"
                            class="w-full mt-2 py-3 px-4 font-display tracking-[0.2em] text-base
                                   bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                                   text-white rounded-lg uppercase
                                   focus:outline-none focus:ring-2 focus:ring-red-500/50
                                   active:scale-[0.98] transition-all
                                   shadow-lg shadow-red-900/40 hover:shadow-red-800/60">
                        Crear estudio gratis →
                    </button>
                </form>

                <p class="text-center text-xs text-gray-600 mt-5">
                    ¿Ya tenés cuenta?
                    <a href="<?= BASE_URL ?>/login"
                       class="text-red-400 hover:text-red-300 transition-colors">
                        Iniciar sesión
                    </a>
                </p>
            </div>

            <p class="text-center text-gray-700 text-xs mt-6 brand-tagline">
                © <?= date('Y') ?> InkManager · Free Plan · No credit card required
            </p>
        </div>
    </div>
</div>

<script>
function togglePwd(id) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
// Live password match indicator
document.getElementById('password_confirm').addEventListener('input', function () {
    const pwd  = document.getElementById('password').value;
    const ok   = this.value === pwd && this.value.length >= 8;
    this.style.borderColor = this.value.length ? (ok ? '#22c55e' : '#ef4444') : '';
});
</script>
</body>
</html>
