<?php
// $estilos array de la tabla estilos
// $csrf    string
?>

<!-- ══════════════════════════════════════════════════════════════════
     MODAL — Crear / Editar Tatuaje
════════════════════════════════════════════════════════════════════ -->
<div id="modal-form-overlay"
     class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm hidden"></div>

<div id="modal-form-tatuaje"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden pointer-events-none">
    <div class="w-full max-w-md bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl
                pointer-events-auto max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800 flex-shrink-0">
            <h3 id="form-modal-title" class="text-base font-semibold text-white">Nuevo tatuaje</h3>
            <button id="btn-cancel-form"
                    class="text-gray-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="tatuaje-form" enctype="multipart/form-data"
              class="overflow-y-auto flex-1 px-5 py-4 space-y-4">

            <!-- Foto upload -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">Foto</label>

                <!-- Dropzone -->
                <label id="form-foto-dropzone"
                       for="form-foto"
                       class="flex flex-col items-center justify-center gap-2 w-full h-32
                              border-2 border-dashed border-gray-700 rounded-xl cursor-pointer
                              hover:border-red-600/60 hover:bg-red-600/5 transition-colors">
                    <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021
                                 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span class="text-gray-500 text-xs text-center">
                        Arrastrá una foto o hacé click<br>
                        <span class="text-gray-700">JPG · PNG · WebP · máx. 5MB</span>
                    </span>
                </label>

                <!-- Preview -->
                <div class="relative hidden" id="form-foto-preview-wrap">
                    <img id="form-foto-preview"
                         src="" alt="preview"
                         class="hidden w-full h-40 object-cover rounded-xl border border-gray-700 mt-2">
                    <button type="button"
                            id="btn-remove-foto"
                            class="absolute top-4 right-2 w-6 h-6 rounded-full bg-black/60
                                   text-white flex items-center justify-center text-xs
                                   hover:bg-red-600 transition-colors">✕</button>
                </div>

                <input type="file" id="form-foto" name="foto"
                       accept="image/jpeg,image/png,image/webp"
                       class="sr-only">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- Estilo -->
                <div class="col-span-2">
                    <label for="form-estilo"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Estilo</label>
                    <select id="form-estilo" name="estilo_id"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm
                                   focus:outline-none focus:ring-2 focus:ring-red-600/50">
                        <option value="">Sin estilo</option>
                        <?php foreach ($estilos as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fecha -->
                <div>
                    <label for="form-fecha"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Fecha</label>
                    <input type="date" id="form-fecha" name="fecha"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm [color-scheme:dark]
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50">
                </div>

                <!-- Precio -->
                <div>
                    <label for="form-precio"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Precio (ARS)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input type="number" id="form-precio" name="precio"
                               min="0" step="100"
                               class="w-full pl-6 pr-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600
                                      focus:outline-none focus:ring-2 focus:ring-red-600/50"
                               placeholder="15000">
                    </div>
                </div>

                <!-- Sesiones -->
                <div>
                    <label for="form-ses-hechas"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Sesiones hechas</label>
                    <input type="number" id="form-ses-hechas" name="sesiones_hechas"
                           min="0" value="0"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50">
                </div>

                <div>
                    <label for="form-ses-total"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Sesiones totales</label>
                    <input type="number" id="form-ses-total" name="sesiones_totales"
                           min="1" value="1"
                           class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50">
                </div>

                <!-- Notas -->
                <div class="col-span-2">
                    <label for="form-notas"
                           class="block text-xs font-medium text-gray-400 mb-1.5">Notas</label>
                    <textarea id="form-notas" name="notas" rows="2"
                              class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                     text-white text-sm placeholder-gray-600 resize-none
                                     focus:outline-none focus:ring-2 focus:ring-red-600/50"
                              placeholder="Zona del cuerpo, referencias, detalles…"></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 pt-1 pb-1">
                <button type="button" id="btn-cancel-form-2"
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700
                               text-gray-300 text-sm rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-glow flex-1 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-[0.98] transition-all">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════
     MODAL — Detalle Tatuaje
════════════════════════════════════════════════════════════════════ -->
<div id="modal-detalle-overlay"
     class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm hidden"></div>

<div id="modal-detalle-tatuaje"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden pointer-events-none">
    <div class="w-full max-w-sm bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl
                pointer-events-auto overflow-hidden">

        <!-- Foto grande -->
        <div class="relative bg-gray-950 flex items-center justify-center"
             style="min-height:180px; max-height:260px">
            <img id="detail-foto"
                 src="" alt=""
                 class="hidden w-full object-cover" style="max-height:260px">
            <div id="detail-no-foto"
                 class="flex flex-col items-center gap-2 py-10 text-gray-700">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5
                             l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5
                             1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25
                             6v12a1.5 1.5 0 001.5 1.5z"/>
                </svg>
                <span class="text-sm">Sin foto</span>
            </div>

            <!-- Close button -->
            <button id="btn-close-detail"
                    class="absolute top-3 right-3 w-7 h-7 rounded-full bg-black/60
                           text-white flex items-center justify-center
                           hover:bg-red-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Info -->
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-3 mb-3">
                <h3 id="detail-estilo"
                    class="text-lg font-bold text-white"></h3>
                <div class="text-right flex-shrink-0">
                    <p id="detail-precio" class="text-red-400 font-semibold text-sm"></p>
                    <p id="detail-fecha"  class="text-gray-500 text-xs mt-0.5"></p>
                </div>
            </div>

            <!-- Sesiones progress -->
            <div class="mb-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Sesiones</span>
                    <span id="detail-sesiones" class="text-xs text-gray-400"></span>
                </div>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div id="detail-progress"
                         class="h-full bg-red-600 rounded-full transition-all duration-500"
                         style="width:0%"></div>
                </div>
            </div>

            <!-- Notas -->
            <div id="detail-notas-wrap" class="hidden mb-4">
                <p class="text-xs text-gray-600 mb-1">Notas</p>
                <p id="detail-notas"
                   class="text-gray-400 text-sm leading-relaxed"></p>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
                <button id="btn-edit-detail"
                        class="flex-1 py-2 bg-gray-800 hover:bg-gray-700
                               text-gray-300 text-sm rounded-lg transition-colors">
                    Editar
                </button>
                <button id="btn-delete-detail"
                        class="flex-1 py-2 bg-red-600/10 hover:bg-red-600/20
                               text-red-400 text-sm rounded-lg border border-red-600/20
                               hover:border-red-600/40 transition-colors">
                    Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Botón cancelar duplicado en el form
document.getElementById('btn-cancel-form-2')?.addEventListener('click', () => {
    document.getElementById('modal-form-overlay').classList.add('hidden');
    document.getElementById('modal-form-tatuaje').classList.add('hidden');
});

// Remover foto preview
document.getElementById('btn-remove-foto')?.addEventListener('click', () => {
    const input = document.getElementById('form-foto');
    const preview = document.getElementById('form-foto-preview');
    const dropzone = document.getElementById('form-foto-dropzone');
    if (input) input.value = '';
    if (preview) { preview.src = ''; preview.classList.add('hidden'); }
    if (dropzone) dropzone.classList.remove('hidden');
});

// Mostrar preview cuando se elige foto y sincronizar visibilidad del wrap
document.getElementById('form-foto')?.addEventListener('change', function() {
    const wrap = document.getElementById('form-foto-preview-wrap');
    const preview = document.getElementById('form-foto-preview');
    const dropzone = document.getElementById('form-foto-dropzone');
    if (this.files?.[0]) {
        if (wrap) wrap.classList.remove('hidden');
        if (preview) { preview.src = URL.createObjectURL(this.files[0]); preview.classList.remove('hidden'); }
        if (dropzone) dropzone.classList.add('hidden');
    }
});
</script>
