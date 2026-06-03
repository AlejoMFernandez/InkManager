<?php
$pageTitle = 'Reportes';

// ── Chart.js CDN → $extraHead ─────────────────────────────────────────────────
ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php
$extraHead = ob_get_clean();

// ── Chart initialization → $extraScripts ─────────────────────────────────────
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.color       = '#6b7280';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.borderColor = 'rgba(75,85,99,.15)';

    const LABELS      = <?= json_encode($chartLabels,   JSON_UNESCAPED_UNICODE) ?>;
    const ING_DATA    = <?= json_encode($chartIngData) ?>;
    const CLI_DATA    = <?= json_encode($chartCliData) ?>;
    const DIAS_LBL    = <?= json_encode($diasLabels,    JSON_UNESCAPED_UNICODE) ?>;
    const DIAS_DATA   = <?= json_encode($diasData) ?>;
    const EST_LBL     = <?= json_encode(array_column($estilosData, 'estilo'), JSON_UNESCAPED_UNICODE) ?>;
    const EST_DATA    = <?= json_encode(array_map(fn($r) => (float) $r['total'], $estilosData)) ?>;
    const ESTADO      = <?= json_encode($estadoMap) ?>;
    const CAJA_ING    = <?= json_encode($cajaIngData) ?>;
    const CAJA_EGR    = <?= json_encode($cajaEgrData) ?>;

    const PALETTE = ['#dc2626','#ea580c','#d97706','#ca8a04','#65a30d','#16a34a','#0891b2','#6366f1'];
    const fmtARS  = v => '$ ' + v.toLocaleString('es-AR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    const fmtK    = v => v >= 1000 ? '$ ' + (v / 1000).toFixed(0) + 'k' : '$ ' + v;

    /* ── 1. Ingresos por mes — line chart ─────────────────────────────── */
    const ctxIng = document.getElementById('chart-ingresos');
    if (ctxIng) {
        const grd = ctxIng.getContext('2d').createLinearGradient(0, 0, 0, 220);
        grd.addColorStop(0, 'rgba(220,38,38,.20)');
        grd.addColorStop(1, 'rgba(220,38,38,0)');
        new Chart(ctxIng, {
            type: 'line',
            data: {
                labels: LABELS,
                datasets: [{
                    data: ING_DATA,
                    borderColor: '#dc2626',
                    backgroundColor: grd,
                    borderWidth: 2,
                    pointBackgroundColor: '#dc2626',
                    pointRadius: ING_DATA.length > 8 ? 2 : 3,
                    pointHoverRadius: 5,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: c => ' ' + fmtARS(c.parsed.y) }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        grid: { color: 'rgba(75,85,99,.15)' },
                        border: { display: false },
                        ticks: { callback: fmtK },
                    }
                }
            }
        });
    }

    /* ── 2. Turnos por estado — doughnut ──────────────────────────────── */
    const ctxEst = document.getElementById('chart-estados');
    if (ctxEst) {
        const labels = ['Agendados','Confirmados','Hechos','Cancelados'];
        const data   = [ESTADO.agendado, ESTADO.confirmado, ESTADO.hecho, ESTADO.cancelado];
        const total  = data.reduce((a, b) => a + b, 0);
        if (total > 0) {
            new Chart(ctxEst, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: ['#3b82f6', '#22c55e', '#6b7280', '#ef4444'],
                        borderColor: '#030712',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 9, padding: 12, usePointStyle: true, pointStyle: 'circle' }
                        },
                        tooltip: {
                            callbacks: { label: c => ' ' + c.label + ': ' + c.parsed }
                        }
                    }
                }
            });
        } else {
            ctxEst.closest('[data-chart-wrap]').innerHTML =
                '<p class="text-gray-600 text-xs text-center py-10">Sin datos en el período</p>';
        }
    }

    /* ── 3. Clientes nuevos por mes — bar ─────────────────────────────── */
    const ctxCli = document.getElementById('chart-clientes');
    if (ctxCli) {
        new Chart(ctxCli, {
            type: 'bar',
            data: {
                labels: LABELS,
                datasets: [{
                    data: CLI_DATA,
                    backgroundColor: 'rgba(59,130,246,.45)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        grid: { color: 'rgba(75,85,99,.15)' },
                        border: { display: false },
                        ticks: { stepSize: 1, precision: 0 },
                    }
                }
            }
        });
    }

    /* ── 4. Día más activo — bar ──────────────────────────────────────── */
    const ctxDia = document.getElementById('chart-dias');
    if (ctxDia) {
        const maxVal = Math.max(...DIAS_DATA);
        new Chart(ctxDia, {
            type: 'bar',
            data: {
                labels: DIAS_LBL,
                datasets: [{
                    data: DIAS_DATA,
                    backgroundColor: DIAS_DATA.map(
                        v => (v === maxVal && maxVal > 0) ? 'rgba(220,38,38,.75)' : 'rgba(220,38,38,.22)'
                    ),
                    borderColor: DIAS_DATA.map(
                        v => (v === maxVal && maxVal > 0) ? '#dc2626' : 'transparent'
                    ),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        grid: { color: 'rgba(75,85,99,.15)' },
                        border: { display: false },
                        ticks: { stepSize: 1, precision: 0 },
                    }
                }
            }
        });
    }

    /* ── 5. Ingresos por estilo — horizontal bar ──────────────────────── */
    const ctxEstilo = document.getElementById('chart-estilos');
    if (ctxEstilo && EST_DATA.length > 0) {
        new Chart(ctxEstilo, {
            type: 'bar',
            data: {
                labels: EST_LBL,
                datasets: [{
                    data: EST_DATA,
                    backgroundColor: EST_DATA.map((_, i) => PALETTE[i % PALETTE.length] + 'aa'),
                    borderColor:     EST_DATA.map((_, i) => PALETTE[i % PALETTE.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: c => ' ' + fmtARS(c.parsed.x) }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(75,85,99,.15)' },
                        border: { display: false },
                        ticks: { callback: fmtK },
                    },
                    y: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    }
    /* ── 6. Flujo de caja — grouped bar ──────────────────────────────── */
    const ctxCaja = document.getElementById('chart-caja-flujo');
    if (ctxCaja) {
        const hasData = CAJA_ING.some(v => v > 0) || CAJA_EGR.some(v => v > 0);
        if (hasData) {
            new Chart(ctxCaja, {
                type: 'bar',
                data: {
                    labels: LABELS,
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: CAJA_ING,
                            backgroundColor: 'rgba(34,197,94,.4)',
                            borderColor: '#22c55e',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Egresos',
                            data: CAJA_EGR,
                            backgroundColor: 'rgba(239,68,68,.35)',
                            borderColor: '#ef4444',
                            borderWidth: 1,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 9, padding: 14, usePointStyle: true, pointStyle: 'circle' }
                        },
                        tooltip: {
                            callbacks: { label: c => ' ' + c.dataset.label + ': ' + fmtARS(c.parsed.y) }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false } },
                        y: {
                            grid: { color: 'rgba(75,85,99,.15)' },
                            border: { display: false },
                            ticks: { callback: fmtK },
                        }
                    }
                }
            });
        } else {
            ctxCaja.closest('[data-chart-wrap]').innerHTML =
                '<p class="text-gray-600 text-xs text-center py-10">Sin movimientos de caja en el período</p>';
        }
    }
});
</script>
<?php
$extraScripts = ob_get_clean();
?>

<?php
$periodos = [
    '30d' => 'Últimos 30d',
    '3m'  => '3 meses',
    '6m'  => '6 meses',
    '12m' => '12 meses',
    'ytd' => 'Este año',
];
$completadosColor = $kpiTasaCompletados >= 70
    ? 'text-green-400'
    : ($kpiTasaCompletados >= 40 ? 'text-yellow-400' : 'text-gray-200');
?>

<!-- ── Header + period selector ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 page-enter">
    <div>
        <p class="brand-tagline mb-1">// Analítica</p>
        <h2 class="section-heading">Reportes</h2>
        <p class="text-gray-500 text-xs mt-1">
            <?= date('d/m/Y', strtotime($desde)) ?> — <?= date('d/m/Y', strtotime($hasta)) ?>
        </p>
    </div>
    <!-- Period pills -->
    <div class="flex items-center gap-1 bg-gray-900 border border-gray-800 rounded-lg p-1 flex-wrap">
        <?php foreach ($periodos as $key => $label): ?>
        <a href="?periodo=<?= $key ?>"
           class="px-3 py-1.5 text-xs font-medium rounded-md transition-all whitespace-nowrap
                  <?= $periodo === $key
                    ? 'bg-red-600/20 text-red-400 border border-red-600/30'
                    : 'text-gray-500 hover:text-white hover:bg-gray-800/60' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── KPI row ──────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    <div class="kpi-card bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <div class="flex items-start justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Ingresos</p>
            <svg class="w-4 h-4 text-red-600/60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="font-display text-2xl text-white tracking-wider leading-none">
            $&nbsp;<?= number_format($kpiIngresos, 0, ',', '.') ?>
        </p>
        <p class="text-xs text-gray-600 mt-1.5">por tatuajes</p>
    </div>

    <div class="kpi-card bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <div class="flex items-start justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Señas</p>
            <svg class="w-4 h-4 text-yellow-600/60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <p class="font-display text-2xl text-white tracking-wider leading-none">
            $&nbsp;<?= number_format($kpiSenas, 0, ',', '.') ?>
        </p>
        <p class="text-xs text-gray-600 mt-1.5">cobradas en turnos</p>
    </div>

    <div class="kpi-card bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <div class="flex items-start justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Clientes nuevos</p>
            <svg class="w-4 h-4 text-blue-500/60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <p class="font-display text-2xl text-white tracking-wider leading-none">
            <?= $kpiNuevosClientes ?>
        </p>
        <p class="text-xs text-gray-600 mt-1.5">registrados en el período</p>
    </div>

    <div class="kpi-card bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <div class="flex items-start justify-between mb-2">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Completados</p>
            <svg class="w-4 h-4 text-green-500/60 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="font-display text-2xl tracking-wider leading-none <?= $completadosColor ?>">
            <?= $kpiTasaCompletados ?>%
        </p>
        <p class="text-xs text-gray-600 mt-1.5"><?= $kpiTurnosHechos ?> turnos hechos</p>
    </div>

</div>

<!-- ── Chart row 1: Revenue (2/3) + Status donut (1/3) ──────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    <div class="lg:col-span-2 bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <p class="brand-tagline mb-0.5">// Ingresos</p>
        <h3 class="text-sm font-semibold text-white mb-4">Ingresos por mes</h3>
        <div class="h-52">
            <canvas id="chart-ingresos"></canvas>
        </div>
    </div>

    <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <p class="brand-tagline mb-0.5">// Turnos</p>
        <h3 class="text-sm font-semibold text-white mb-4">Por estado</h3>
        <div class="h-52" data-chart-wrap>
            <canvas id="chart-estados"></canvas>
        </div>
    </div>

</div>

<!-- ── Chart row 2: New clients (1/2) + Busiest day (1/2) ───────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <p class="brand-tagline mb-0.5">// Clientes</p>
        <h3 class="text-sm font-semibold text-white mb-4">Nuevos clientes por mes</h3>
        <div class="h-44">
            <canvas id="chart-clientes"></canvas>
        </div>
    </div>

    <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
        <p class="brand-tagline mb-0.5">// Agenda</p>
        <h3 class="text-sm font-semibold text-white mb-4">Día más activo de la semana</h3>
        <div class="h-44">
            <canvas id="chart-dias"></canvas>
        </div>
    </div>

</div>

<!-- ── Revenue by style — horizontal bar (only if data exists) ──────────── -->
<?php if (!empty($estilosData)): ?>
<div class="bg-gray-900/60 border border-gray-800 rounded-xl p-5 mb-4 card-in">
    <p class="brand-tagline mb-0.5">// Estilos</p>
    <h3 class="text-sm font-semibold text-white mb-4">Ingresos por estilo</h3>
    <div style="height: <?= max(160, count($estilosData) * 38) ?>px">
        <canvas id="chart-estilos"></canvas>
    </div>
</div>
<?php endif; ?>

<!-- ── Sección Caja ──────────────────────────────────────────────────────── -->
<?php
$metodoLabels = [
    'efectivo'      => '💵 Efectivo',
    'transferencia' => '🏦 Transferencia',
    'tarjeta'       => '💳 Tarjeta',
    'sena'          => '📌 Seña',
    'otro'          => '⚙️ Otro',
];
$balanceColor = $cajaBalance >= 0 ? 'text-green-400' : 'text-red-400';
?>

<div class="mt-6 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="brand-tagline">// Caja</p>
            <h3 class="text-sm font-semibold text-white">Flujo de caja</h3>
        </div>
        <a href="<?= BASE_URL ?>/caja" class="text-xs text-gray-500 hover:text-white transition-colors">
            Ver caja →
        </a>
    </div>

    <!-- KPIs de caja -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 card-in">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Ingresos</p>
            <p class="font-display text-xl text-green-400 tracking-wider leading-none">
                $&nbsp;<?= number_format($cajaIngresos, 0, ',', '.') ?>
            </p>
            <p class="text-xs text-gray-600 mt-1.5">cobros registrados</p>
        </div>
        <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 card-in">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Egresos</p>
            <p class="font-display text-xl text-red-400 tracking-wider leading-none">
                $&nbsp;<?= number_format($cajaEgresos, 0, ',', '.') ?>
            </p>
            <p class="text-xs text-gray-600 mt-1.5">gastos registrados</p>
        </div>
        <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-4 card-in">
            <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Balance</p>
            <p class="font-display text-xl <?= $balanceColor ?> tracking-wider leading-none">
                <?= $cajaBalance >= 0 ? '+' : '' ?>$&nbsp;<?= number_format(abs($cajaBalance), 0, ',', '.') ?>
            </p>
            <p class="text-xs text-gray-600 mt-1.5">neto del período</p>
        </div>
    </div>

    <!-- Chart flujo + breakdown por método -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Gráfico grouped bar -->
        <div class="lg:col-span-2 bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
            <h4 class="text-xs font-semibold text-gray-400 mb-4 uppercase tracking-wider">Ingresos vs egresos por mes</h4>
            <div class="h-48" data-chart-wrap>
                <canvas id="chart-caja-flujo"></canvas>
            </div>
        </div>

        <!-- Breakdown por método -->
        <div class="bg-gray-900/60 border border-gray-800 rounded-xl p-5 card-in">
            <h4 class="text-xs font-semibold text-gray-400 mb-4 uppercase tracking-wider">Por método de pago</h4>
            <?php if (empty($cajaMetodos)): ?>
            <p class="text-gray-600 text-xs py-6 text-center">Sin movimientos en el período</p>
            <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($cajaMetodos as $m):
                    $total = (float)$m['ingresos'] - (float)$m['egresos'];
                    $pct   = $cajaIngresos > 0
                        ? min(100, round((float)$m['ingresos'] / $cajaIngresos * 100))
                        : 0;
                ?>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-400">
                            <?= $metodoLabels[$m['metodo']] ?? htmlspecialchars($m['metodo']) ?>
                        </span>
                        <span class="text-xs text-gray-300 tabular-nums font-medium">
                            $<?= number_format((float)$m['ingresos'], 0, ',', '.') ?>
                        </span>
                    </div>
                    <div class="h-1 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500/60 rounded-full transition-all"
                             style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Top clients table ─────────────────────────────────────────────────── -->
<?php if (!empty($topClientes)): ?>
<div class="bg-gray-900/60 border border-gray-800 rounded-xl overflow-hidden card-in">
    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
        <div>
            <p class="brand-tagline">// Top clientes</p>
            <h3 class="text-sm font-semibold text-white">Clientes más activos del período</h3>
        </div>
        <span class="text-xs text-gray-600"><?= count($topClientes) ?> clientes</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800/80">
                    <th class="px-5 py-3 text-left text-xs text-gray-500 uppercase tracking-wider font-semibold w-8">#</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 uppercase tracking-wider font-semibold">Cliente</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500 uppercase tracking-wider font-semibold">Turnos</th>
                    <th class="px-4 py-3 text-right text-xs text-gray-500 uppercase tracking-wider font-semibold">Hechos</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 uppercase tracking-wider font-semibold">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
                <?php foreach ($topClientes as $i => $c): ?>
                <tr class="table-row-hover hover:bg-gray-800/25 transition-colors">
                    <td class="px-5 py-3 text-gray-600 font-mono text-xs"><?= $i + 1 ?></td>
                    <td class="px-5 py-3">
                        <a href="<?= BASE_URL ?>/clientes/<?= (int) $c['id'] ?>"
                           class="font-medium text-gray-200 hover:text-white link-underline">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </a>
                        <?php if (!empty($c['instagram'])): ?>
                        <span class="text-gray-600 text-xs ml-1.5">@<?= htmlspecialchars($c['instagram']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-400"><?= (int) $c['num_turnos'] ?></td>
                    <td class="px-4 py-3 text-right">
                        <span class="<?= $c['turnos_hechos'] > 0 ? 'text-green-400' : 'text-gray-600' ?>">
                            <?= (int) $c['turnos_hechos'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right font-mono text-gray-300">
                        <?= $c['total_invertido'] > 0
                            ? '$&nbsp;' . number_format((float) $c['total_invertido'], 0, ',', '.')
                            : '<span class="text-gray-700">—</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<div class="bg-gray-900/40 border border-gray-800/50 rounded-xl p-10 text-center card-in">
    <svg class="w-8 h-8 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
    </svg>
    <p class="text-gray-600 text-sm">No hay actividad en el período seleccionado.</p>
    <p class="text-gray-700 text-xs mt-1">Probá ampliar el rango de fechas.</p>
</div>
<?php endif; ?>
