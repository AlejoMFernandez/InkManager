<?php
/** @var array  $clientes  Lista de clientes */
/** @var array  $old       Valores previos en caso de error */
/** @var string $csrf      CSRF token */

use App\Models\Galeria;

$val     = fn(string $k) => htmlspecialchars($old[$k] ?? '');
$estilos = Galeria::ESTILOS;

$estiloLabel = [
    'tradicional'    => 'Tradicional',
    'neo-tradicional'=> 'Neo-trad',
    'realismo'       => 'Realismo',
    'blackwork'      => 'Blackwork',
    'watercolor'     => 'Watercolor',
    'linework'       => 'Linework',
    'geometric'      => 'Geometric',
    'japonés'        => 'Japonés',
    'chicano'        => 'Chicano',
    'otro'           => 'Otro',
];
?>

<div class="max-w-xl">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/galeria" class="hover:text-white transition-colors">Galería</a>
        <span>/</span>
        <span class="text-gray-300">Subir foto</span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-6">Nueva foto</h2>

        <form method="POST" action="<?= BASE_URL ?>/galeria/subir"
              enctype="multipart/form-data" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="space-y-5">

                <!-- Drop zone -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">
                        Imagen <span class="text-red-500">*</span>
                        <span class="text-gray-600 font-normal ml-1">— JPG, PNG, WEBP, GIF · máx. 8 MB</span>
                    </label>
                    <div id="drop-zone"
                         class="relative border-2 border-dashed border-gray-700 rounded-xl
                                transition-colors cursor-pointer hover:border-gray-500
                                flex flex-col items-center justify-center text-center
                                min-h-[180px] overflow-hidden"
                         onclick="document.getElementById('foto-input').click()">

                        <!-- Placeholder -->
                        <div id="drop-placeholder" class="py-8 px-4 pointer-events-none">
                            <svg class="w-10 h-10 text-gray-600 mx-auto mb-3" fill="none"
                                 stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0
                                         0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                            </svg>
                            <p class="text-gray-400 text-sm font-medium">Hacé clic o arrastrá una imagen</p>
                            <p class="text-gray-600 text-xs mt-1">JPG · PNG · WEBP · GIF</p>
                        </div>

                        <!-- Preview -->
                        <img id="drop-preview"
                             src="" alt=""
                             class="hidden absolute inset-0 w-full h-full object-cover rounded-xl">

                        <!-- Overlay sobre preview -->
                        <div id="drop-overlay"
                             class="hidden absolute inset-0 flex items-center justify-center
                                    bg-black/40 rounded-xl opacity-0 hover:opacity-100 transition-opacity">
                            <span class="text-white text-xs font-medium bg-black/60 px-3 py-1.5 rounded-lg">
                                Cambiar imagen
                            </span>
                        </div>
                    </div>

                    <input type="file" id="foto-input" name="foto"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           class="hidden" required>
                </div>

                <!-- Título (opcional) -->
                <div>
                    <label for="titulo" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Título
                        <span class="text-gray-600 font-normal ml-1">— opcional</span>
                    </label>
                    <input type="text" id="titulo" name="titulo"
                           value="<?= $val('titulo') ?>"
                           maxlength="150"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="ej. Manga japonesa, Rosa neo-trad…">
                </div>

                <!-- Estilo -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-2">
                        Estilo
                        <span class="text-gray-600 font-normal ml-1">— opcional</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $estiloActual = $old['estilo'] ?? '';
                        foreach ($estilos as $e):
                            $active = $estiloActual === $e;
                        ?>
                        <label data-estilo-label
                               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer
                                      select-none text-sm transition-all
                                      <?= $active
                                          ? 'border-red-500/50 bg-red-500/10 text-white'
                                          : 'border-gray-700 hover:border-gray-600 text-gray-400' ?>">
                            <input type="radio" name="estilo" value="<?= htmlspecialchars($e) ?>"
                                   <?= $active ? 'checked' : '' ?>
                                   class="sr-only">
                            <?= htmlspecialchars($estiloLabel[$e] ?? $e) ?>
                        </label>
                        <?php endforeach; ?>
                        <!-- Opción "sin estilo" -->
                        <label data-estilo-label
                               class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border cursor-pointer
                                      select-none text-sm transition-all
                                      <?= $estiloActual === ''
                                          ? 'border-gray-600 bg-gray-700/50 text-gray-300'
                                          : 'border-gray-700 hover:border-gray-600 text-gray-500' ?>">
                            <input type="radio" name="estilo" value=""
                                   <?= $estiloActual === '' ? 'checked' : '' ?>
                                   class="sr-only">
                            Sin especificar
                        </label>
                    </div>
                </div>

                <!-- Cliente (opcional) -->
                <div>
                    <label for="cliente_id" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Cliente
                        <span class="text-gray-600 font-normal ml-1">— opcional</span>
                    </label>
                    <select id="cliente_id" name="cliente_id"
                            class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm
                                   focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                   [color-scheme:dark]">
                        <option value="">— Sin vincular —</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"
                                <?= ($old['cliente_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div><!-- /space-y-5 -->

            <!-- Actions -->
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-800">
                <a href="<?= BASE_URL ?>/galeria"
                   class="text-sm text-gray-400 hover:text-white transition-colors">
                    ← Cancelar
                </a>
                <button type="submit"
                        class="btn-glow px-5 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-95 transition-all
                               focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    Subir foto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var input    = document.getElementById('foto-input');
    var zone     = document.getElementById('drop-zone');
    var preview  = document.getElementById('drop-preview');
    var placeholder = document.getElementById('drop-placeholder');
    var overlay  = document.getElementById('drop-overlay');

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            overlay.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () {
        if (this.files[0]) showPreview(this.files[0]);
    });

    // Drag & drop
    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.classList.add('border-red-500/50', 'bg-red-500/5');
    });
    zone.addEventListener('dragleave', function () {
        zone.classList.remove('border-red-500/50', 'bg-red-500/5');
    });
    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('border-red-500/50', 'bg-red-500/5');
        var file = e.dataTransfer.files[0];
        if (file) {
            // Transferir al input para que se incluya en el POST
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        }
    });

    // Estilo pills
    function syncEstilo() {
        document.querySelectorAll('[data-estilo-label]').forEach(function (label) {
            var radio  = label.querySelector('input[type=radio]');
            var active = radio && radio.checked;
            var isEmpty = radio && radio.value === '';
            label.classList.toggle('border-red-500/50',   active && !isEmpty);
            label.classList.toggle('bg-red-500/10',        active && !isEmpty);
            label.classList.toggle('text-white',           active && !isEmpty);
            label.classList.toggle('border-gray-600',      active && isEmpty);
            label.classList.toggle('bg-gray-700/50',       active && isEmpty);
            label.classList.toggle('text-gray-300',        active && isEmpty);
            label.classList.toggle('border-gray-700',      !active);
            label.classList.toggle('text-gray-400',        !active && !isEmpty);
            label.classList.toggle('text-gray-500',        !active && isEmpty);
        });
    }
    document.querySelectorAll('[name="estilo"]').forEach(function (r) {
        r.addEventListener('change', syncEstilo);
    });
})();
</script>
