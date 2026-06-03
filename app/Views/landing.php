<!doctype html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkManager — El sistema operativo para estudios de tatuaje</title>
    <meta name="description" content="Gestioná clientes, turnos y marcá cada tatuaje en el body map 3D. Software completo para estudios de tatuaje modernos. Gratis.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="<?= PUBLIC_URL ?>/assets/css/brand.css">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background:
                radial-gradient(ellipse 1400px 700px at 70% -5%,  rgba(220,38,38,0.13), transparent 55%),
                radial-gradient(ellipse 800px  500px at 0%   100%, rgba(76,29,149,0.08), transparent 50%),
                #060608;
        }

        /* Navbar scroll blur */
        #main-nav { transition: background 300ms ease, box-shadow 300ms ease; }
        #main-nav.scrolled {
            background: rgba(6,6,8,0.88) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 1px 0 rgba(220,38,38,0.08);
        }

        /* Hero animations */
        @keyframes heroReveal {
            from { opacity: 0; transform: translateY(36px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .h1 { animation: heroReveal 0.85s cubic-bezier(0.22,1,0.36,1) 0.05s both; }
        .h2 { animation: heroReveal 0.85s cubic-bezier(0.22,1,0.36,1) 0.2s  both; }
        .h3 { animation: heroReveal 0.85s cubic-bezier(0.22,1,0.36,1) 0.35s both; }
        .h4 { animation: heroReveal 0.85s cubic-bezier(0.22,1,0.36,1) 0.5s  both; }
        .h5 { animation: heroReveal 0.85s cubic-bezier(0.22,1,0.36,1) 0.65s both; }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.65s ease, transform 0.65s cubic-bezier(0.22,1,0.36,1);
        }
        .reveal.visible { opacity: 1; transform: none; }
        .delay-1 { transition-delay: 0.08s; }
        .delay-2 { transition-delay: 0.18s; }
        .delay-3 { transition-delay: 0.28s; }

        /* Feature card */
        .feat-card {
            transition: border-color 300ms, transform 300ms cubic-bezier(0.22,1,0.36,1), box-shadow 300ms;
        }
        .feat-card:hover {
            border-color: rgba(220,38,38,0.28) !important;
            transform: translateY(-4px);
            box-shadow: 0 24px 48px -16px rgba(220,38,38,0.10);
        }

        /* Scan line */
        @keyframes scanLine {
            0%   { transform: translateY(-100%); opacity:0; }
            10%  { opacity:1; }
            90%  { opacity:1; }
            100% { transform: translateY(100vh);  opacity:0; }
        }
        .scan-line {
            position:fixed; left:0; right:0; height:1px; top:0;
            background: linear-gradient(90deg, transparent, rgba(239,68,68,0.35), transparent);
            animation: scanLine 9s linear infinite;
            pointer-events:none; z-index:1;
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #fff 0%, #fca5a5 50%, #ef4444 100%);
            -webkit-background-clip: text; background-clip: text; color: transparent;
        }

        /* Pro card glow */
        .pro-glow {
            box-shadow: 0 0 60px rgba(220,38,38,0.10), 0 0 120px rgba(220,38,38,0.04);
        }

        /* Diagonal separator between sections */
        .section-sep {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(220,38,38,0.15) 30%, rgba(220,38,38,0.15) 70%, transparent 100%);
        }

        /* Mobile menu */
        #mobile-menu { transition: max-height 0.3s ease, opacity 0.3s ease; max-height: 0; opacity: 0; overflow: hidden; }
        #mobile-menu.open { max-height: 300px; opacity: 1; }
    </style>
</head>
<body class="min-h-screen text-gray-100 antialiased overflow-x-hidden">

<div class="scan-line"></div>
<div class="fixed inset-0 brand-grid pointer-events-none" style="opacity:.4"></div>

<!-- ══════════════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════════════════ -->
<nav id="main-nav" class="fixed inset-x-0 top-0 z-50">
    <div class="max-w-7xl mx-auto flex items-center justify-between px-6 md:px-10 h-16">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>/" class="flex items-center gap-3 flex-shrink-0">
            <?php $monogramSize = 36; $monogramAnimate = false;
                  require APP_PATH . '/Views/partials/monogram.php'; ?>
            <span class="brand-wordmark text-base hidden sm:block">InkManager</span>
        </a>

        <!-- Desktop center links -->
        <div class="hidden md:flex items-center gap-8">
            <a href="#features"        class="text-xs text-gray-500 hover:text-gray-200 uppercase tracking-[0.2em] transition-colors">Features</a>
            <a href="#como-funciona"   class="text-xs text-gray-500 hover:text-gray-200 uppercase tracking-[0.2em] transition-colors">Cómo funciona</a>
            <a href="#precios"         class="text-xs text-gray-500 hover:text-gray-200 uppercase tracking-[0.2em] transition-colors">Precios</a>
        </div>

        <!-- Auth buttons -->
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="<?= BASE_URL ?>/login"
               class="hidden sm:inline-block text-sm text-gray-400 hover:text-white transition-colors px-3 py-1.5">
                Iniciar sesión
            </a>
            <a href="<?= BASE_URL ?>/registro"
               class="inline-flex items-center gap-1.5 px-4 py-2 font-display tracking-[0.12em]
                      text-sm uppercase bg-gradient-to-r from-red-600 to-red-700
                      hover:from-red-500 hover:to-red-600 text-white rounded-lg transition-all
                      shadow-lg shadow-red-900/30">
                <span>Crear estudio</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <!-- Hamburger mobile -->
            <button id="hamburger" class="md:hidden p-1.5 text-gray-400 hover:text-white transition-colors ml-1"
                    aria-label="Menú">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile dropdown -->
    <div id="mobile-menu" class="md:hidden bg-gray-950/95 backdrop-blur-md border-b border-gray-800 px-6">
        <div class="flex flex-col py-4 gap-3">
            <a href="#features"        class="text-sm text-gray-400 hover:text-white transition-colors py-1">Features</a>
            <a href="#como-funciona"   class="text-sm text-gray-400 hover:text-white transition-colors py-1">Cómo funciona</a>
            <a href="#precios"         class="text-sm text-gray-400 hover:text-white transition-colors py-1">Precios</a>
            <a href="<?= BASE_URL ?>/login"    class="text-sm text-gray-400 hover:text-white transition-colors py-1">Iniciar sesión</a>
        </div>
    </div>
</nav>


<!-- ══════════════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════════════════ -->
<section class="relative min-h-screen flex flex-col items-center justify-center
                px-6 text-center pt-24 pb-16">

    <!-- Radial glow behind text -->
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div style="position:absolute;top:25%;left:50%;transform:translateX(-50%);
                    width:900px;height:500px;border-radius:50%;
                    background:radial-gradient(ellipse at center, rgba(220,38,38,0.07) 0%, transparent 70%);"></div>
    </div>

    <div class="relative max-w-5xl mx-auto">

        <!-- Version tag -->
        <div class="h1 flex items-center justify-center gap-3 mb-8">
            <div class="h-px w-10 bg-gradient-to-r from-transparent to-red-600/50"></div>
            <span class="px-3 py-1.5 bg-gray-900/80 border border-gray-700 rounded-full
                         text-xs text-gray-400 font-mono tracking-wider">
                Versión 1.0 · Plan Free abierto
            </span>
            <div class="h-px w-10 bg-gradient-to-l from-transparent to-red-600/50"></div>
        </div>

        <!-- Main headline -->
        <h1 class="h2 font-display text-[clamp(3.5rem,10vw,8rem)] text-white
                   leading-none tracking-wider uppercase mb-8">
            El sistema<br>
            operativo para<br>
            tu <span class="text-red-500">estudio.</span>
        </h1>

        <!-- Subtitle -->
        <p class="h3 text-gray-400 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed mb-10">
            Clientes, turnos y body map 3D, todo en un solo lugar.
            Diseñado para estudios de tatuaje que quieren trabajar de manera profesional.
        </p>

        <!-- CTAs -->
        <div class="h4 flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
            <a href="<?= BASE_URL ?>/registro"
               class="inline-flex items-center gap-2.5 px-8 py-4 font-display tracking-[0.18em] text-base
                      bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                      text-white rounded-xl uppercase transition-all shadow-xl shadow-red-900/40
                      hover:shadow-red-800/60 active:scale-[0.98]">
                Crear estudio gratis
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <a href="#features"
               class="inline-flex items-center gap-2.5 px-8 py-4 font-display tracking-[0.18em] text-sm
                      border border-gray-700 hover:border-gray-500 text-gray-400 hover:text-white
                      rounded-xl uppercase transition-all">
                Ver funcionalidades ↓
            </a>
        </div>

        <!-- Trust pills -->
        <div class="h5 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
            <?php foreach ([
                ['M5 13l4 4L19 7', 'Sin tarjeta de crédito'],
                ['M5 13l4 4L19 7', 'Hasta 50 clientes gratis'],
                ['M5 13l4 4L19 7', 'Body Map 3D incluido'],
            ] as [$path, $text]): ?>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/>
                </svg>
                <?= $text ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-700 select-none">
        <span class="brand-tagline">Scroll</span>
        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════════════════════════════ -->
<section id="features" class="relative py-28 px-6">
    <div class="section-sep max-w-4xl mx-auto mb-28"></div>
    <div class="max-w-6xl mx-auto">

        <!-- Section header -->
        <div class="text-center mb-16 reveal">
            <p class="brand-tagline mb-3">/02 — Funcionalidades</p>
            <h2 class="font-display text-4xl sm:text-5xl text-white tracking-wider uppercase">
                Todo lo que necesita<br>
                <span class="text-red-500">tu estudio.</span>
            </h2>
            <p class="text-gray-500 text-sm mt-5 max-w-lg mx-auto leading-relaxed">
                Cada herramienta está pensada para el workflow real de un tatuador,
                sin complejidad innecesaria.
            </p>
        </div>

        <!-- 3-col feature grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <?php
            $features = [
                [
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'tag'   => '/01 — CRM',
                    'title' => 'Clientes',
                    'color' => '#ef4444',
                    'desc'  => 'Ficha completa por cliente: historial de sesiones, notas, etiquetas personalizadas y foto de perfil.',
                    'items' => ['Historial de tatuajes y turnos', 'Etiquetas en colores', 'Avatar y notas por sesión', 'Búsqueda instantánea'],
                ],
                [
                    'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'tag'   => '/02 — Agenda',
                    'title' => 'Turnos',
                    'color' => '#3b82f6',
                    'desc'  => 'Calendario visual con drag & drop. Reagendá en segundos y controlá el estado de cada sesión.',
                    'items' => ['Vista semanal y mensual', 'Reagendado drag & drop', 'Control de seña / depósito', 'Estados: agendado → hecho'],
                ],
                [
                    'icon'  => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                    'tag'   => '/03 — Body Map',
                    'title' => 'Mapa 3D',
                    'color' => '#a855f7',
                    'desc'  => 'Marcá cada tatuaje en el lugar exacto del cuerpo 3D. Rotá 360°, hacé zoom y guardá los marcadores.',
                    'items' => ['Modelo masculino y femenino', 'Marcadores con detalle por zona', 'Vista frontal, lateral y dorsal', 'Historial visual por cliente'],
                ],
            ];
            foreach ($features as $i => $f): ?>
            <div class="feat-card corner-brackets reveal delay-<?= $i+1 ?>
                        bg-gray-900/50 border border-gray-800/80 rounded-2xl p-7">
                <span class="br-tl"></span><span class="br-br"></span>

                <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-5 flex-shrink-0"
                     style="background:<?= $f['color'] ?>18; border:1px solid <?= $f['color'] ?>28;">
                    <svg class="w-5 h-5" fill="none" stroke="<?= $f['color'] ?>" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="<?= $f['icon'] ?>"/>
                    </svg>
                </div>

                <p class="brand-tagline mb-2"><?= $f['tag'] ?></p>
                <h3 class="font-display text-2xl text-white tracking-wider uppercase mb-3">
                    <?= $f['title'] ?>
                </h3>
                <p class="text-gray-500 text-sm leading-relaxed mb-5"><?= $f['desc'] ?></p>

                <ul class="space-y-2">
                    <?php foreach ($f['items'] as $item): ?>
                    <li class="flex items-center gap-2.5 text-xs text-gray-500">
                        <span class="w-1 h-1 rounded-full flex-shrink-0" style="background:<?= $f['color'] ?>"></span>
                        <?= $item ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Secondary features (pill row) -->
        <div class="reveal flex flex-wrap justify-center gap-2.5">
            <?php foreach ([
                '📊 Dashboard con analítica',
                '🌐 Multi-idioma ES / EN',
                '🔒 CSRF Protection',
                '📱 Responsive design',
                '🏷️ Etiquetas personalizadas',
                '📈 Gráficos de ingresos',
                '🎨 Estilos de tatuaje',
                '🔔 Sistema de notificaciones',
            ] as $extra): ?>
            <span class="px-3.5 py-1.5 bg-gray-900/60 border border-gray-800 rounded-full
                         text-xs text-gray-500">
                <?= $extra ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════
     HOW IT WORKS
══════════════════════════════════════════════════════════════════════ -->
<section id="como-funciona" class="relative py-28 px-6">
    <div class="section-sep max-w-4xl mx-auto mb-28"></div>
    <div class="max-w-5xl mx-auto">

        <div class="text-center mb-16 reveal">
            <p class="brand-tagline mb-3">/03 — Cómo funciona</p>
            <h2 class="font-display text-4xl sm:text-5xl text-white tracking-wider uppercase">
                En 3 pasos<br>
                <span class="text-red-500">estás listo.</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 relative">
            <!-- Connector lines (desktop only) -->
            <div class="hidden md:block absolute top-9 left-[calc(16.6%+2rem)] right-[calc(16.6%+2rem)] h-px
                        bg-gradient-to-r from-red-600/20 via-red-600/40 to-red-600/20"></div>

            <?php
            $steps = [
                ['num'=>'01','title'=>'Creá tu estudio','desc'=>'Registrate gratis en 30 segundos. Sin tarjeta de crédito. Tu estudio queda configurado al instante con tu nombre y usuario de acceso.'],
                ['num'=>'02','title'=>'Cargá tus clientes','desc'=>'Agregá clientes con ficha completa: foto de perfil, notas, etiquetas y primera fecha de visita. Importar datos existentes es simple.'],
                ['num'=>'03','title'=>'Gestioná todo','desc'=>'Dashboard con métricas en tiempo real, agenda drag & drop y body map 3D para registrar con precisión cada trabajo realizado.'],
            ];
            foreach ($steps as $i => $step): ?>
            <div class="reveal delay-<?= $i+1 ?> text-center flex flex-col items-center">
                <div class="w-[4.5rem] h-[4.5rem] rounded-2xl bg-gray-900 border border-gray-800
                            flex items-center justify-center mb-6 relative z-10
                            font-display text-3xl text-red-500 tracking-wider
                            shadow-lg shadow-red-900/20">
                    <?= $step['num'] ?>
                </div>
                <h3 class="font-display text-xl text-white tracking-wider uppercase mb-3">
                    <?= $step['title'] ?>
                </h3>
                <p class="text-gray-500 text-sm leading-relaxed max-w-xs"><?= $step['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════
     PRICING
══════════════════════════════════════════════════════════════════════ -->
<section id="precios" class="relative py-28 px-6">
    <div class="section-sep max-w-4xl mx-auto mb-28"></div>
    <div class="max-w-4xl mx-auto">

        <div class="text-center mb-16 reveal">
            <p class="brand-tagline mb-3">/04 — Planes</p>
            <h2 class="font-display text-4xl sm:text-5xl text-white tracking-wider uppercase">
                Empezá gratis,<br>
                <span class="text-red-500">escalá cuando crezcas.</span>
            </h2>
            <p class="text-gray-500 text-sm mt-5">Sin cargos ocultos. Cambiás de plan cuando lo necesitás.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            <!-- ── Free Plan ──────────────────────────────────────── -->
            <div class="reveal corner-brackets bg-gray-900/60 border border-gray-800 rounded-2xl p-8">
                <span class="br-tl"></span><span class="br-br"></span>

                <p class="brand-tagline mb-4">Plan gratuito</p>
                <div class="flex items-baseline gap-2 mb-2">
                    <span class="font-display text-6xl text-white tracking-wider">$0</span>
                    <span class="text-gray-600 text-sm">/mes</span>
                </div>
                <p class="text-xs text-gray-600 mb-8">Sin límite de tiempo · Sin tarjeta de crédito</p>

                <ul class="space-y-3 mb-8">
                    <?php foreach ([
                        'Hasta 50 clientes',
                        '1 usuario (owner)',
                        'Body Map 3D completo',
                        'Calendario drag & drop',
                        'CRM con notas y etiquetas',
                        'Dashboard con analítica',
                        'Multi-idioma ES / EN',
                        'Configuración del estudio',
                    ] as $item): ?>
                    <li class="flex items-center gap-3 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= $item ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= BASE_URL ?>/registro"
                   class="flex items-center justify-center gap-2 w-full py-3.5 px-6
                          border border-gray-700 hover:border-gray-500
                          text-gray-300 hover:text-white font-display tracking-[0.15em]
                          text-sm uppercase rounded-xl transition-all">
                    Crear estudio gratis →
                </a>
            </div>

            <!-- ── Pro Plan ───────────────────────────────────────── -->
            <div class="reveal delay-1 corner-brackets pro-glow
                        bg-gray-900/60 border border-red-600/25 rounded-2xl p-8 relative overflow-hidden">
                <span class="br-tl" style="border-color:rgba(220,38,38,0.6);opacity:1"></span>
                <span class="br-br" style="border-color:rgba(220,38,38,0.6);opacity:1"></span>

                <!-- Badge -->
                <div class="absolute top-5 right-5">
                    <span class="px-2.5 py-1 bg-red-600/15 border border-red-600/35
                                 text-red-400 text-xs font-mono rounded-full">
                        Próximamente
                    </span>
                </div>

                <!-- Subtle red shimmer top -->
                <div class="absolute top-0 left-0 right-0 h-px
                            bg-gradient-to-r from-transparent via-red-500/50 to-transparent"></div>

                <p class="brand-tagline mb-4" style="color:rgba(220,38,38,0.8)">Plan Pro</p>
                <div class="flex items-baseline gap-2 mb-2">
                    <span class="font-display text-5xl text-white tracking-wider">Pro</span>
                </div>
                <p class="text-xs text-gray-600 mb-8">Precio a confirmar · Sin contrato mínimo</p>

                <ul class="space-y-3 mb-8">
                    <?php foreach ([
                        [true,  'Todo el plan Free incluido'],
                        [true,  'Clientes ilimitados'],
                        [true,  'Hasta 5 usuarios por estudio'],
                        [true,  'Estadísticas avanzadas'],
                        [true,  'Exportar reportes'],
                        [true,  'Notificaciones por email'],
                        [true,  'Gestión de staff y roles'],
                        [true,  'Soporte prioritario'],
                    ] as [$check, $item]): ?>
                    <li class="flex items-center gap-3 text-sm text-gray-400">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <?= $item ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <button disabled
                        class="flex items-center justify-center gap-2 w-full py-3.5 px-6
                               bg-gradient-to-r from-red-600/20 to-red-700/20
                               border border-red-600/25 text-red-400/50
                               font-display tracking-[0.15em] text-sm uppercase rounded-xl
                               cursor-not-allowed select-none">
                    Próximamente
                </button>
            </div>
        </div>

        <!-- FAQ mini -->
        <div class="mt-10 reveal">
            <p class="text-center text-xs text-gray-700 mb-6 brand-tagline">Preguntas frecuentes</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ([
                    ['¿Necesito tarjeta para el plan Free?', 'No. El plan Free no requiere tarjeta de crédito ni datos de pago.'],
                    ['¿Qué pasa si supero 50 clientes?', 'El sistema te avisa y te invita a pasar al plan Pro. No se borran tus datos.'],
                    ['¿Puedo cancelar en cualquier momento?', 'Sí. El plan Pro (cuando esté disponible) no tendrá contrato mínimo.'],
                    ['¿Mis datos son seguros?', 'Sí. Cada estudio tiene sus datos aislados. No compartimos información entre estudios.'],
                ] as [$q, $a]): ?>
                <div class="bg-gray-900/40 border border-gray-800/60 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-300 mb-1.5"><?= $q ?></p>
                    <p class="text-xs text-gray-600 leading-relaxed"><?= $a ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════
     FINAL CTA
══════════════════════════════════════════════════════════════════════ -->
<section class="relative py-28 px-6">
    <div class="section-sep max-w-4xl mx-auto mb-28"></div>
    <div class="max-w-2xl mx-auto text-center reveal">
        <!-- Decorative monogram large -->
        <div class="flex justify-center mb-8">
            <?php $monogramSize = 72; $monogramAnimate = true;
                  require APP_PATH . '/Views/partials/monogram.php'; ?>
        </div>
        <p class="brand-tagline mb-4">/05 — Empezá hoy</p>
        <h2 class="font-display text-5xl sm:text-6xl text-white tracking-wider uppercase mb-6 leading-none">
            Tu estudio,<br>
            <span class="text-red-500">ordenado.</span>
        </h2>
        <p class="text-gray-500 text-sm mb-10 max-w-sm mx-auto leading-relaxed">
            Creá tu estudio en 30 segundos. Gratis. Sin tarjeta de crédito.
            Todo listo para empezar a trabajar.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= BASE_URL ?>/registro"
               class="inline-flex items-center gap-2.5 px-10 py-4 font-display tracking-[0.2em] text-base
                      bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                      text-white rounded-xl uppercase transition-all
                      shadow-xl shadow-red-900/40 hover:shadow-red-800/60 active:scale-[0.98]">
                Crear estudio gratis
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
            <a href="<?= BASE_URL ?>/login"
               class="text-sm text-gray-500 hover:text-gray-300 transition-colors">
                ¿Ya tenés cuenta? Iniciá sesión →
            </a>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════════════════ -->
<footer class="border-t border-gray-800/50 px-6 py-8">
    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <?php $monogramSize = 22; $monogramAnimate = false;
                  require APP_PATH . '/Views/partials/monogram.php'; ?>
            <span class="brand-wordmark text-xs">InkManager</span>
            <span class="text-gray-800 select-none">·</span>
            <span class="brand-tagline">Digital Studio System</span>
        </div>
        <div class="flex items-center gap-6 text-xs text-gray-700">
            <a href="#features"             class="hover:text-gray-400 transition-colors">Features</a>
            <a href="#precios"              class="hover:text-gray-400 transition-colors">Precios</a>
            <a href="<?= BASE_URL ?>/login"    class="hover:text-gray-400 transition-colors">Iniciar sesión</a>
            <a href="<?= BASE_URL ?>/registro" class="hover:text-gray-400 transition-colors">Registro</a>
            <span>© <?= date('Y') ?> InkManager</span>
        </div>
    </div>
</footer>


<script>
// ── Navbar scroll blur ────────────────────────────────────────────────
(function () {
    var nav = document.getElementById('main-nav');
    function onScroll() { nav.classList.toggle('scrolled', window.scrollY > 48); }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

// ── Mobile menu toggle ────────────────────────────────────────────────
(function () {
    var btn  = document.getElementById('hamburger');
    var menu = document.getElementById('mobile-menu');
    if (!btn || !menu) return;
    btn.addEventListener('click', function () { menu.classList.toggle('open'); });
    // Close on link click
    menu.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { menu.classList.remove('open'); });
    });
})();

// ── Scroll reveal (IntersectionObserver) ─────────────────────────────
(function () {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.reveal').forEach(function (el) {
            el.classList.add('visible');
        });
        return;
    }
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });

    document.querySelectorAll('.reveal').forEach(function (el) {
        observer.observe(el);
    });
})();
</script>

</body>
</html>
