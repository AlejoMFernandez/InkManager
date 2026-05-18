<?php
$pageTitle = 'Dashboard';
use App\Core\Auth;
$user = Auth::user();
$hora = (int) date('H');
$saludo = $hora < 12 ? 'Buen día' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
?>

<!-- ═════════════ HERO BANNER ═════════════ -->
<div class="relative mb-8 overflow-hidden rounded-2xl border border-gray-800
            bg-gradient-to-br from-gray-900 via-gray-900 to-red-950/30 p-6 sm:p-8 card-in">
    <!-- Decorative grid -->
    <div class="absolute inset-0 brand-grid pointer-events-none opacity-60"></div>
    <!-- Decorative gradient orb -->
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <p class="brand-tagline mb-3">// Studio Control Panel</p>
            <h1 class="font-display text-4xl sm:text-5xl text-white tracking-wider uppercase leading-none">
                <?= $saludo ?>,<br>
                <span class="bg-gradient-to-r from-red-400 to-red-600 bg-clip-text text-transparent">
                    <?= htmlspecialchars($user['nombre'] ?? 'artista') ?>.
                </span>
            </h1>
            <p class="text-gray-400 text-sm mt-3 max-w-md">
                Hoy es <?= date('l, d \d\e F') ?>. Tu estudio tiene
                <strong class="text-white"><?= $totalTurnos ?> turnos activos</strong> y
                <strong class="text-white"><?= $totalClientes ?> clientes</strong> en cartera.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="<?= BASE_URL ?>/clientes/nuevo"
               class="btn-glow inline-flex items-center gap-2 px-4 py-2.5
                      bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                      text-white text-xs font-bold uppercase tracking-wider rounded-lg
                      active:scale-95 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo cliente
            </a>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="inline-flex items-center gap-2 px-4 py-2.5
                      bg-gray-800 hover:bg-gray-700 border border-gray-700
                      text-gray-200 text-xs font-bold uppercase tracking-wider rounded-lg
                      active:scale-95 transition-all">
                + Turno
            </a>
        </div>
    </div>
</div>

<!-- Section title -->
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="brand-tagline mb-1">/01 — Métricas</p>
        <h2 class="section-heading">Estado del <span class="accent">Estudio</span></h2>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8" id="kpi-grid">

    <?php
    $kpis = [
        [
            'label'  => 'Clientes',
            'value'  => $totalClientes,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'color'  => 'blue',
        ],
        [
            'label'  => 'Tatuajes',
            'value'  => $totalTatuajes,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
            'color'  => 'red',
        ],
        [
            'label'  => 'Turnos activos',
            'value'  => $totalTurnos,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            'color'  => 'yellow',
        ],
        [
            'label'  => 'Ingresos del mes',
            'value'  => $ingMes,
            'format' => 'money',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'color'  => 'green',
        ],
    ];

    $colorMap = [
        'red'    => ['bg' => 'bg-red-500/10',    'text' => 'text-red-400',    'border' => 'border-red-500/20',    'hex' => '#ef4444'],
        'blue'   => ['bg' => 'bg-blue-500/10',   'text' => 'text-blue-400',   'border' => 'border-blue-500/20',   'hex' => '#3b82f6'],
        'green'  => ['bg' => 'bg-green-500/10',  'text' => 'text-green-400',  'border' => 'border-green-500/20',  'hex' => '#22c55e'],
        'yellow' => ['bg' => 'bg-yellow-500/10', 'text' => 'text-yellow-400', 'border' => 'border-yellow-500/20', 'hex' => '#eab308'],
    ];

    foreach ($kpis as $kpi):
        $c = $colorMap[$kpi['color']];
        $display = $kpi['format'] === 'money'
            ? '$' . number_format((float)$kpi['value'], 0, ',', '.')
            : number_format((int)$kpi['value']);
    ?>
    <div class="kpi-card card-in bg-gray-900 border border-gray-800 rounded-xl p-5
                hover:border-gray-700 cursor-default group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-gray-500 text-xs font-medium uppercase tracking-wide"><?= $kpi['label'] ?></p>
            <div class="w-8 h-8 rounded-lg <?= $c['bg'] ?> <?= $c['border'] ?> border
                        flex items-center justify-center
                        group-hover:scale-110 transition-transform duration-200">
                <svg class="w-4 h-4 <?= $c['text'] ?>" fill="none" stroke="currentColor"
                     stroke-width="1.5" viewBox="0 0 24 24">
                    <?= $kpi['icon'] ?>
                </svg>
            </div>
        </div>
        <p class="big-stat tabular-nums"
           data-counter="<?= $kpi['format'] ?>"
           data-value="<?= (float)$kpi['value'] ?>">
            <?= $display ?>
        </p>
        <!-- decorative bar -->
        <div class="mt-3 h-px" style="background: linear-gradient(90deg, <?= $c['hex'] ?>66, transparent);"></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Section title -->
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="brand-tagline mb-1">/02 — Agenda</p>
        <h2 class="section-heading">Operaciones <span class="accent">en curso</span></h2>
    </div>
</div>

<!-- Two-column layout: próximos turnos + placeholder chart -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <!-- Próximos turnos -->
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:200ms">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-200">Próximos turnos</h2>
            <a href="<?= BASE_URL ?>/turnos"
               class="text-xs text-red-400 hover:text-red-300 transition-colors">Ver todos →</a>
        </div>

        <?php if (empty($proximosTurnos)): ?>
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <svg class="w-10 h-10 text-gray-700 mb-3" fill="none" stroke="currentColor"
                 stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-600 text-sm">No hay turnos próximos</p>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="mt-3 text-xs text-red-400 hover:text-red-300">+ Agendar turno</a>
        </div>
        <?php else: ?>
        <div class="space-y-2">
            <?php
            $estadoColors = [
                'agendado'   => 'bg-blue-500/20 text-blue-400',
                'confirmado' => 'bg-green-500/20 text-green-400',
                'hecho'      => 'bg-gray-500/20 text-gray-400',
                'cancelado'  => 'bg-red-500/20 text-red-400',
            ];
            foreach ($proximosTurnos as $t):
                $fechaFmt = date('d/m H:i', strtotime($t['fecha_inicio']));
                $colorEstado = $estadoColors[$t['estado']] ?? 'bg-gray-500/20 text-gray-400';
            ?>
            <div class="flex items-center gap-3 px-3 py-3 rounded-lg bg-gray-800/50
                        hover:bg-gray-800 transition-colors">
                <div class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white font-medium truncate">
                        <?= htmlspecialchars($t['cliente_nombre']) ?>
                    </p>
                    <p class="text-xs text-gray-500"><?= $fechaFmt ?> — <?= $t['duracion_min'] ?> min</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full <?= $colorEstado ?>">
                    <?= ucfirst($t['estado']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Acceso rápido -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:280ms">
        <h2 class="text-sm font-semibold text-gray-200 mb-4">Acceso rápido</h2>
        <div class="space-y-2">
            <a href="<?= BASE_URL ?>/clientes/nuevo"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      hover:bg-gray-750 border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-red-500 font-bold text-base leading-none">+</span>
                Nuevo cliente
            </a>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      hover:bg-gray-750 border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-red-500 font-bold text-base leading-none">+</span>
                Nuevo turno
            </a>
            <a href="<?= BASE_URL ?>/clientes"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      hover:bg-gray-750 border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-gray-500">→</span>
                Ver todos los clientes
            </a>
        </div>

        <!-- Coming soon chart placeholder -->
        <div class="mt-4 pt-4 border-t border-gray-800">
            <p class="text-xs text-gray-600 mb-2">Ingresos (últimos 6 meses)</p>
            <div class="flex items-end gap-1 h-16" id="mini-bars">
                <?php
                $bars = [40, 60, 45, 80, 55, 100];
                foreach ($bars as $i => $h): ?>
                <div class="flex-1 rounded-t bar-anim"
                     style="height: 0%; background: linear-gradient(to top, #dc2626aa, #ef444455);
                            transition: height 0.6s cubic-bezier(0.22,1,0.36,1) <?= $i * 80 ?>ms"
                     data-h="<?= $h ?>"></div>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-gray-700 text-center mt-2">Chart.js — Fase 6</p>
        </div>
    </div>

</div>

<script>
(function () {
    /* ── KPI counter roll-up ─────────────────────────────────────── */
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

    function animateCounter(el) {
        const fmt    = el.dataset.counter;   // 'money' | 'number'
        const target = parseFloat(el.dataset.value) || 0;
        const dur    = 900;
        const start  = performance.now();

        function tick(now) {
            const p   = Math.min((now - start) / dur, 1);
            const val = target * easeOut(p);

            if (fmt === 'money') {
                el.textContent = '$' + Math.round(val).toLocaleString('es-AR');
            } else {
                el.textContent = Math.round(val).toLocaleString('es-AR');
            }
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    /* observe KPI cards once they enter the viewport */
    const counters = document.querySelectorAll('[data-counter]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    animateCounter(e.target);
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(el => io.observe(el));
    } else {
        counters.forEach(animateCounter);
    }

    /* ── Mini bar chart grow ─────────────────────────────────────── */
    setTimeout(() => {
        document.querySelectorAll('#mini-bars .bar-anim').forEach(bar => {
            bar.style.height = bar.dataset.h + '%';
        });
    }, 350);
})();
</script>
