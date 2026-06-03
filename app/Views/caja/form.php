<?php
/** @var array       $clientes     Lista de clientes del estudio */
/** @var array       $old          Valores previos en caso de error */
/** @var string      $csrf         CSRF token */
/** @var array|null  $turnoOrigen  Turno desde el que se llegó (botón Cobrar), o null */

use App\Models\Pago;

$val = fn(string $k) => htmlspecialchars($old[$k] ?? '');

$tipoActual   = $old['tipo']   ?? 'ingreso';
$metodoActual = $old['metodo'] ?? 'efectivo';
?>

<div class="max-w-xl">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/caja" class="hover:text-white transition-colors">Caja</a>
        <span>/</span>
        <span class="text-gray-300">Registrar cobro</span>
    </div>

    <?php if (!empty($turnoOrigen)): ?>
    <!-- Banner "desde turno" -->
    <div class="mb-4 px-4 py-3 rounded-xl border border-emerald-500/30 bg-emerald-500/[.07]
                flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-full bg-emerald-500/15 border border-emerald-500/30
                        flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1
                             2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25
                             2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21
                             18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25
                             0 0 1 21 9v7.5"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-emerald-300 text-sm font-medium truncate">
                    Cobrando turno de <?= htmlspecialchars($turnoOrigen['cliente_nombre']) ?>
                </p>
                <p class="text-emerald-700 text-xs">
                    <?= date('d/m/Y \a\l\a\s H:i', strtotime($turnoOrigen['fecha_inicio'])) ?>
                    <?php if (!empty($turnoOrigen['sena']) && (float)$turnoOrigen['sena'] > 0): ?>
                    &middot; Seña registrada: $<?= number_format((float)$turnoOrigen['sena'], 0, ',', '.') ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/turnos/<?= $turnoOrigen['id'] ?>"
           class="text-xs text-emerald-700 hover:text-emerald-400 transition-colors whitespace-nowrap flex-shrink-0">
            ← Volver
        </a>
    </div>
    <?php endif; ?>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-6">Nuevo registro</h2>

        <form method="POST" action="<?= BASE_URL ?>/caja/nuevo" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <?php if (!empty($old['_turno_id'])): ?>
            <input type="hidden" name="_turno_id" value="<?= (int)$old['_turno_id'] ?>">
            <?php endif; ?>

            <!-- Tipo: Ingreso / Egreso -->
            <div class="mb-5">
                <label class="block text-xs font-medium text-gray-400 mb-2">Tipo</label>
                <div class="grid grid-cols-2 gap-3" id="tipo-selector">
                    <label data-tipo-label
                           class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer
                                  transition-all select-none
                                  <?= $tipoActual === 'ingreso'
                                      ? 'border-emerald-500/50 bg-emerald-500/10'
                                      : 'border-gray-700 hover:border-gray-600' ?>">
                        <input type="radio" name="tipo" value="ingreso"
                               <?= $tipoActual === 'ingreso' ? 'checked' : '' ?>
                               class="sr-only">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center
                                    flex-shrink-0 bg-emerald-500/15 border border-emerald-500/30">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold <?= $tipoActual === 'ingreso' ? 'text-emerald-300' : 'text-gray-300' ?>">
                                Ingreso
                            </p>
                            <p class="text-xs text-gray-600 leading-tight">Cobro, pago recibido</p>
                        </div>
                    </label>

                    <label data-tipo-label
                           class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer
                                  transition-all select-none
                                  <?= $tipoActual === 'egreso'
                                      ? 'border-red-500/50 bg-red-500/10'
                                      : 'border-gray-700 hover:border-gray-600' ?>">
                        <input type="radio" name="tipo" value="egreso"
                               <?= $tipoActual === 'egreso' ? 'checked' : '' ?>
                               class="sr-only">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center
                                    flex-shrink-0 bg-red-500/15 border border-red-500/30">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor"
                                 stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19.5 12h-15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold <?= $tipoActual === 'egreso' ? 'text-red-300' : 'text-gray-300' ?>">
                                Egreso
                            </p>
                            <p class="text-xs text-gray-600 leading-tight">Gasto, compra de material</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Concepto -->
                <div class="sm:col-span-2">
                    <label for="concepto" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Concepto <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="concepto" name="concepto"
                           value="<?= $val('concepto') ?>"
                           required maxlength="200" autofocus
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="Sesión tatuaje, tinta negra, etc.">
                </div>

                <!-- Monto -->
                <div>
                    <label for="monto" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Monto <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">$</span>
                        <input type="number" id="monto" name="monto"
                               value="<?= $val('monto') ?>"
                               required min="0.01" step="0.01"
                               class="w-full pl-8 pr-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600 tabular-nums
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                               placeholder="0,00">
                    </div>
                </div>

                <!-- Fecha -->
                <div>
                    <label for="fecha" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Fecha
                    </label>
                    <input type="date" id="fecha" name="fecha"
                           value="<?= $val('fecha') ?: date('Y-m-d') ?>"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                  [color-scheme:dark]">
                </div>

                <!-- Método -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-2">Método de pago</label>
                    <div class="flex flex-wrap gap-2" id="metodo-selector">
                        <?php
                        $metodoLabels = [
                            'efectivo'      => ['💵', 'Efectivo'],
                            'transferencia' => ['🏦', 'Transferencia'],
                            'tarjeta'       => ['💳', 'Tarjeta'],
                            'sena'          => ['📌', 'Seña'],
                            'otro'          => ['⚙️', 'Otro'],
                        ];
                        foreach ($metodoLabels as $mVal => [$icon, $mLabel]):
                            $isActive = $metodoActual === $mVal;
                        ?>
                        <label data-metodo-label
                               class="flex items-center gap-1.5 px-3 py-2 rounded-lg border cursor-pointer
                                      transition-all select-none text-sm
                                      <?= $isActive
                                          ? 'border-red-500/50 bg-red-500/10 text-white'
                                          : 'border-gray-700 hover:border-gray-600 text-gray-400' ?>">
                            <input type="radio" name="metodo" value="<?= $mVal ?>"
                                   <?= $isActive ? 'checked' : '' ?>
                                   class="sr-only">
                            <span><?= $icon ?></span>
                            <span class="font-medium"><?= $mLabel ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Cliente (opcional) -->
                <div class="sm:col-span-2">
                    <label for="cliente_id" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Cliente
                        <span class="text-gray-600 font-normal ml-1">— opcional</span>
                    </label>
                    <select id="cliente_id" name="cliente_id"
                            class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm
                                   focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                   [color-scheme:dark]">
                        <option value="">— Sin cliente —</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"
                                <?= ($old['cliente_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Notas -->
                <div class="sm:col-span-2">
                    <label for="notas" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Notas internas
                        <span class="text-gray-600 font-normal ml-1">— opcional</span>
                    </label>
                    <textarea id="notas" name="notas" rows="2"
                              class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                     text-white text-sm placeholder-gray-600 resize-none
                                     focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                              placeholder="Observaciones adicionales…"><?= $val('notas') ?></textarea>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-800">
                <?php if (!empty($turnoOrigen)): ?>
                <a href="<?= BASE_URL ?>/turnos/<?= $turnoOrigen['id'] ?>"
                   class="text-sm text-gray-400 hover:text-white transition-colors">
                    ← Volver al turno
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/caja"
                   class="text-sm text-gray-400 hover:text-white transition-colors">
                    ← Cancelar
                </a>
                <?php endif; ?>
                <button type="submit"
                        class="btn-glow px-5 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-95 transition-all
                               focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    Registrar cobro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // Tipo selector (Ingreso / Egreso)
    function syncTipo() {
        document.querySelectorAll('[data-tipo-label]').forEach(function (label) {
            var radio  = label.querySelector('input[type=radio]');
            var active = radio && radio.checked;
            var isIng  = radio && radio.value === 'ingreso';

            label.classList.toggle('border-emerald-500/50', active && isIng);
            label.classList.toggle('bg-emerald-500/10',     active && isIng);
            label.classList.toggle('border-red-500/50',     active && !isIng);
            label.classList.toggle('bg-red-500/10',         active && !isIng);
            label.classList.toggle('border-gray-700',       !active);
            label.classList.toggle('hover:border-gray-600', !active);

            var title = label.querySelector('p');
            if (title) {
                title.classList.toggle('text-emerald-300', active && isIng);
                title.classList.toggle('text-red-300',     active && !isIng);
                title.classList.toggle('text-gray-300',    !active);
            }
        });
    }
    document.querySelectorAll('[name="tipo"]').forEach(function (r) {
        r.addEventListener('change', syncTipo);
    });

    // Método selector
    function syncMetodo() {
        document.querySelectorAll('[data-metodo-label]').forEach(function (label) {
            var radio  = label.querySelector('input[type=radio]');
            var active = radio && radio.checked;
            label.classList.toggle('border-red-500/50', active);
            label.classList.toggle('bg-red-500/10',     active);
            label.classList.toggle('text-white',        active);
            label.classList.toggle('border-gray-700',   !active);
            label.classList.toggle('text-gray-400',     !active);
        });
    }
    document.querySelectorAll('[name="metodo"]').forEach(function (r) {
        r.addEventListener('change', syncMetodo);
    });
})();
</script>
