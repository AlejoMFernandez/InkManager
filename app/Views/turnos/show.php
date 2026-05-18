<?php
$pageTitle = 'Turno — ' . htmlspecialchars($turno['cliente_nombre']);
use App\Core\Auth;
$csrf = Auth::csrfToken();

$estadoColors = [
    'agendado'   => 'bg-blue-500/15 text-blue-400 border-blue-500/25',
    'confirmado' => 'bg-green-500/15 text-green-400 border-green-500/25',
    'hecho'      => 'bg-gray-500/15 text-gray-400 border-gray-500/25',
    'cancelado'  => 'bg-red-500/15 text-red-400 border-red-500/25',
];
$colorClass = $estadoColors[$turno['estado']] ?? $estadoColors['agendado'];

$estados = ['agendado','confirmado','hecho','cancelado'];
?>

<div class="max-w-lg">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/turnos" class="hover:text-white">Turnos</a>
        <span>/</span>
        <span class="text-gray-300"><?= htmlspecialchars($turno['cliente_nombre']) ?></span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <!-- Header con estado -->
        <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
            <div>
                <a href="<?= BASE_URL ?>/clientes/<?= $turno['cliente_id'] ?>"
                   class="text-lg font-bold text-white hover:text-red-400 transition-colors">
                    <?= htmlspecialchars($turno['cliente_nombre']) ?>
                </a>
                <?php if ($turno['cliente_instagram']): ?>
                <p class="text-blue-400 text-xs"><?= htmlspecialchars($turno['cliente_instagram']) ?></p>
                <?php endif; ?>
            </div>
            <span class="px-3 py-1 rounded-full border text-xs font-medium <?= $colorClass ?>">
                <?= ucfirst($turno['estado']) ?>
            </span>
        </div>

        <!-- Datos -->
        <div class="px-6 py-5 space-y-3">
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm">Fecha</dt>
                <dd class="text-white text-sm font-medium">
                    <?= date('d/m/Y H:i', strtotime($turno['fecha_inicio'])) ?>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm">Duración</dt>
                <dd class="text-gray-300 text-sm"><?= $turno['duracion_min'] ?> minutos</dd>
            </div>
            <?php if ($turno['sena']): ?>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm">Seña</dt>
                <dd class="text-gray-300 text-sm">$<?= number_format((float)$turno['sena'],0,',','.') ?></dd>
            </div>
            <?php endif; ?>
            <?php if ($turno['notas']): ?>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm">Notas</dt>
                <dd class="text-gray-400 text-sm"><?= htmlspecialchars($turno['notas']) ?></dd>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cambiar estado rápido -->
        <div class="px-6 py-4 border-t border-gray-800">
            <p class="text-xs text-gray-600 mb-2">Cambiar estado:</p>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($estados as $e): ?>
                <form method="POST" action="<?= BASE_URL ?>/turnos/<?= $turno['id'] ?>/estado">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="estado" value="<?= $e ?>">
                    <button type="submit"
                            class="px-3 py-1.5 text-xs rounded-lg border transition-colors
                                   <?= $turno['estado'] === $e
                                       ? 'bg-red-600 border-red-600 text-white font-semibold'
                                       : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white hover:border-gray-600' ?>">
                        <?= ucfirst($e) ?>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Acciones -->
        <div class="px-6 py-4 border-t border-gray-800 flex gap-3">
            <a href="<?= BASE_URL ?>/turnos/<?= $turno['id'] ?>/editar"
               class="flex-1 py-2 text-center bg-gray-800 hover:bg-gray-700
                      text-gray-300 text-sm rounded-lg transition-colors">
                Editar
            </a>
            <a href="<?= BASE_URL ?>/clientes/<?= $turno['cliente_id'] ?>"
               class="flex-1 py-2 text-center bg-gray-800 hover:bg-gray-700
                      text-gray-300 text-sm rounded-lg transition-colors">
                Ver cliente
            </a>
        </div>
    </div>
</div>
