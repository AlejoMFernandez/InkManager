<?php
/** @var array  $pagos      Pagos del período */
/** @var array  $kpis       Totales hoy/semana/mes */
/** @var array  $breakdown  Totales por método */
/** @var string $periodo    hoy|semana|mes|todo */
/** @var string $desde      Fecha inicio del período (Y-m-d) */
/** @var string $hasta      Fecha fin del período (Y-m-d) */
/** @var float  $totalIng   Suma ingresos del período */
/** @var float  $totalEgr   Suma egresos del período */
/** @var string $csrf       CSRF token */

use App\Core\Auth;

$metodoCss = [
    'efectivo'      => 'bg-gray-500/20 text-gray-300',
    'transferencia' => 'bg-blue-500/20 text-blue-300',
    'tarjeta'       => 'bg-violet-500/20 text-violet-300',
    'sena'          => 'bg-amber-500/20 text-amber-300',
    'otro'          => 'bg-gray-600/20 text-gray-400',
];
$metodoLabel = [
    'efectivo'      => 'Efectivo',
    'transferencia' => 'Transferencia',
    'tarjeta'       => 'Tarjeta',
    'sena'          => 'Seña',
    'otro'          => 'Otro',
];

$periodoLabels = ['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes', 'todo' => 'Todo'];

function fmtMoney(float $n): string {
    return '$&nbsp;' . number_format($n, 2, ',', '.');
}
?>

<!-- ── Header ──────────────────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <h2 class="text-xl font-bold text-white">Caja</h2>
        <p class="text-xs text-gray-500 mt-0.5">Registro de ingresos y egresos del estudio</p>
    </div>
    <a href="<?= BASE_URL ?>/caja/nuevo"
       class="btn-glow flex items-center gap-2 px-4 py-2.5
              bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
              text-white text-sm font-semibold rounded-lg active:scale-95 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Registrar cobro
    </a>
</div>

<!-- ── KPI cards ───────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <?php
    $kpiData = [
        ['label' => 'Hoy',         'ing' => (float)($kpis['hoy_ing'] ?? 0), 'egr' => (float)($kpis['hoy_egr'] ?? 0), 'key' => 'hoy'],
        ['label' => 'Esta semana', 'ing' => (float)($kpis['sem_ing'] ?? 0), 'egr' => (float)($kpis['sem_egr'] ?? 0), 'key' => 'semana'],
        ['label' => 'Este mes',    'ing' => (float)($kpis['mes_ing'] ?? 0), 'egr' => (float)($kpis['mes_egr'] ?? 0), 'key' => 'mes'],
    ];
    foreach ($kpiData as $k):
        $neto   = $k['ing'] - $k['egr'];
        $active = $periodo === $k['key'];
    ?>
    <a href="?periodo=<?= $k['key'] ?>"
       class="kpi-card group block bg-gray-900 border rounded-xl p-4
              transition-all hover:border-gray-700
              <?= $active ? 'border-red-600/40 ring-1 ring-red-600/20' : 'border-gray-800' ?>">

        <div class="flex items-start justify-between mb-2">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <?= $k['label'] ?>
            </span>
            <?php if ($active): ?>
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 mt-1 flex-shrink-0
                         animate-[pulse-dot_2s_ease_infinite]"></span>
            <?php endif; ?>
        </div>

        <p class="text-2xl font-bold <?= $neto >= 0 ? 'text-white' : 'text-red-400' ?> leading-none mb-3 tabular-nums">
            <?= fmtMoney(abs($neto)) ?>
            <?php if ($neto < 0): ?>
            <span class="text-sm font-normal text-red-500 ml-1">negativo</span>
            <?php endif; ?>
        </p>

        <div class="flex items-center gap-4 text-xs tabular-nums">
            <span class="flex items-center gap-1 text-emerald-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                <?= fmtMoney($k['ing']) ?>
            </span>
            <span class="flex items-center gap-1 text-red-400">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                <?= fmtMoney($k['egr']) ?>
            </span>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Period tabs + table ─────────────────────────────────────────────── -->
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">

    <!-- Tabs -->
    <div class="flex items-center gap-1 px-4 pt-4 pb-0 border-b border-gray-800">
        <?php foreach ($periodoLabels as $key => $label): ?>
        <a href="?periodo=<?= $key ?>"
           class="px-3 py-2 text-sm font-medium rounded-t-lg border-b-2 transition-colors
                  <?= $periodo === $key
                      ? 'border-red-500 text-red-400'
                      : 'border-transparent text-gray-500 hover:text-gray-300' ?>">
            <?= $label ?>
        </a>
        <?php endforeach; ?>

        <div class="ml-auto flex items-center gap-2 pb-2 text-xs text-gray-600">
            <?php if ($periodo !== 'todo'): ?>
            <span><?= date('d/m', strtotime($desde)) ?><?= $desde !== $hasta ? ' — ' . date('d/m/Y', strtotime($hasta)) : '/'.date('Y', strtotime($desde)) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table / empty -->
    <?php if (empty($pagos)): ?>
    <div class="flex flex-col items-center justify-center gap-3 py-16 text-gray-600">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75
                     M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25
                     M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504
                     1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0
                     00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75
                     A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5z
                     m-12 0h.008v.008H6V10.5z"/>
        </svg>
        <p class="text-sm">Sin registros para este período</p>
        <a href="<?= BASE_URL ?>/caja/nuevo"
           class="text-red-400 hover:text-red-300 text-sm transition-colors">
            + Registrar primer cobro
        </a>
    </div>

    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b border-gray-800/60">
                    <th class="px-5 py-3 font-medium">Fecha</th>
                    <th class="px-5 py-3 font-medium">Cliente</th>
                    <th class="px-5 py-3 font-medium">Concepto</th>
                    <th class="px-5 py-3 font-medium hidden sm:table-cell">Método</th>
                    <th class="px-5 py-3 font-medium text-right">Monto</th>
                    <th class="px-5 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/40">
            <?php foreach ($pagos as $pago): ?>
                <tr class="table-row-hover group/row">
                    <td class="px-5 py-3 text-gray-400 whitespace-nowrap font-mono text-xs">
                        <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                    </td>
                    <td class="px-5 py-3 max-w-[140px]">
                        <?php if (!empty($pago['cliente_nombre'])): ?>
                        <a href="<?= BASE_URL ?>/clientes/<?= $pago['cliente_id'] ?>"
                           class="text-gray-300 hover:text-white truncate block transition-colors">
                            <?= htmlspecialchars($pago['cliente_nombre']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-gray-600">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 text-gray-200 max-w-[200px]">
                        <span class="truncate block"><?= htmlspecialchars($pago['concepto']) ?></span>
                        <?php if (!empty($pago['notas'])): ?>
                        <span class="text-xs text-gray-600 truncate block"><?= htmlspecialchars($pago['notas']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                     <?= $metodoCss[$pago['metodo']] ?? 'bg-gray-500/20 text-gray-400' ?>">
                            <?= $metodoLabel[$pago['metodo']] ?? $pago['metodo'] ?>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right whitespace-nowrap font-semibold tabular-nums
                               <?= $pago['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-red-400' ?>">
                        <?= $pago['tipo'] === 'egreso' ? '−' : '+' ?>
                        <?= fmtMoney((float) $pago['monto']) ?>
                    </td>
                    <td class="px-3 py-3">
                        <form method="POST"
                              action="<?= BASE_URL ?>/caja/<?= $pago['id'] ?>/borrar"
                              onsubmit="return confirm('¿Eliminar este registro?')">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit"
                                    class="opacity-0 group-hover/row:opacity-100 transition-opacity
                                           text-gray-600 hover:text-red-400 p-1 rounded"
                                    title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                             01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0
                                             00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Period total -->
    <div class="flex items-center justify-between px-5 py-3.5
                border-t border-gray-800 bg-gray-950/40">
        <div class="flex items-center gap-5 text-xs text-gray-500">
            <span><?= count($pagos) ?> registro<?= count($pagos) !== 1 ? 's' : '' ?></span>
            <?php if ($totalEgr > 0): ?>
            <span class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                Egresos: <span class="text-red-400 font-semibold tabular-nums ml-1"><?= fmtMoney($totalEgr) ?></span>
            </span>
            <?php endif; ?>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-500 mb-0.5">Neto del período</p>
            <p class="text-lg font-bold tabular-nums <?= ($totalIng - $totalEgr) >= 0 ? 'text-white' : 'text-red-400' ?>">
                <?= fmtMoney($totalIng - $totalEgr) ?>
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($breakdown)): ?>
<!-- ── Breakdown por método ─────────────────────────────────────────────── -->
<div class="mt-4 bg-gray-900 border border-gray-800 rounded-xl p-5">
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
        Desglose por método — <?= $periodoLabels[$periodo] ?? $periodo ?>
    </h3>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
    <?php
    // Group by method
    $byMetodo = [];
    foreach ($breakdown as $b) {
        $m = $b['metodo'];
        if (!isset($byMetodo[$m])) $byMetodo[$m] = ['ing' => 0, 'egr' => 0, 'qty' => 0];
        $byMetodo[$m][$b['tipo'] === 'ingreso' ? 'ing' : 'egr'] += (float)$b['total'];
        $byMetodo[$m]['qty'] += (int)$b['cantidad'];
    }
    foreach ($byMetodo as $met => $vals):
        $css = $metodoCss[$met] ?? 'bg-gray-500/20 text-gray-400';
    ?>
    <div class="bg-gray-800/40 rounded-lg p-3 border border-gray-800">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold mb-2
                     <?= $css ?>">
            <?= $metodoLabel[$met] ?? $met ?>
        </span>
        <p class="text-sm font-bold text-white tabular-nums"><?= fmtMoney($vals['ing'] - $vals['egr']) ?></p>
        <p class="text-[10px] text-gray-600 mt-0.5"><?= $vals['qty'] ?> mov.</p>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
