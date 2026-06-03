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

// Género por defecto 'masculino' si no hay valor guardado
$generoActual = $val('genero') ?: 'masculino';
?>

<div class="max-w-2xl">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= BASE_URL ?>/clientes" class="hover:text-white transition-colors"><?= __('nav.clients') ?></a>
        <span>/</span>
        <span class="text-gray-300"><?= $isEdit ? htmlspecialchars($cliente['nombre']) : __('client.breadcrumb_new') ?></span>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">

        <h2 class="text-lg font-semibold text-white mb-6">
            <?= $isEdit ? __('client.edit') : __('client.new') ?>
        </h2>

        <form method="POST" action="<?= $action ?>" novalidate
              <?= !$isEdit ? 'enctype="multipart/form-data"' : '' ?>>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- ── Avatar picker ────────────────────────────────────── -->
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-5 pb-5 mb-1 border-b border-gray-800/60">

                        <!-- Clickable thumbnail -->
                        <div id="form-avatar-trigger"
                             class="relative flex-shrink-0 group/avatar cursor-pointer select-none"
                             title="<?= htmlspecialchars($isEdit ? __('form.change_photo_title') : __('form.choose_photo_title')) ?>">

                            <?php if ($isEdit && !empty($cliente['foto_perfil'])): ?>
                            <img id="form-avatar-preview"
                                 src="<?= PUBLIC_URL ?>/assets/uploads/avatars/<?= htmlspecialchars($cliente['foto_perfil']) ?>"
                                 alt=""
                                 class="w-24 h-24 rounded-2xl object-cover border border-gray-700">
                            <?php else: ?>
                            <div id="form-avatar-placeholder"
                                 class="w-24 h-24 rounded-2xl
                                        bg-gradient-to-br from-gray-800 to-gray-900
                                        border border-dashed border-gray-700
                                        flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0
                                             01 12 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <?php endif; ?>

                            <!-- Camera overlay on hover -->
                            <div class="absolute inset-0 rounded-2xl bg-black/65
                                        flex flex-col items-center justify-center gap-1
                                        opacity-0 group-hover/avatar:opacity-100
                                        transition-opacity duration-150 pointer-events-none">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
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
                                <span class="text-white text-[10px] font-medium">
                                    <?= ($isEdit && !empty($cliente['foto_perfil'])) ? __('form.change') : __('form.choose') ?>
                                </span>
                            </div>

                            <?php if ($isEdit): ?>
                            <!-- Spinner — solo en edición (AJAX) -->
                            <div id="form-avatar-spinner"
                                 class="absolute inset-0 rounded-2xl bg-gray-950/80
                                        items-center justify-center hidden">
                                <div class="w-5 h-5 border-2 border-gray-600 border-t-red-500
                                            rounded-full animate-spin"></div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Description text -->
                        <div>
                            <p class="text-sm font-semibold text-white mb-1"><?= __('form.avatar_title') ?></p>
                            <p class="text-xs text-gray-600 leading-relaxed mb-2">
                                <?= __('form.avatar_hint') ?><?php if ($isEdit): ?><br>
                                <span class="text-gray-700"><?= __('form.avatar_instant') ?></span>
                                <?php else: ?><br>
                                <span class="text-gray-700"><?= __('form.avatar_with_form') ?></span>
                                <?php endif; ?>
                            </p>
                            <button type="button"
                                    onclick="document.getElementById('form-avatar-input').click()"
                                    class="text-xs text-red-400 hover:text-red-300 transition-colors">
                                <?= ($isEdit && !empty($cliente['foto_perfil'])) ? __('form.change_photo') : __('form.choose_photo') ?>
                            </button>
                        </div>
                    </div>

                    <!-- File input: con name sólo en creación (va con el form),
                         en edición sube por AJAX y no debe enviarse con el form -->
                    <input type="file" id="form-avatar-input"
                           <?= !$isEdit ? 'name="avatar"' : '' ?>
                           accept="image/jpeg,image/png,image/webp" class="sr-only">
                </div>
                <!-- /Avatar picker ──────────────────────────────────────── -->

                <!-- Nombre -->
                <div class="sm:col-span-2">
                    <label for="nombre" class="block text-xs font-medium text-gray-400 mb-1.5">
                        <?= __('form.name_label') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?= $val('nombre') ?>"
                           required maxlength="120"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="<?= htmlspecialchars(__('form.name_ph')) ?>">
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
                        <?= __('client.col_phone') ?>
                    </label>
                    <input type="tel" id="telefono" name="telefono"
                           value="<?= $val('telefono') ?>"
                           maxlength="30"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                           placeholder="<?= htmlspecialchars(__('form.phone_ph')) ?>">
                </div>

                <!-- Primera visita -->
                <div>
                    <label for="primera_visita" class="block text-xs font-medium text-gray-400 mb-1.5">
                        <?= __('form.first_visit_label') ?>
                    </label>
                    <input type="date" id="primera_visita" name="primera_visita"
                           value="<?= $val('primera_visita') ?>"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                  [color-scheme:dark]">
                </div>

                <!-- Fecha de nacimiento -->
                <div>
                    <label for="fecha_nacimiento" class="block text-xs font-medium text-gray-400 mb-1.5">
                        Fecha de nacimiento
                        <span class="text-gray-600 font-normal ml-1">— para cumpleaños</span>
                    </label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                           value="<?= $val('fecha_nacimiento') ?>"
                           class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                  text-white text-sm
                                  focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50
                                  [color-scheme:dark]">
                </div>

                <!-- Modelo 3D / Género -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-2">
                        <?= __('form.bodymap_label') ?>
                        <span class="text-gray-600 font-normal ml-1"><?= __('form.bodymap_hint') ?></span>
                    </label>
                    <div class="flex flex-wrap gap-2" id="genero-selector">
                        <?php foreach ([
                            ['masculino', '♂', __('form.gender_male')],
                            ['femenino',  '♀', __('form.gender_female')],
                            ['otro',      '⚥', __('form.gender_other')],
                        ] as [$optVal, $optIcon, $optLabel]): ?>
                        <label data-genero-label
                               class="flex items-center gap-2 px-4 py-2.5 rounded-lg border cursor-pointer
                                      transition-all select-none
                                      <?= $generoActual === $optVal
                                          ? 'border-red-500/50 bg-red-500/10 text-white'
                                          : 'border-gray-700 hover:border-gray-600 text-gray-400' ?>">
                            <input type="radio" name="genero" value="<?= $optVal ?>"
                                   <?= $generoActual === $optVal ? 'checked' : '' ?>
                                   class="sr-only">
                            <span class="text-base leading-none"><?= $optIcon ?></span>
                            <span class="text-sm font-medium"><?= $optLabel ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-600"><?= __('form.gender_other_hint') ?></p>
                </div>

                <!-- Notas -->
                <div class="sm:col-span-2">
                    <label for="notas" class="block text-xs font-medium text-gray-400 mb-1.5">
                        <?= __('form.notes_label') ?>
                    </label>
                    <textarea id="notas" name="notas" rows="3"
                              class="w-full px-3.5 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                     text-white text-sm placeholder-gray-600 resize-y
                                     focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50"
                              placeholder="<?= htmlspecialchars(__('form.notes_ph')) ?>"><?= $val('notas') ?></textarea>
                </div>

            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-800">
                <a href="<?= $isEdit ? BASE_URL . '/clientes/' . $cliente['id'] : BASE_URL . '/clientes' ?>"
                   class="text-sm text-gray-400 hover:text-white transition-colors">
                    <?= __('btn.cancel') ?>
                </a>
                <button type="submit"
                        class="btn-glow px-5 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-95 transition-all
                               focus:outline-none focus:ring-2 focus:ring-red-500/50">
                    <?= $isEdit ? __('btn.save_changes') : __('form.create_client_btn') ?>
                </button>
            </div>

        </form>
    </div>

    <?php if ($isEdit): ?>
    <!-- Danger zone -->
    <div class="mt-4 bg-gray-900 border border-red-900/30 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-red-400 mb-2"><?= __('common.danger_zone') ?></h3>
        <p class="text-gray-500 text-xs mb-3">
            <?= __('client.delete_warning') ?>
        </p>
        <form method="POST"
              action="<?= BASE_URL ?>/clientes/<?= $cliente['id'] ?>/borrar"
              onsubmit="return confirm('<?= htmlspecialchars(__('client.delete_confirm', ['name' => addslashes($cliente['nombre'])])) ?>')">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit"
                    class="px-4 py-2 border border-red-800 text-red-400 hover:bg-red-600/10
                           text-sm rounded-lg transition-colors">
                <?= __('client.delete_btn') ?>
            </button>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
// ── Avatar picker ──────────────────────────────────────────────────────────────
(function () {
    var trigger = document.getElementById('form-avatar-trigger');
    var input   = document.getElementById('form-avatar-input');
    if (!trigger || !input) return;

    var isEdit = <?= json_encode($isEdit) ?>;
    <?php if ($isEdit): ?>
    var spinner  = document.getElementById('form-avatar-spinner');
    var CSRF     = <?= json_encode($csrf) ?>;
    var ENDPOINT = <?= json_encode(BASE_URL . '/api/clientes/' . (int)($cliente['id'] ?? 0) . '/avatar') ?>;
    var hadPhoto = <?= json_encode(!empty($cliente['foto_perfil'])) ?>;
    var tempImg  = false;
    <?php endif; ?>

    trigger.addEventListener('click', function () { input.click(); });

    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;

        var blobUrl = URL.createObjectURL(file);

        if (isEdit) {
            <?php if ($isEdit): ?>
            // Edit: snapshot for revert, then upload via AJAX immediately
            var prevEl  = document.getElementById('form-avatar-preview');
            var prevSrc = prevEl ? prevEl.src : null;
            input.value = ''; // allow re-selection
            applyPreview(blobUrl, prevEl);
            spinnerShow(true);

            var fd = new FormData();
            fd.append('avatar', file);
            fd.append('_csrf', CSRF);

            fetch(ENDPOINT, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    spinnerShow(false);
                    URL.revokeObjectURL(blobUrl);
                    if (d.ok) {
                        var img = document.getElementById('form-avatar-preview');
                        if (img) img.src = d.url;
                        hadPhoto = true; tempImg = false;
                        window.showToast && window.showToast('Foto de perfil actualizada.', 'success');
                    } else {
                        revertPreview(prevSrc);
                        window.showToast && window.showToast(d.error || 'Error al subir la foto.', 'error');
                    }
                })
                .catch(function () {
                    spinnerShow(false);
                    URL.revokeObjectURL(blobUrl);
                    revertPreview(prevSrc);
                    window.showToast && window.showToast('Error de red al subir la foto.', 'error');
                });
            <?php endif; ?>
        } else {
            // Create: just show local preview — file rides with form on submit
            applyPreview(blobUrl, document.getElementById('form-avatar-preview'));
        }
    });

    function applyPreview(url, prevEl) {
        if (prevEl) {
            prevEl.src = url;
        } else {
            var img = document.createElement('img');
            img.id        = 'form-avatar-preview';
            img.className = 'w-24 h-24 rounded-2xl object-cover border border-gray-700';
            img.alt       = '';
            img.src       = url;
            trigger.insertBefore(img, trigger.firstChild);
            var plh = document.getElementById('form-avatar-placeholder');
            if (plh) plh.style.display = 'none';
            <?php if ($isEdit): ?>tempImg = true;<?php endif; ?>
        }
    }

    <?php if ($isEdit): ?>
    function revertPreview(prevSrc) {
        if (tempImg && !hadPhoto) {
            var img = document.getElementById('form-avatar-preview');
            if (img) img.remove();
            tempImg = false;
            var plh = document.getElementById('form-avatar-placeholder');
            if (plh) plh.style.display = '';
        } else if (prevSrc) {
            var img = document.getElementById('form-avatar-preview');
            if (img) img.src = prevSrc;
        }
    }

    function spinnerShow(show) {
        if (!spinner) return;
        if (show) { spinner.classList.remove('hidden'); spinner.style.display = 'flex'; }
        else       { spinner.classList.add('hidden');   spinner.style.display = ''; }
    }
    <?php endif; ?>
})();
</script>

<script>
(function () {
    // Normalizar arroba en Instagram al submit
    document.querySelector('form').addEventListener('submit', function() {
        const ig = document.getElementById('instagram');
        const v  = ig.value.trim();
        if (v && !v.startsWith('@')) ig.value = '@' + v;
    });

    // Resaltar la opción de género seleccionada
    function syncGeneroUI() {
        document.querySelectorAll('[data-genero-label]').forEach(label => {
            const radio  = label.querySelector('input[type=radio]');
            const active = radio?.checked ?? false;
            label.classList.toggle('border-red-500/50', active);
            label.classList.toggle('bg-red-500/10',     active);
            label.classList.toggle('text-white',         active);
            label.classList.toggle('border-gray-700',   !active);
            label.classList.toggle('text-gray-400',     !active);
        });
    }

    document.querySelectorAll('[name="genero"]').forEach(r =>
        r.addEventListener('change', syncGeneroUI)
    );
})();
</script>
