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
                              placeholder="Referencias, estilo, detalles…"></textarea>
                </div>

                <!-- ── Tamaño ─────────────────────────────────────────────── -->
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-2">
                        Tamaño del tatuaje
                    </label>
                    <input type="hidden" name="tamano" id="form-tamano" value="m">
                    <div class="flex gap-1.5" id="tamano-pills">
                        <button type="button" data-tamano="xs"
                                class="tamano-pill flex-1 py-1.5 text-xs font-semibold rounded-lg
                                       border border-gray-700 text-gray-500 transition-colors
                                       hover:text-white hover:border-gray-500">XS</button>
                        <button type="button" data-tamano="s"
                                class="tamano-pill flex-1 py-1.5 text-xs font-semibold rounded-lg
                                       border border-gray-700 text-gray-500 transition-colors
                                       hover:text-white hover:border-gray-500">S</button>
                        <button type="button" data-tamano="m"
                                class="tamano-pill flex-1 py-1.5 text-xs font-semibold rounded-lg
                                       border border-gray-700 text-gray-500 transition-colors
                                       hover:text-white hover:border-gray-500">M</button>
                        <button type="button" data-tamano="l"
                                class="tamano-pill flex-1 py-1.5 text-xs font-semibold rounded-lg
                                       border border-gray-700 text-gray-500 transition-colors
                                       hover:text-white hover:border-gray-500">L</button>
                        <button type="button" data-tamano="xl"
                                class="tamano-pill flex-1 py-1.5 text-xs font-semibold rounded-lg
                                       border border-gray-700 text-gray-500 transition-colors
                                       hover:text-white hover:border-gray-500">XL</button>
                    </div>
                    <p class="text-xs text-gray-700 mt-1" id="tamano-hint"></p>
                </div>

                <!-- ── Zona de cobertura (visible para L y XL) ───────────── -->
                <div class="col-span-2" id="zona-wrap" style="display:none">
                    <label for="form-zona"
                           class="block text-xs font-medium text-gray-400 mb-1.5">
                        Zona de cobertura
                        <span class="text-gray-700 font-normal ml-1">— para ver el área en el 3D</span>
                    </label>
                    <select id="form-zona" name="zona"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                        <option value="">— Sin zona específica</option>
                        <option value="arm-left">Brazo izquierdo (bícep)</option>
                        <option value="arm-right">Brazo derecho (bícep)</option>
                        <option value="forearm-left">Antebrazo izquierdo</option>
                        <option value="forearm-right">Antebrazo derecho</option>
                        <option value="sleeve-left">Manga completa izquierda ★</option>
                        <option value="sleeve-right">Manga completa derecha ★</option>
                        <option value="chest">Pecho</option>
                        <option value="back">Espalda</option>
                        <option value="abdomen">Abdomen</option>
                        <option value="leg-left">Muslo izquierdo</option>
                        <option value="leg-right">Muslo derecho</option>
                        <option value="calf-left">Pantorrilla izquierda</option>
                        <option value="calf-right">Pantorrilla derecha</option>
                        <option value="neck">Cuello / Nuca</option>
                    </select>
                </div>

                <!-- ── Tipo de tinta (visible para L y XL) ───────────────── -->
                <div class="col-span-2" id="tinta-wrap" style="display:none">
                    <label class="block text-xs font-medium text-gray-400 mb-2">
                        Tipo de tinta
                    </label>
                    <input type="hidden" name="tinta" id="form-tinta" value="negro">
                    <div class="flex gap-2">
                        <button type="button" data-tinta="negro"
                                class="tinta-btn flex-1 flex items-center gap-2 px-3 py-2
                                       rounded-lg border border-gray-700 text-gray-500
                                       hover:text-white hover:border-gray-500 transition-colors text-xs">
                            <span class="w-3 h-3 rounded-full flex-shrink-0 bg-[#2a2a5a]"></span>
                            Negro / Blackout
                        </button>
                        <button type="button" data-tinta="gris"
                                class="tinta-btn flex-1 flex items-center gap-2 px-3 py-2
                                       rounded-lg border border-gray-700 text-gray-500
                                       hover:text-white hover:border-gray-500 transition-colors text-xs">
                            <span class="w-3 h-3 rounded-full flex-shrink-0 bg-[#9090c0]"></span>
                            Gris / B&amp;W
                        </button>
                        <button type="button" data-tinta="color"
                                class="tinta-btn flex-1 flex items-center gap-2 px-3 py-2
                                       rounded-lg border border-gray-700 text-gray-500
                                       hover:text-white hover:border-gray-500 transition-colors text-xs">
                            <span class="w-3 h-3 rounded-full flex-shrink-0 bg-[#ff3366]"></span>
                            Color
                        </button>
                    </div>
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
        <div class="relative bg-gray-950 flex items-center justify-center group/dfoto"
             style="min-height:180px; max-height:260px">

            <!-- Foto (click → lightbox) -->
            <img id="detail-foto"
                 src="" alt=""
                 class="hidden w-full object-cover cursor-zoom-in"
                 style="max-height:260px"
                 title="Ver foto completa">

            <!-- Botón cámara: cambiar foto cuando ya hay una -->
            <label id="detail-upload-btn"
                   for="detail-foto-input"
                   style="display:none"
                   class="absolute bottom-2 right-2 w-8 h-8 rounded-full
                          bg-black/60 backdrop-blur-sm cursor-pointer z-10
                          items-center justify-center
                          opacity-0 group-hover/dfoto:opacity-100
                          transition-opacity duration-200 hover:bg-red-600/80"
                   title="Cambiar foto">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175
                             C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15
                             A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169
                             a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055
                             l-.822-1.316a2.192 2.192 0 00-1.736-1.039 50.655 50.655 0
                             00-4.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z
                             M18.75 10.5h.008v.008h-.008V10.5z"/>
                </svg>
            </label>

            <!-- Sin foto: estado vacío con botón subir -->
            <div id="detail-no-foto"
                 class="flex flex-col items-center gap-2 py-8 text-gray-700">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5
                             l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5
                             1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25
                             6v12a1.5 1.5 0 001.5 1.5z"/>
                </svg>
                <span class="text-sm">Sin foto</span>
                <label for="detail-foto-input"
                       class="mt-0.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                              bg-gray-800 hover:bg-gray-700 cursor-pointer
                              text-gray-400 hover:text-white text-xs transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5
                                 m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    Subir foto
                </label>
            </div>

            <!-- Spinner de carga -->
            <div id="detail-foto-spinner"
                 class="absolute inset-0 bg-gray-950/70 items-center justify-center z-20"
                 style="display:none">
                <div class="w-8 h-8 border-2 border-gray-600 border-t-red-500
                            rounded-full animate-spin"></div>
            </div>

            <!-- Botón cerrar -->
            <button id="btn-close-detail"
                    class="absolute top-3 right-3 w-7 h-7 rounded-full bg-black/60
                           text-white flex items-center justify-center
                           hover:bg-red-600 transition-colors z-30">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Input oculto para subir foto desde el modal detalle -->
        <input type="file" id="detail-foto-input"
               accept="image/jpeg,image/png,image/webp" class="sr-only">

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

            <!-- Cobertura: tamaño + zona + tinta -->
            <div id="detail-cobertura-wrap" class="hidden flex flex-wrap items-center gap-1.5 mb-3">
                <span id="detail-tamano-chip"
                      class="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-400 font-mono font-semibold"></span>
                <span id="detail-zona-chip"
                      class="hidden text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-400"></span>
                <span class="text-xs px-2 py-0.5 rounded bg-gray-800 text-gray-400 flex items-center gap-1">
                    <span id="detail-tinta-dot" class="w-2 h-2 rounded-full flex-shrink-0"></span>
                    <span id="detail-tinta-label"></span>
                </span>
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

<!-- ══════════════════════════════════════════════════════════════════
     LIGHTBOX — Foto a pantalla completa
════════════════════════════════════════════════════════════════════ -->
<div id="lightbox"
     class="fixed inset-0 z-[100] bg-black/95 backdrop-blur-md
            items-center justify-center p-4 cursor-zoom-out"
     style="display:none"
     onclick="window.closeLightbox()">
    <button class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/10
                   hover:bg-white/20 flex items-center justify-center
                   text-white transition-colors z-10 cursor-default"
            onclick="window.closeLightbox()">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <img id="lightbox-img"
         src="" alt="Foto de tatuaje"
         class="max-w-full max-h-full object-contain rounded-lg shadow-2xl select-none"
         onclick="event.stopPropagation()">
</div>

<script>
// ── Tamaño pill selector ───────────────────────────────────────────────────────
(function () {
    var hints = {
        xs: 'XS — hasta 5 cm (símbolo, inicial)',
        s:  'S — 5 a 10 cm (tamaño puño)',
        m:  'M — 10 a 20 cm (sesión típica)',
        l:  'L — 20 a 40 cm (pieza grande / cuarto de manga)',
        xl: 'XL — cobertura total (manga, espalda completa, blackout)',
    };

    function activateTamano(val) {
        document.querySelectorAll('.tamano-pill').forEach(function (p) {
            var on = p.dataset.tamano === val;
            p.classList.toggle('bg-gray-700',    on);
            p.classList.toggle('border-gray-500', on);
            p.classList.toggle('text-white',      on);
            p.classList.toggle('text-gray-500',   !on);
        });
        var inp = document.getElementById('form-tamano');
        if (inp) inp.value = val;
        var hint = document.getElementById('tamano-hint');
        if (hint) hint.textContent = hints[val] ?? '';

        // Mostrar/ocultar zona y tinta según tamaño
        var showExtra = val === 'l' || val === 'xl';
        document.getElementById('zona-wrap').style.display  = showExtra ? '' : 'none';
        document.getElementById('tinta-wrap').style.display = showExtra ? '' : 'none';
    }

    document.querySelectorAll('.tamano-pill').forEach(function (p) {
        p.addEventListener('click', function () { activateTamano(p.dataset.tamano); });
    });
    activateTamano('m'); // default

    window._activateTamano = activateTamano;
})();

// ── Tipo de tinta selector ─────────────────────────────────────────────────────
(function () {
    function activateTinta(val) {
        document.querySelectorAll('.tinta-btn').forEach(function (b) {
            var on = b.dataset.tinta === val;
            b.classList.toggle('bg-gray-800',    on);
            b.classList.toggle('border-gray-400', on);
            b.classList.toggle('text-white',      on);
            b.classList.toggle('text-gray-500',   !on);
            b.classList.toggle('border-gray-700', !on);
        });
        var inp = document.getElementById('form-tinta');
        if (inp) inp.value = val;
    }

    document.querySelectorAll('.tinta-btn').forEach(function (b) {
        b.addEventListener('click', function () { activateTinta(b.dataset.tinta); });
    });
    activateTinta('negro'); // default

    window._activateTinta = activateTinta;
})();

// ── Form modal: botón cancelar secundario ──────────────────────────────────────
document.getElementById('btn-cancel-form-2')?.addEventListener('click', () => {
    document.getElementById('modal-form-overlay').classList.add('hidden');
    document.getElementById('modal-form-tatuaje').classList.add('hidden');
});

// ── Form modal: remover foto preview ──────────────────────────────────────────
document.getElementById('btn-remove-foto')?.addEventListener('click', () => {
    const input    = document.getElementById('form-foto');
    const preview  = document.getElementById('form-foto-preview');
    const dropzone = document.getElementById('form-foto-dropzone');
    const wrap     = document.getElementById('form-foto-preview-wrap');
    if (input)    input.value = '';
    if (preview)  { preview.src = ''; preview.classList.add('hidden'); }
    if (dropzone) dropzone.classList.remove('hidden');
    if (wrap)     wrap.classList.add('hidden');
});

// ── Form modal: preview al elegir foto ───────────────────────────────────────
document.getElementById('form-foto')?.addEventListener('change', function () {
    const wrap     = document.getElementById('form-foto-preview-wrap');
    const preview  = document.getElementById('form-foto-preview');
    const dropzone = document.getElementById('form-foto-dropzone');
    if (this.files?.[0]) {
        if (wrap)     wrap.classList.remove('hidden');
        if (preview)  { preview.src = URL.createObjectURL(this.files[0]); preview.classList.remove('hidden'); }
        if (dropzone) dropzone.classList.add('hidden');
    }
});

// ── Lightbox ──────────────────────────────────────────────────────────────────
(function () {
    function onKeydown(e) { if (e.key === 'Escape') close(); }

    function open(url) {
        var lb  = document.getElementById('lightbox');
        var img = document.getElementById('lightbox-img');
        if (!lb || !img || !url) return;
        img.src = url;
        lb.style.display = 'flex';
        document.addEventListener('keydown', onKeydown);
    }

    function close() {
        var lb = document.getElementById('lightbox');
        if (!lb) return;
        lb.style.display = '';
        document.removeEventListener('keydown', onKeydown);
    }

    window.openLightbox  = open;
    window.closeLightbox = close;

    // Click en foto del modal detalle → abre lightbox
    document.getElementById('detail-foto')?.addEventListener('click', function () {
        var src = this.getAttribute('src');
        if (src && !this.classList.contains('hidden')) open(src);
    });
})();

// ── Quick photo upload desde el modal detalle ─────────────────────────────────
(function () {
    var detailInput = document.getElementById('detail-foto-input');
    if (!detailInput) return;

    var CSRF = <?= json_encode($csrf) ?>;
    var BASE = <?= json_encode(BASE_URL) ?>;

    detailInput.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        this.value = ''; // permite re-seleccionar el mismo archivo

        // Leer el ID del tatuaje activo del botón Editar (lo setea openDetailModal)
        var tatId = parseInt(document.getElementById('btn-edit-detail')?.dataset?.id ?? '0');
        if (!tatId) return;

        var spinner   = document.getElementById('detail-foto-spinner');
        var fotoEl    = document.getElementById('detail-foto');
        var noFotoEl  = document.getElementById('detail-no-foto');
        var uploadBtn = document.getElementById('detail-upload-btn');

        if (spinner) spinner.style.display = 'flex';

        var fd = new FormData();
        fd.append('foto',  file);
        fd.append('_csrf', CSRF);

        fetch(BASE + '/api/tatuajes/' + tatId + '/foto', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (spinner) spinner.style.display = '';
                var bm = window.__bodymap?.markers;

                if (!d.success) {
                    bm ? bm.showToast(d.error || 'Error al subir la foto.', 'error')
                       : alert(d.error || 'Error.');
                    return;
                }

                var t = d.tatuaje;

                // Actualizar foto en el modal detalle
                if (fotoEl)   { fotoEl.src = t.foto_url; fotoEl.classList.remove('hidden'); }
                if (noFotoEl) noFotoEl.classList.add('hidden');
                if (uploadBtn) uploadBtn.style.display = 'flex';

                // Sincronizar tatuajesMap + sidebar en markers.js
                bm?.updateTatuajeData?.(t);

                // Actualizar imagen en la galería si el ítem ya existe
                var galItem = document.querySelector('.gallery-item[data-tat-id="' + t.id + '"]');
                if (galItem && t.foto_url) {
                    var img = galItem.querySelector('img');
                    if (img) img.src = t.foto_url;
                    galItem.dataset.fotoUrl = t.foto_url;
                    galItem.onclick = (function (url) {
                        return function () { window.openLightbox(url); };
                    })(t.foto_url);
                }

                bm ? bm.showToast('Foto guardada.', 'success')
                   : console.log('Foto guardada.');
            })
            .catch(function () {
                if (spinner) spinner.style.display = '';
                var bm = window.__bodymap?.markers;
                bm ? bm.showToast('Error de red.', 'error') : alert('Error de red.');
            });
    });
})();
</script>
