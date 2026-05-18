<?php
// $cliente = null → crear | array → editar
// $old     = array de valores anteriores en caso de error
$isEdit  = $cliente !== null;
$action  = $isEdit
    ? BASE_URL . '/clientes/' . $cliente['id'] . '/editar'
    : BASE_URL . '/clientes/nuevo';

use App\Core\Auth;
$csrf = Auth::csrfToken();

// Valores a mostrar: si hay $old (error de validación) esos tienen prioridad
$val = fn(string $k) => htmlspecialchars(
    $old[$k] ?? ($cliente[$k] ?? '')
);
?>

<div class="max-w-2xl">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/clientes" class="hover:text-white transition-colors">Clientes</a>
        <span>/</span>
        <span class="text-gray-300"><?= $isEdit ? htmlspecialchars($cliente['nombre']) : 'Nuevo' ?></span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">

        <h2 class="text-lg font-semibold text-white mb-6">
            <?= $isEdit ? 'Editar cliente' : 'Nuevo cliente' ?>
        </h2>

        <form method="POST" action="<?= $action ?>" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Nombre -->
                <div class="sm:col-span-2">
                    <label for="nombre" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?= $val('nombre') ?>"
                           required maxlength="120"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="Valentina Ruiz">
                </div>

                <!-- Instagram -->
                <div>
                    <label for="instagram" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Instagram
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">@</span>
                        <input type="text" id="instagram" name="instagram"
                               value="<?= ltrim($val('instagram'), '@') ?>"
                               maxlength="80"
                               class="w-full pl-7 pr-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                               placeholder="vale.ruiz">
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Teléfono
                    </label>
                    <input type="tel" id="telefono" name="telefono"
                           value="<?= $val('telefono') ?>"
                           maxlength="30"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="+54 11 1234-5678">
                </div>

                <!-- Primera visita -->
                <div>
                    <label for="primera_visita" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Primera visita
                    </label>
                    <input type="date" id="primera_visita" name="primera_visita"
                           value="<?= $val('primera_visita') ?>"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                  [color-scheme:dark]">
                </div>

                <!-- Notas -->
                <div class="sm:col-span-2">
                    <label for="notas" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Notas
                    </label>
                    <textarea id="notas" name="notas" rows="3"
                              class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                     text-white text-sm placeholder-gray-600 resize-y
                                     focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                              placeholder="Preferencias, alergias, referencias…"><?= $val('notas') ?></textarea>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-800">
                <a href="<?= $isEdit ? BASE_URL . '/clientes/' . $cliente['id'] : BASE_URL . '/clientes' ?>"
                   class="text-sm text-gray-400 hover:text-white transition-colors">
                    ← Cancelar
                </a>
                <button type="submit"
                        class="btn-glow px-5 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-95 transition-all
                               focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    <?= $isEdit ? 'Guardar cambios' : 'Crear cliente' ?>
                </button>
            </div>

        </form>
    </div>

    <?php if ($isEdit): ?>
    <!-- Danger zone -->
    <div class="mt-4 bg-gray-900 border border-red-900/30 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-red-400 mb-2">Zona peligrosa</h3>
        <p class="text-gray-500 text-xs mb-3">
            Eliminar este cliente borrará también <strong class="text-gray-400">todos sus tatuajes y turnos</strong>.
            Esta acción no se puede deshacer.
        </p>
        <form method="POST"
              action="<?= BASE_URL ?>/clientes/<?= $cliente['id'] ?>/borrar"
              onsubmit="return confirm('¿Estás seguro? Se eliminarán todos los datos de <?= htmlspecialchars(addslashes($cliente['nombre'])) ?>')">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit"
                    class="px-4 py-2 border border-red-800 text-red-400 hover:bg-red-600/10
                           text-sm rounded-lg transition-colors">
                Eliminar cliente
            </button>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
    // Normalizar arroba en Instagram al submit
    document.querySelector('form').addEventListener('submit', function() {
        const ig = document.getElementById('instagram');
        const v  = ig.value.trim();
        if (v && !v.startsWith('@')) ig.value = '@' + v;
    });
</script>
