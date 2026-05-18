<?php
use App\Core\Auth;
$csrf    = Auth::csrfToken();
$isEdit  = $turno !== null;
$action  = $isEdit
    ? BASE_URL . '/turnos/' . $turno['id'] . '/editar'
    : BASE_URL . '/turnos/nuevo';

$val = fn(string $k, mixed $def = '') =>
    htmlspecialchars((string) ($old[$k] ?? ($turno[$k] ?? $def)));

// Pre-setear fecha desde querystring (click en slot del calendario)
$fechaDefault = '';
if (!$isEdit && !empty($fechaPreStr)) {
    // fechaPreStr puede ser ISO "2025-03-15T10:00:00" o "2025-03-15"
    $ts = strtotime($fechaPreStr);
    if ($ts) $fechaDefault = date('Y-m-d\TH:i', $ts);
}
$fechaVal = $isEdit
    ? date('Y-m-d\TH:i', strtotime($turno['fecha_inicio']))
    : ($old['fecha_inicio'] ?? $fechaDefault);

$estados = ['agendado','confirmado','hecho','cancelado'];
?>

<div class="max-w-xl">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/turnos" class="hover:text-white transition-colors">Turnos</a>
        <span>/</span>
        <span class="text-gray-300"><?= $isEdit ? 'Editar' : 'Nuevo' ?></span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-6">
            <?= $isEdit ? 'Editar turno' : 'Nuevo turno' ?>
        </h2>

        <form method="POST" action="<?= $action ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="space-y-4">

                <!-- Cliente -->
                <div>
                    <label for="cliente_id" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Cliente <span class="text-red-500">*</span>
                    </label>
                    <select id="cliente_id" name="cliente_id" required
                            class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                        <option value="">Seleccioná un cliente…</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= ((int)($old['cliente_id'] ?? $turno['cliente_id'] ?? $clienteIdPre)) === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                            <?= $c['instagram'] ? '(' . htmlspecialchars($c['instagram']) . ')' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Fecha y hora -->
                    <div class="col-span-2 sm:col-span-1">
                        <label for="fecha_inicio" class="block text-xs font-medium text-gray-400 mb-1.5">
                            Fecha y hora <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" id="fecha_inicio" name="fecha_inicio"
                               required
                               value="<?= htmlspecialchars($fechaVal) ?>"
                               class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                      text-white text-sm [color-scheme:dark]
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50">
                    </div>

                    <!-- Duración -->
                    <div class="col-span-2 sm:col-span-1">
                        <label for="duracion_min" class="block text-xs font-medium text-gray-400 mb-1.5">
                            Duración (minutos)
                        </label>
                        <select id="duracion_min" name="duracion_min"
                                class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                       text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                            <?php foreach ([30,60,90,120,150,180,240,300,360] as $min): ?>
                            <option value="<?= $min ?>"
                                <?= (int)($old['duracion_min'] ?? $turno['duracion_min'] ?? 60) === $min ? 'selected' : '' ?>>
                                <?= $min >= 60
                                    ? ($min/60 == floor($min/60) ? ($min/60).'h' : floor($min/60).'h '.($min%60).'min')
                                    : $min.'min' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="estado" class="block text-xs font-medium text-gray-400 mb-1.5">Estado</label>
                        <select id="estado" name="estado"
                                class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                       text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                            <?php foreach ($estados as $e): ?>
                            <option value="<?= $e ?>"
                                <?= ($old['estado'] ?? $turno['estado'] ?? 'agendado') === $e ? 'selected' : '' ?>>
                                <?= ucfirst($e) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Seña -->
                    <div>
                        <label for="sena" class="block text-xs font-medium text-gray-400 mb-1.5">Seña (ARS)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                            <input type="number" id="sena" name="sena"
                                   min="0" step="100"
                                   value="<?= $val('sena') ?>"
                                   class="w-full pl-6 pr-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                          text-white text-sm placeholder-gray-600
                                          focus:outline-none focus:ring-2 focus:ring-red-600/50"
                                   placeholder="5000">
                        </div>
                    </div>
                </div>

                <!-- Notas -->
                <div>
                    <label for="notas" class="block text-xs font-medium text-gray-400 mb-1.5">Notas</label>
                    <textarea id="notas" name="notas" rows="2"
                              class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                     text-white text-sm placeholder-gray-600 resize-none
                                     focus:outline-none focus:ring-2 focus:ring-red-600/50"
                              placeholder="Referencias, zona, observaciones…"><?= $val('notas') ?></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-800">
                <a href="<?= BASE_URL ?>/turnos"
                   class="text-sm text-gray-400 hover:text-white transition-colors">← Cancelar</a>
                <button type="submit"
                        class="btn-glow px-5 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-95 transition-all
                               focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    <?= $isEdit ? 'Guardar cambios' : 'Agendar turno' ?>
                </button>
            </div>
        </form>
    </div>

    <!-- Danger zone (solo editar) -->
    <?php if ($isEdit): ?>
    <div class="mt-4 bg-gray-900 border border-red-900/30 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-red-400 mb-2">Zona peligrosa</h3>
        <form method="POST" action="<?= BASE_URL ?>/turnos/<?= $turno['id'] ?>/borrar"
              onsubmit="return confirm('¿Eliminar este turno?')">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit"
                    class="px-4 py-2 border border-red-800 text-red-400 hover:bg-red-600/10
                           text-sm rounded-lg transition-colors">
                Eliminar turno
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>
