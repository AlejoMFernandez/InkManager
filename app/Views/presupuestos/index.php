<?php
/** @var array  $presupuestos */
/** @var string|null $estadoValido */

$estadoColors = [
    'borrador'  => 'text-gray-400  bg-gray-800/60   border-gray-700',
    'enviado'   => 'text-blue-400  bg-blue-900/30   border-blue-800',
    'aceptado'  => 'text-green-400 bg-green-900/30  border-green-800',
    'rechazado' => 'text-red-400   bg-red-900/30    border-red-800',
];
$estadoLabels = [
    'borrador'  => 'Borrador',
    'enviado'   => 'Enviado',
    'aceptado'  => 'Aceptado',
    'rechazado' => 'Rechazado',
];

$csrf = \App\Core\Auth::csrfToken();
?>

<?php ob_start(); ?>
<style>
.pre-row:hover td { background-color: rgba(255,255,255,.03); }
.chip-filter { transition: background .15s, color .15s, border-color .15s; }
.chip-filter.active { background: rgba(220,38,38,.15); color: #f87171; border-color: rgba(220,38,38,.4); }
</style>
<?php $extraHead = ob_get_clean(); ?>

<!-- Header ─────────────────────────────────────────────────────────── -->
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="brand-tagline mb-1">// Presupuestos</p>
        <h1 class="section-heading text-2xl">Presupuestos
            <span class="text-gray-600 text-lg font-normal"><?= count($presupuestos) ?></span>
        </h1>
    </div>
    <a href="<?= BASE_URL ?>/presupuestos/nuevo"
       class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo presupuesto
    </a>
</div>

<!-- Filter chips ────────────────────────────────────────────────────── -->
<div class="flex flex-wrap gap-2 mb-5">
    <?php
    $filters = [null => 'Todos'] + $estadoLabels;
    foreach ($filters as $val => $label):
        $isActive = ($val === $estadoValido);
        $href = $val ? BASE_URL . '/presupuestos?estado=' . $val : BASE_URL . '/presupuestos';
    ?>
    <a href="<?= $href ?>"
       class="chip-filter inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-medium
              <?= $isActive ? 'active' : 'border-gray-700 text-gray-400 hover:text-white hover:border-gray-500' ?>">
        <?php if ($val && $isActive): ?>
        <span class="w-1.5 h-1.5 rounded-full
            <?= $val === 'borrador' ? 'bg-gray-400' : ($val === 'enviado' ? 'bg-blue-400' : ($val === 'aceptado' ? 'bg-green-400' : 'bg-red-400')) ?>"></span>
        <?php endif; ?>
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Table ───────────────────────────────────────────────────────────── -->
<?php if (empty($presupuestos)): ?>
<div class="card-base flex flex-col items-center justify-center py-16 text-center">
    <svg class="w-12 h-12 text-gray-700 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500 mb-4">
        <?= $estadoValido ? 'No hay presupuestos con ese estado.' : 'Todavía no hay presupuestos.' ?>
    </p>
    <a href="<?= BASE_URL ?>/presupuestos/nuevo"
       class="text-red-400 hover:text-red-300 text-sm font-medium">+ Crear el primero</a>
</div>
<?php else: ?>
<div class="card-base overflow-hidden p-0">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-800">
                <th class="text-left px-4 py-3 text-gray-500 font-medium">Número</th>
                <th class="text-left px-4 py-3 text-gray-500 font-medium">Cliente</th>
                <th class="text-left px-4 py-3 text-gray-500 font-medium hidden sm:table-cell">Título</th>
                <th class="text-right px-4 py-3 text-gray-500 font-medium hidden md:table-cell">Monto</th>
                <th class="text-center px-4 py-3 text-gray-500 font-medium">Estado</th>
                <th class="text-right px-4 py-3 text-gray-500 font-medium hidden lg:table-cell">Fecha</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/60">
            <?php foreach ($presupuestos as $p):
                $badgeCls = $estadoColors[$p['estado']] ?? 'text-gray-400 bg-gray-800 border-gray-700';
                $badgeTxt = $estadoLabels[$p['estado']] ?? ucfirst($p['estado']);
            ?>
            <tr class="pre-row transition-colors cursor-pointer"
                onclick="window.location='<?= BASE_URL ?>/presupuestos/<?= (int)$p['id'] ?>'">
                <td class="px-4 py-3">
                    <span class="font-mono text-xs text-gray-400"><?= htmlspecialchars($p['numero']) ?></span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-white font-medium"><?= htmlspecialchars($p['cliente_nombre']) ?></span>
                </td>
                <td class="px-4 py-3 hidden sm:table-cell text-gray-300 max-w-[200px] truncate">
                    <?= htmlspecialchars($p['titulo']) ?>
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-right">
                    <?php if ($p['monto'] !== null && $p['monto'] !== ''): ?>
                    <span class="text-white font-mono">$<?= number_format((float)$p['monto'], 2, ',', '.') ?></span>
                    <?php else: ?>
                    <span class="text-gray-600">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-xs font-medium <?= $badgeCls ?>">
                        <?= $badgeTxt ?>
                    </span>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-500 text-xs">
                    <?= date('d/m/Y', strtotime($p['fecha'])) ?>
                </td>
                <td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
                    <a href="<?= BASE_URL ?>/presupuestos/<?= (int)$p['id'] ?>"
                       class="text-gray-500 hover:text-white transition-colors text-xs font-medium">Ver →</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
