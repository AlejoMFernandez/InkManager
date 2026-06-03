<?php
/** @var array|null $presupuesto  null → create mode */
/** @var array      $clientes */
/** @var int        $clienteIdPre */
/** @var array      $old */
/** @var string     $pageTitle */

$isEdit = $presupuesto !== null;
$csrf   = \App\Core\Auth::csrfToken();

// Helpers: prefer $_SESSION['old'] over $presupuesto
$val = fn(string $k, mixed $def = '') => $old[$k] ?? ($presupuesto[$k] ?? $def);
?>

<?php ob_start(); ?>
<style>
.form-label  { @apply block text-xs text-gray-500 mb-1.5 font-medium uppercase tracking-wide; }
.form-input  { @apply w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/30 transition-colors
                      placeholder-gray-600; }
.form-select { @apply w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/30 transition-colors; }
.form-textarea{ @apply w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm
                      focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/30 transition-colors
                      placeholder-gray-600 resize-y min-h-[80px]; }
</style>
<?php $extraHead = ob_get_clean(); ?>

<!-- Breadcrumb ──────────────────────────────────────────────────────── -->
<div class="flex items-center gap-2 text-xs text-gray-600 mb-5">
    <a href="<?= BASE_URL ?>/presupuestos" class="hover:text-gray-400 transition-colors">Presupuestos</a>
    <span>/</span>
    <span class="text-gray-400"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
</div>

<div class="flex items-center justify-between mb-6">
    <div>
        <p class="brand-tagline mb-1">// <?= $isEdit ? 'Editar' : 'Nuevo' ?></p>
        <h1 class="section-heading text-2xl"><?= htmlspecialchars($pageTitle) ?></h1>
    </div>
    <?php if ($isEdit): ?>
    <a href="<?= BASE_URL ?>/presupuestos/<?= (int)$presupuesto['id'] ?>"
       class="text-sm text-gray-500 hover:text-white transition-colors">← Volver</a>
    <?php else: ?>
    <a href="<?= BASE_URL ?>/presupuestos"
       class="text-sm text-gray-500 hover:text-white transition-colors">← Volver</a>
    <?php endif; ?>
</div>

<form method="POST"
      action="<?= BASE_URL ?>/presupuestos<?= $isEdit ? '/' . (int)$presupuesto['id'] . '/editar' : '/nuevo' ?>"
      class="space-y-6 max-w-3xl">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <!-- Card: datos principales -->
    <div class="card-base space-y-5">
        <h2 class="text-sm font-semibold text-gray-300 border-b border-gray-800 pb-3">Datos del presupuesto</h2>

        <!-- Cliente + Número (read-only) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Cliente <span class="text-red-500">*</span></label>
                <select name="cliente_id" required class="form-select">
                    <option value="">Seleccioná un cliente…</option>
                    <?php foreach ($clientes as $c):
                        $sel = ((int)$val('cliente_id', $clienteIdPre)) === (int)$c['id'] ? 'selected' : '';
                    ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $sel ?>><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($isEdit): ?>
            <div>
                <label class="form-label">Número</label>
                <input type="text" value="<?= htmlspecialchars($presupuesto['numero']) ?>"
                       disabled class="form-input opacity-50 cursor-not-allowed">
            </div>
            <?php else: ?>
            <div>
                <label class="form-label">Número</label>
                <div class="form-input flex items-center text-gray-600 select-none cursor-default">
                    Se asigna automáticamente
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Título -->
        <div>
            <label class="form-label">Título <span class="text-red-500">*</span></label>
            <input type="text" name="titulo" required
                   value="<?= htmlspecialchars((string)$val('titulo')) ?>"
                   placeholder="Ej: Tatuaje manga completa, boceto geométrico…"
                   class="form-input">
        </div>

        <!-- Descripción -->
        <div>
            <label class="form-label">Descripción
                <span class="text-gray-600 font-normal normal-case tracking-normal ml-1">— opcional</span>
            </label>
            <textarea name="descripcion" rows="4"
                      placeholder="Detallá el trabajo: zona del cuerpo, estilo, referencias de imagen, sesiones estimadas…"
                      class="form-textarea"><?= htmlspecialchars((string)$val('descripcion')) ?></textarea>
        </div>
    </div>

    <!-- Card: precio y validez -->
    <div class="card-base space-y-5">
        <h2 class="text-sm font-semibold text-gray-300 border-b border-gray-800 pb-3">Precio y validez</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Monto -->
            <div>
                <label class="form-label">Monto (ARS)
                    <span class="text-gray-600 font-normal normal-case tracking-normal ml-1">— opcional</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                    <input type="number" name="monto" min="0" step="0.01"
                           value="<?= htmlspecialchars((string)$val('monto')) ?>"
                           placeholder="0,00"
                           class="form-input pl-7">
                </div>
            </div>

            <!-- Validez -->
            <div>
                <label class="form-label">Validez</label>
                <select name="validez_dias" class="form-select">
                    <?php foreach ([7 => '7 días', 14 => '14 días', 30 => '30 días', 60 => '60 días', 90 => '90 días'] as $days => $label):
                        $sel = ((int)$val('validez_dias', 30)) === $days ? 'selected' : '';
                    ?>
                    <option value="<?= $days ?>" <?= $sel ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha -->
            <div>
                <label class="form-label">Fecha <span class="text-red-500">*</span></label>
                <input type="date" name="fecha" required
                       value="<?= htmlspecialchars((string)$val('fecha', date('Y-m-d'))) ?>"
                       class="form-input">
            </div>
        </div>

        <!-- Estado -->
        <div class="sm:w-1/2">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <?php
                $estados = ['borrador' => 'Borrador', 'enviado' => 'Enviado', 'aceptado' => 'Aceptado', 'rechazado' => 'Rechazado'];
                foreach ($estados as $k => $lbl):
                    $sel = ($val('estado', 'borrador') === $k) ? 'selected' : '';
                ?>
                <option value="<?= $k ?>" <?= $sel ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Card: notas internas -->
    <div class="card-base space-y-3">
        <h2 class="text-sm font-semibold text-gray-300 border-b border-gray-800 pb-3">Notas internas</h2>
        <textarea name="notas" rows="3"
                  placeholder="Notas privadas: acuerdos verbales, descuentos acordados, contacto…"
                  class="form-textarea"><?= htmlspecialchars((string)$val('notas')) ?></textarea>
        <p class="text-xs text-gray-600">Estas notas no se muestran al cliente en la versión impresa.</p>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-500
                       text-white text-sm font-semibold rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <?= $isEdit ? 'Guardar cambios' : 'Crear presupuesto' ?>
        </button>
        <a href="<?= BASE_URL ?>/presupuestos<?= $isEdit ? '/' . (int)$presupuesto['id'] : '' ?>"
           class="px-4 py-2.5 text-sm text-gray-500 hover:text-white transition-colors rounded-lg">
            Cancelar
        </a>
    </div>
</form>
