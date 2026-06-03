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
        <a href="<?= BASE_URL ?>/turnos" class="hover:text-white"><?= __('nav.appointments') ?></a>
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
                <?= htmlspecialchars(__('status.' . $turno['estado'])) ?>
            </span>
        </div>

        <!-- Datos -->
        <div class="px-6 py-5 space-y-3">
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm"><?= __('field.date') ?></dt>
                <dd class="text-white text-sm font-medium">
                    <?= date('d/m/Y H:i', strtotime($turno['fecha_inicio'])) ?>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm"><?= __('field.duration') ?></dt>
                <dd class="text-gray-300 text-sm"><?= $turno['duracion_min'] ?> <?= __('common.minutes') ?></dd>
            </div>
            <?php if ($turno['sena']): ?>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm"><?= __('appointment.deposit') ?></dt>
                <dd class="text-gray-300 text-sm">$<?= number_format((float)$turno['sena'],0,',','.') ?></dd>
            </div>
            <?php endif; ?>
            <?php if ($turno['notas']): ?>
            <div class="flex gap-3">
                <dt class="text-gray-600 w-28 flex-shrink-0 text-sm"><?= __('field.notes') ?></dt>
                <dd class="text-gray-400 text-sm"><?= htmlspecialchars($turno['notas']) ?></dd>
            </div>
            <?php endif; ?>
        </div>

        <!-- Cambiar estado rápido -->
        <div class="px-6 py-4 border-t border-gray-800">
            <p class="text-xs text-gray-600 mb-2"><?= __('common.change_status') ?></p>
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
                        <?= htmlspecialchars(__('status.' . $e)) ?>
                    </button>
                </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Acciones -->
        <div class="px-6 py-4 border-t border-gray-800 flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/turnos/<?= $turno['id'] ?>/editar"
               class="flex-1 min-w-[100px] py-2 text-center bg-gray-800 hover:bg-gray-700
                      text-gray-300 text-sm rounded-lg transition-colors">
                <?= __('btn.edit') ?>
            </a>
            <a href="<?= BASE_URL ?>/clientes/<?= $turno['cliente_id'] ?>"
               class="flex-1 min-w-[100px] py-2 text-center bg-gray-800 hover:bg-gray-700
                      text-gray-300 text-sm rounded-lg transition-colors">
                <?= __('client.view_client') ?>
            </a>
            <a href="<?= BASE_URL ?>/caja/nuevo?turno_id=<?= $turno['id'] ?>"
               class="flex-1 min-w-[100px] py-2 flex items-center justify-center gap-1.5
                      bg-emerald-600/15 hover:bg-emerald-600/25 border border-emerald-600/30
                      text-emerald-400 hover:text-emerald-300 text-sm rounded-lg transition-colors"
               title="Registrar cobro de este turno en Caja">
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0
                             1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12
                             12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303
                             0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9
                             9 0 0 1 18 0z"/>
                </svg>
                Cobrar
            </a>
            <?php if (!empty($turno['cliente_telefono'])): ?>
            <?php
                $dias    = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                $waPhone = ltrim(preg_replace('/[^\d]/', '', $turno['cliente_telefono']), '0');
                $waMsg   = "Hola {$turno['cliente_nombre']}! 👋\n\n"
                         . "Te recordamos que tenés turno el *"
                         . $dias[date('w', strtotime($turno['fecha_inicio']))]
                         . " " . date('d/m/Y', strtotime($turno['fecha_inicio']))
                         . "* a las *" . date('H:i', strtotime($turno['fecha_inicio'])) . "*."
                         . "\n\n¡Te esperamos! 🎨";
                $waHref  = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMsg);
            ?>
            <a href="<?= $waHref ?>" target="_blank" rel="noopener noreferrer"
               class="flex-1 min-w-[120px] py-2 flex items-center justify-center gap-1.5
                      bg-green-600/15 hover:bg-green-600/25 border border-green-600/30
                      text-green-400 hover:text-green-300 text-sm rounded-lg transition-colors"
               title="Abrir WhatsApp con mensaje de recordatorio">
                <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Recordatorio WA
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

