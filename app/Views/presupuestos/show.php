<?php
/** @var array $presupuesto */

$csrf = \App\Core\Auth::csrfToken();

$estadoColors = [
    'borrador'  => 'text-gray-400  bg-gray-800/60  border-gray-700',
    'enviado'   => 'text-blue-400  bg-blue-900/30  border-blue-800',
    'aceptado'  => 'text-green-400 bg-green-900/30 border-green-800',
    'rechazado' => 'text-red-400   bg-red-900/30   border-red-800',
];
$estadoLabels = [
    'borrador'  => 'Borrador',
    'enviado'   => 'Enviado',
    'aceptado'  => 'Aceptado',
    'rechazado' => 'Rechazado',
];

$badgeCls = $estadoColors[$presupuesto['estado']] ?? 'text-gray-400 bg-gray-800 border-gray-700';
$badgeTxt = $estadoLabels[$presupuesto['estado']] ?? ucfirst($presupuesto['estado']);

// Calculate expiry date
$fechaExpira = date('Y-m-d', strtotime($presupuesto['fecha'] . ' +' . $presupuesto['validez_dias'] . ' days'));
$expirado    = $fechaExpira < date('Y-m-d') && $presupuesto['estado'] === 'enviado';
?>

<?php ob_start(); ?>
<style>
.status-btn {
    @apply w-full text-left px-3 py-2 rounded-lg text-sm transition-colors font-medium;
}
.status-btn-borrador  { @apply text-gray-400 hover:bg-gray-800 hover:text-white; }
.status-btn-enviado   { @apply text-blue-400  hover:bg-blue-900/30 hover:text-blue-300; }
.status-btn-aceptado  { @apply text-green-400 hover:bg-green-900/30 hover:text-green-300; }
.status-btn-rechazado { @apply text-red-400   hover:bg-red-900/30 hover:text-red-300; }
</style>
<?php $extraHead = ob_get_clean(); ?>

<!-- Breadcrumb ──────────────────────────────────────────────────────── -->
<div class="flex items-center gap-2 text-xs text-gray-600 mb-5">
    <a href="<?= BASE_URL ?>/presupuestos" class="hover:text-gray-400 transition-colors">Presupuestos</a>
    <span>/</span>
    <span class="text-gray-400 font-mono"><?= htmlspecialchars($presupuesto['numero']) ?></span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Main column ──────────────────────────────────────────────────── -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Header card -->
        <div class="card-base">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <p class="brand-tagline mb-1">// Presupuesto</p>
                    <h1 class="section-heading text-xl leading-snug">
                        <?= htmlspecialchars($presupuesto['titulo']) ?>
                    </h1>
                    <p class="text-gray-500 text-sm mt-1">
                        Para
                        <a href="<?= BASE_URL ?>/clientes/<?= (int)$presupuesto['cliente_id'] ?>"
                           class="text-gray-300 hover:text-white transition-colors">
                            <?= htmlspecialchars($presupuesto['cliente_nombre']) ?>
                        </a>
                        <?php if (!empty($presupuesto['cliente_instagram'])): ?>
                        · <span class="text-gray-600">@<?= htmlspecialchars($presupuesto['cliente_instagram']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full border text-sm font-medium flex-shrink-0 <?= $badgeCls ?>">
                    <?= $badgeTxt ?>
                </span>
            </div>

            <!-- Meta row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-gray-800">
                <div>
                    <p class="text-xs text-gray-600 mb-0.5">Número</p>
                    <p class="text-sm font-mono text-gray-300"><?= htmlspecialchars($presupuesto['numero']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 mb-0.5">Fecha</p>
                    <p class="text-sm text-gray-300"><?= date('d/m/Y', strtotime($presupuesto['fecha'])) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 mb-0.5">Vence</p>
                    <p class="text-sm <?= $expirado ? 'text-red-400' : 'text-gray-300' ?>">
                        <?= date('d/m/Y', strtotime($fechaExpira)) ?>
                        <?php if ($expirado): ?>
                        <span class="text-xs text-red-500 ml-1">vencido</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-600 mb-0.5">Monto</p>
                    <?php if ($presupuesto['monto'] !== null && $presupuesto['monto'] !== ''): ?>
                    <p class="text-sm text-white font-semibold font-mono">
                        $<?= number_format((float)$presupuesto['monto'], 2, ',', '.') ?>
                    </p>
                    <?php else: ?>
                    <p class="text-sm text-gray-600">A convenir</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Descripción -->
        <?php if (!empty($presupuesto['descripcion'])): ?>
        <div class="card-base">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Descripción</h3>
            <div class="text-sm text-gray-300 whitespace-pre-wrap leading-relaxed">
                <?= nl2br(htmlspecialchars($presupuesto['descripcion'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Notas internas -->
        <?php if (!empty($presupuesto['notas'])): ?>
        <div class="card-base border-dashed border-gray-700">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas internas</h3>
            </div>
            <div class="text-sm text-gray-400 whitespace-pre-wrap leading-relaxed">
                <?= nl2br(htmlspecialchars($presupuesto['notas'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions: edit / print / delete -->
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?= BASE_URL ?>/presupuestos/<?= (int)$presupuesto['id'] ?>/editar"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600
                      text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
            <a href="<?= BASE_URL ?>/presupuestos/<?= (int)$presupuesto['id'] ?>/imprimir"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600
                      text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir / PDF
            </a>
            <!-- Agendar turno link -->
            <a href="<?= BASE_URL ?>/turnos/nuevo?cliente_id=<?= (int)$presupuesto['cliente_id'] ?>"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600/15 hover:bg-red-600/25 border border-red-600/30
                      text-red-400 hover:text-red-300 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Agendar turno
            </a>
            <?php if (!empty($presupuesto['cliente_telefono'])): ?>
            <?php
                $waPhone  = ltrim(preg_replace('/[^\d]/', '', $presupuesto['cliente_telefono']), '0');
                $montoStr = $presupuesto['monto'] !== null
                    ? '$' . number_format((float)$presupuesto['monto'], 0, ',', '.')
                    : 'a convenir';
                $waMsg = "Hola {$presupuesto['cliente_nombre']}! 👋\n\n"
                       . "Te compartimos el presupuesto *{$presupuesto['numero']}*"
                       . " — _{$presupuesto['titulo']}_ "
                       . "por *{$montoStr}*.\n\n"
                       . "¡Quedamos a tu disposición para cualquier consulta! 💬";
                $waHref = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMsg);
            ?>
            <a href="<?= $waHref ?>" target="_blank" rel="noopener noreferrer"
               title="Enviar presupuesto por WhatsApp"
               class="inline-flex items-center gap-2 px-4 py-2
                      bg-green-600/15 hover:bg-green-600/25 border border-green-600/30
                      text-green-400 hover:text-green-300 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Enviar por WA
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar column ───────────────────────────────────────────────── -->
    <div class="space-y-5">

        <!-- Estado rápido -->
        <div class="card-base">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Cambiar estado</h3>
            <form method="POST" action="<?= BASE_URL ?>/presupuestos/<?= (int)$presupuesto['id'] ?>/estado" class="space-y-1">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <?php
                $estados = ['borrador' => 'Borrador', 'enviado' => 'Enviado', 'aceptado' => 'Aceptado', 'rechazado' => 'Rechazado'];
                foreach ($estados as $k => $lbl):
                    $isCurrent = $presupuesto['estado'] === $k;
                ?>
                <button type="submit" name="estado" value="<?= $k ?>"
                        class="status-btn status-btn-<?= $k ?> <?= $isCurrent ? 'ring-1 ring-inset ring-current' : '' ?>">
                    <span class="inline-flex items-center gap-2">
                        <?php if ($isCurrent): ?>
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <?php else: ?>
                        <span class="w-3.5 h-3.5"></span>
                        <?php endif; ?>
                        <?= $lbl ?>
                    </span>
                </button>
                <?php endforeach; ?>
            </form>
        </div>

        <!-- Info card -->
        <div class="card-base text-sm space-y-3">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Información</h3>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Validez</span>
                <span class="text-gray-300"><?= (int)$presupuesto['validez_dias'] ?> días</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Creado</span>
                <span class="text-gray-300"><?= date('d/m/Y', strtotime($presupuesto['created_at'])) ?></span>
            </div>
            <?php if ($presupuesto['created_at'] !== $presupuesto['updated_at']): ?>
            <div class="flex justify-between items-center">
                <span class="text-gray-500">Modificado</span>
                <span class="text-gray-300"><?= date('d/m/Y H:i', strtotime($presupuesto['updated_at'])) ?></span>
            </div>
            <?php endif; ?>
            <div class="pt-2 border-t border-gray-800">
                <a href="<?= BASE_URL ?>/clientes/<?= (int)$presupuesto['cliente_id'] ?>"
                   class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Ver ficha de <?= htmlspecialchars($presupuesto['cliente_nombre']) ?>
                </a>
            </div>
        </div>

        <!-- Danger zone -->
        <div class="card-base border-red-900/40">
            <h3 class="text-xs font-semibold text-red-500/70 uppercase tracking-wider mb-3">Zona peligrosa</h3>
            <form method="POST" action="<?= BASE_URL ?>/presupuestos/<?= (int)$presupuesto['id'] ?>/borrar"
                  onsubmit="return confirm('¿Eliminar el presupuesto <?= htmlspecialchars($presupuesto['numero'], ENT_QUOTES) ?>? Esta acción no se puede deshacer.')">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit"
                        class="w-full text-left px-3 py-2 text-sm text-red-500/70 hover:text-red-400
                               hover:bg-red-900/20 rounded-lg transition-colors">
                    Eliminar presupuesto
                </button>
            </form>
        </div>
    </div>

</div>
