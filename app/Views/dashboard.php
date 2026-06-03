<?php
$pageTitle = 'Dashboard';
use App\Core\Auth;
$user   = Auth::user();
$hora   = (int) date('H');
$saludo = $hora < 12
    ? __('dashboard.greeting_morning')
    : ($hora < 19 ? __('dashboard.greeting_afternoon') : __('dashboard.greeting_evening'));

// ── Datos para el template de estado de turnos ────────────────────────────────
$estadoConfig = [
    'hecho'      => ['label' => __('dashboard.status_done'),       'color' => '#22c55e'],
    'confirmado' => ['label' => __('dashboard.status_confirmed'),   'color' => '#3b82f6'],
    'agendado'   => ['label' => __('dashboard.status_scheduled'),   'color' => '#eab308'],
    'cancelado'  => ['label' => __('dashboard.status_cancelled'),   'color' => '#ef4444'],
];
$totalEstado = max(1, array_sum($estadoMap));

// ── Chart.js CDN via $extraHead ───────────────────────────────────────────────
ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php
$extraHead = ob_get_clean();

// ── Datos + inicialización de charts via $extraScripts ────────────────────────
ob_start();
?>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    /* ── Tema global ──────────────────────────────────── */
    Chart.defaults.color          = '#6b7280';
    Chart.defaults.font.family    = "'Inter', sans-serif";
    Chart.defaults.font.size      = 11;
    Chart.defaults.borderColor    = 'rgba(75,85,99,.18)';

    const LABELS   = <?= json_encode($chartMesLabels) ?>;
    const ING_DATA = <?= json_encode($chartIngData) ?>;
    const CLI_DATA = <?= json_encode($chartCliData) ?>;
    const EST_LAB  = <?= json_encode(array_column($tatuajesPorEstilo, 'estilo')) ?>;
    const EST_DATA = <?= json_encode(array_map('intval', array_column($tatuajesPorEstilo, 'total'))) ?>;
    const PALETTE  = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#a855f7','#ec4899','#6b7280'];

    const tooltipDefaults = {
        backgroundColor : 'rgba(8,8,18,.97)',
        borderColor     : 'rgba(255,255,255,.08)',
        borderWidth     : 1,
        padding         : 10,
        titleColor      : '#f9fafb',
        bodyColor       : '#9ca3af',
        displayColors   : false,
    };

    /* ── 1. Ingresos — bar chart (grande) ────────────── */
    const ctxIng = document.getElementById('chart-ingresos');
    if (ctxIng) {
        new Chart(ctxIng, {
            type: 'bar',
            data: {
                labels   : LABELS,
                datasets : [{
                    data                : ING_DATA,
                    backgroundColor     : 'rgba(220,38,38,.65)',
                    hoverBackgroundColor: 'rgba(239,68,68,.90)',
                    borderRadius        : 5,
                    borderSkipped       : false,
                }],
            },
            options: {
                responsive          : true,
                maintainAspectRatio : false,
                plugins: {
                    legend : { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: { label: ctx => ' $' + ctx.raw.toLocaleString('es-AR') }
                    }
                },
                scales: {
                    x: {
                        grid  : { display: false },
                        border: { display: false },
                        ticks : { color: '#6b7280' },
                    },
                    y: {
                        grid  : { color: 'rgba(75,85,99,.15)' },
                        border: { display: false, dash: [4,4] },
                        ticks : {
                            color   : '#6b7280',
                            callback: v => '$' + v.toLocaleString('es-AR'),
                        },
                    },
                },
            },
        });
    }

    /* ── 2. Estilos — doughnut ───────────────────────── */
    const ctxEst = document.getElementById('chart-estilos');
    if (ctxEst) {
        if (EST_DATA.length > 0 && EST_DATA.some(v => v > 0)) {
            new Chart(ctxEst, {
                type: 'doughnut',
                data: {
                    labels  : EST_LAB,
                    datasets: [{
                        data           : EST_DATA,
                        backgroundColor: PALETTE.slice(0, EST_DATA.length),
                        borderWidth    : 0,
                        hoverOffset    : 6,
                    }],
                },
                options: {
                    responsive          : true,
                    maintainAspectRatio : false,
                    cutout              : '65%',
                    plugins: {
                        legend : { display: false },
                        tooltip: {
                            ...tooltipDefaults,
                            callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` }
                        },
                    },
                },
            });

            // Custom legend
            const legend = document.getElementById('estilos-legend');
            if (legend) {
                EST_LAB.forEach(function (label, i) {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between';
                    div.innerHTML =
                        '<div class="flex items-center gap-1.5 min-w-0">' +
                            '<div class="w-2 h-2 rounded-sm flex-shrink-0" style="background:' + PALETTE[i] + '"></div>' +
                            '<span class="text-gray-400 text-xs truncate">' + label + '</span>' +
                        '</div>' +
                        '<span class="text-gray-600 text-xs ml-2 flex-shrink-0">' + EST_DATA[i] + '</span>';
                    legend.appendChild(div);
                });
            }
        } else {
            // No data placeholder
            var p = document.createElement('p');
            p.className = 'text-gray-700 text-xs text-center py-8';
            p.textContent = <?= json_encode(__('dashboard.no_styles')) ?>;
            ctxEst.parentNode.replaceChild(p, ctxEst);
        }
    }

    /* ── 3. Clientes nuevos — line ───────────────────── */
    const ctxCli = document.getElementById('chart-clientes');
    if (ctxCli) {
        new Chart(ctxCli, {
            type: 'line',
            data: {
                labels  : LABELS,
                datasets: [{
                    data               : CLI_DATA,
                    borderColor        : '#ef4444',
                    backgroundColor    : 'rgba(239,68,68,.08)',
                    fill               : true,
                    tension            : 0.4,
                    borderWidth        : 2,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor   : 'transparent',
                    pointRadius        : 3,
                    pointHoverRadius   : 5,
                }],
            },
            options: {
                responsive          : true,
                maintainAspectRatio : false,
                plugins: {
                    legend : { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: { label: ctx => ' ' + ctx.raw + ' <?= __('dashboard.clients_tooltip') ?>' }
                    },
                },
                scales: {
                    x: {
                        grid  : { display: false },
                        border: { display: false },
                        ticks : { color: '#6b7280' },
                    },
                    y: {
                        grid  : { color: 'rgba(75,85,99,.15)' },
                        border: { display: false },
                        min   : 0,
                        ticks : { color: '#6b7280', stepSize: 1, precision: 0 },
                    },
                },
            },
        });
    }

    /* ── 4. Mini ingresos (Acceso rápido) ────────────── */
    const ctxMini = document.getElementById('chart-mini-ingresos');
    if (ctxMini) {
        new Chart(ctxMini, {
            type: 'bar',
            data: {
                labels  : LABELS,
                datasets: [{
                    data                : ING_DATA,
                    backgroundColor     : 'rgba(220,38,38,.55)',
                    hoverBackgroundColor: 'rgba(239,68,68,.85)',
                    borderRadius        : 3,
                    borderSkipped       : false,
                }],
            },
            options: {
                responsive          : true,
                maintainAspectRatio : false,
                plugins : { legend: { display: false }, tooltip: { enabled: false } },
                scales  : { x: { display: false }, y: { display: false } },
                animation : { duration: 800 },
            },
        });
    }

    /* ── Animar barras CSS (estado turnos) ───────────── */
    setTimeout(function () {
        document.querySelectorAll('[data-bar-pct]').forEach(function (el) {
            el.style.width = el.dataset.barPct + '%';
        });
    }, 450);
})();
</script>
<?php
$extraScripts = ob_get_clean();
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
                <?= __('dashboard.hero_today_is') ?> <?= date('d/m/Y') ?>.
                <?= __('dashboard.hero_studio_has') ?>
                <strong class="text-white"><?= $totalTurnos ?> <?= __('dashboard.hero_active_apts') ?></strong>
                <?= __('dashboard.hero_and') ?>
                <strong class="text-white"><?= $totalClientes ?> <?= __('dashboard.hero_clients_in') ?></strong>.
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
                <?= __('dashboard.new_client') ?>
            </a>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="inline-flex items-center gap-2 px-4 py-2.5
                      bg-gray-800 hover:bg-gray-700 border border-gray-700
                      text-gray-200 text-xs font-bold uppercase tracking-wider rounded-lg
                      active:scale-95 transition-all">
                <?= __('dashboard.new_apt_short') ?>
            </a>
        </div>
    </div>
</div>

<!-- /01 — Métricas -->
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="brand-tagline mb-1"><?= __('dashboard.section_01_tag') ?></p>
        <h2 class="section-heading"><?= __('dashboard.section_01_plain') ?><span class="accent"><?= __('dashboard.section_01_accent') ?></span></h2>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php
    $kpis = [
        [
            'label'  => __('dashboard.kpi_clients_label'),
            'value'  => $totalClientes,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            'color'  => 'blue',
        ],
        [
            'label'  => __('dashboard.kpi_tattoos_label'),
            'value'  => $totalTatuajes,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
            'color'  => 'red',
        ],
        [
            'label'  => __('dashboard.kpi_appointments_label'),
            'value'  => $totalTurnos,
            'format' => 'number',
            'icon'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
            'color'  => 'yellow',
        ],
        [
            'label'  => __('dashboard.kpi_revenue_label'),
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
        $c       = $colorMap[$kpi['color']];
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
        <div class="mt-3 h-px" style="background: linear-gradient(90deg, <?= $c['hex'] ?>66, transparent);"></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- /02 — Agenda -->
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="brand-tagline mb-1"><?= __('dashboard.section_02_tag') ?></p>
        <h2 class="section-heading"><?= __('dashboard.section_02_plain') ?><span class="accent"><?= __('dashboard.section_02_accent') ?></span></h2>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">

    <!-- Próximos turnos -->
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:200ms">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-200"><?= __('dashboard.upcoming') ?></h2>
            <a href="<?= BASE_URL ?>/turnos"
               class="text-xs text-red-400 hover:text-red-300 transition-colors"><?= __('dashboard.view_all') ?></a>
        </div>

        <?php if (empty($proximosTurnos)): ?>
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <svg class="w-10 h-10 text-gray-700 mb-3" fill="none" stroke="currentColor"
                 stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-600 text-sm"><?= __('dashboard.no_upcoming') ?></p>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="mt-3 text-xs text-red-400 hover:text-red-300"><?= __('dashboard.schedule_apt') ?></a>
        </div>
        <?php else: ?>
        <div class="space-y-2">
            <?php
            $estadoColors = [
                'agendado'   => 'bg-yellow-500/20 text-yellow-400',
                'confirmado' => 'bg-green-500/20 text-green-400',
                'hecho'      => 'bg-gray-500/20 text-gray-400',
                'cancelado'  => 'bg-red-500/20 text-red-400',
            ];
            foreach ($proximosTurnos as $t):
                $fechaFmt    = date('d/m H:i', strtotime($t['fecha_inicio']));
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
                    <?= __('status.' . $t['estado']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Acceso rápido + mini chart -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:280ms">
        <h2 class="text-sm font-semibold text-gray-200 mb-4"><?= __('dashboard.quick_access') ?></h2>
        <div class="space-y-2">
            <a href="<?= BASE_URL ?>/clientes/nuevo"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-red-500 font-bold text-base leading-none">+</span>
                <?= __('dashboard.new_client') ?>
            </a>
            <a href="<?= BASE_URL ?>/turnos/nuevo"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-red-500 font-bold text-base leading-none">+</span>
                <?= __('dashboard.new_appointment') ?>
            </a>
            <a href="<?= BASE_URL ?>/clientes"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gray-800
                      border border-gray-700/50 hover:border-red-600/30
                      text-sm text-gray-300 hover:text-white transition-all">
                <span class="text-gray-500">→</span>
                <?= __('dashboard.view_clients') ?>
            </a>
        </div>

        <!-- Mini bar chart — ingresos reales últimos 6 meses -->
        <div class="mt-4 pt-4 border-t border-gray-800">
            <p class="text-xs text-gray-500 mb-2"><?= __('dashboard.revenue_mini_label') ?></p>
            <div class="h-14">
                <canvas id="chart-mini-ingresos"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- /03 — Analítica -->
<div class="flex items-center justify-between mb-4">
    <div>
        <p class="brand-tagline mb-1"><?= __('dashboard.section_03_tag') ?></p>
        <h2 class="section-heading"><?= __('dashboard.section_03_plain') ?><span class="accent"><?= __('dashboard.section_03_accent') ?></span></h2>
    </div>
</div>

<!-- Fila A: Ingresos (2/3) + Estilos donut (1/3) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    <!-- Ingresos por mes -->
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:320ms">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium"><?= __('dashboard.revenue_label') ?></p>
                <p class="text-sm font-semibold text-white"><?= __('dashboard.last_6_months') ?></p>
            </div>
            <span class="text-xs text-gray-700 italic"><?= __('dashboard.by_tattoo_date') ?></span>
        </div>
        <div class="h-52">
            <canvas id="chart-ingresos"></canvas>
        </div>
    </div>

    <!-- Tatuajes por estilo — donut -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:380ms">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-0.5"><?= __('dashboard.styles_label') ?></p>
        <p class="text-sm font-semibold text-white mb-3"><?= __('dashboard.styles_distribution') ?></p>
        <div class="h-36">
            <canvas id="chart-estilos"></canvas>
        </div>
        <div id="estilos-legend" class="mt-3 space-y-1.5"></div>
    </div>

</div>

<!-- Fila B: Clientes nuevos (1/2) + Estado de turnos (1/2) -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <!-- Clientes nuevos por mes -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:420ms">
        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-0.5"><?= __('dashboard.kpi_clients_label') ?></p>
        <p class="text-sm font-semibold text-white mb-4"><?= __('dashboard.clients_new_per_month') ?></p>
        <div class="h-40">
            <canvas id="chart-clientes"></canvas>
        </div>
    </div>

    <!-- Turnos por estado — barras CSS animadas -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:460ms">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-medium mb-0.5"><?= __('dashboard.appointments_label') ?></p>
                <p class="text-sm font-semibold text-white"><?= __('dashboard.appointments_last30') ?></p>
            </div>
            <span class="text-lg font-bold text-white tabular-nums">
                <?= array_sum($estadoMap) ?>
            </span>
        </div>
        <div class="space-y-4">
            <?php foreach ($estadoConfig as $estado => $cfg):
                $n   = $estadoMap[$estado] ?? 0;
                $pct = $totalEstado > 0 ? round($n / $totalEstado * 100) : 0;
            ?>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs text-gray-400"><?= $cfg['label'] ?></span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-white tabular-nums"><?= $n ?></span>
                        <span class="text-xs text-gray-700"><?= $pct ?>%</span>
                    </div>
                </div>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 ease-out"
                         style="width: 0%; background: <?= $cfg['color'] ?>"
                         data-bar-pct="<?= $pct ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
(function () {
    /* ── KPI counter roll-up ─────────────────────────────────────── */
    function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

    function animateCounter(el) {
        const fmt    = el.dataset.counter;
        const target = parseFloat(el.dataset.value) || 0;
        const dur    = 900;
        const start  = performance.now();

        function tick(now) {
            const p   = Math.min((now - start) / dur, 1);
            const val = target * easeOut(p);
            el.textContent = fmt === 'money'
                ? '$' + Math.round(val).toLocaleString('es-AR')
                : Math.round(val).toLocaleString('es-AR');
            if (p < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    }

    const counters = document.querySelectorAll('[data-counter]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) { animateCounter(e.target); io.unobserve(e.target); }
            });
        }, { threshold: 0.3 });
        counters.forEach(el => io.observe(el));
    } else {
        counters.forEach(animateCounter);
    }
})();
</script>
