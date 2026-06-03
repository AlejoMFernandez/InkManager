<?php
use App\Core\Auth;

$csrf      = Auth::csrfToken();
$isEditing = $editing !== null;

// For edit mode, derive action URL from editing user's id
$action = $isEditing
    ? BASE_URL . '/staff/' . $editing['id'] . '/editar'
    : BASE_URL . '/staff/nuevo';

$roles = [
    'admin' => [
        'label' => __('staff.role_admin'),
        'desc'  => __('staff.role_admin_desc'),
        'color' => 'text-yellow-400',
        'ring'  => 'ring-yellow-500/30',
        'bg'    => 'bg-yellow-500/8 border-yellow-500/25',
    ],
    'staff' => [
        'label' => __('staff.role_staff'),
        'desc'  => __('staff.role_staff_desc'),
        'color' => 'text-blue-400',
        'ring'  => 'ring-blue-500/30',
        'bg'    => 'bg-blue-500/8 border-blue-500/25',
    ],
];

$selectedRole = $old['rol'] ?? ($editing['rol'] ?? 'staff');
?>

<!-- ── Breadcrumb ────────────────────────────────────────────────────────── -->
<div class="flex items-center gap-2 mb-6 text-xs text-gray-600">
    <a href="<?= BASE_URL ?>/staff" class="hover:text-gray-400 transition-colors"><?= __('staff.title') ?></a>
    <span>/</span>
    <span class="text-gray-400"><?= $isEditing ? __('staff.edit') : __('staff.new') ?></span>
</div>

<!-- ── Page header ──────────────────────────────────────────────────────── -->
<div class="mb-6">
    <p class="brand-tagline mb-1"><?= __('staff.tag') ?></p>
    <h2 class="font-display text-3xl text-white tracking-wider uppercase">
        <?= $isEditing ? __('staff.edit') : __('staff.new') ?>
    </h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    <!-- ── Main form card ────────────────────────────────────────────────── -->
    <div class="lg:col-span-2 bg-gray-900/60 border border-gray-800 rounded-2xl p-6 card-in">
        <form method="POST" action="<?= $action ?>" class="space-y-5">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <?php if (!$isEditing): ?>
            <!-- Name + Email (only for new users) -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                    <?= __('staff.form_name') ?> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nombre" required autocomplete="name"
                       value="<?= htmlspecialchars($old['nombre'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                              text-white text-sm focus:outline-none focus:ring-2
                              focus:ring-red-600/50 focus:border-red-600/50 transition-all">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                    <?= __('staff.form_email') ?> <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" required autocomplete="off"
                       value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                       class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                              text-white text-sm focus:outline-none focus:ring-2
                              focus:ring-red-600/50 focus:border-red-600/50 transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('staff.form_password') ?> <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" id="pwd_new" name="password"
                               required minlength="8" autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                      text-white text-sm focus:outline-none focus:ring-2
                                      focus:ring-red-600/50 focus:border-red-600/50 pr-10 transition-all">
                        <button type="button" onclick="togglePwd('pwd_new','eye1')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-300">
                            <svg id="eye1" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                         9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('staff.form_password_confirm') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="pwd_confirm" name="password_confirm"
                           required minlength="8" autocomplete="new-password"
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm focus:outline-none focus:ring-2
                                  focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>
            </div>
            <p class="text-xs text-gray-700">Mínimo 8 caracteres. El usuario recibirá las credenciales de acceso.</p>

            <!-- Divider -->
            <div class="relative py-1">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-800"></div></div>
                <div class="relative flex justify-center">
                    <span class="bg-gray-900/60 px-3 brand-tagline">Rol de acceso</span>
                </div>
            </div>
            <?php else: ?>
            <!-- Edit mode: show current user info (read only) -->
            <div class="flex items-center gap-4 p-4 bg-gray-950/50 border border-gray-800 rounded-xl">
                <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center
                            bg-gradient-to-br from-gray-700 to-gray-800 border border-gray-700
                            text-sm font-bold text-white select-none">
                    <?= strtoupper(mb_substr($editing['nombre'] ?? '?', 0, 1)) ?>
                </div>
                <div>
                    <p class="text-white text-sm font-medium"><?= htmlspecialchars($editing['nombre']) ?></p>
                    <p class="text-gray-500 text-xs"><?= htmlspecialchars($editing['email']) ?></p>
                </div>
            </div>
            <p class="brand-tagline">Cambiar rol de acceso</p>
            <?php endif; ?>

            <!-- Role selector -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($roles as $roleKey => $roleDef): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="rol" value="<?= $roleKey ?>"
                           <?= $selectedRole === $roleKey ? 'checked' : '' ?>
                           class="sr-only peer">
                    <div class="flex items-start gap-3 p-4 rounded-xl border transition-all
                                border-gray-700 hover:border-gray-600
                                peer-checked:<?= $roleDef['bg'] ?>
                                peer-checked:ring-2 peer-checked:<?= $roleDef['ring'] ?>
                                peer-checked:border-transparent">
                        <div class="w-4 h-4 rounded-full border-2 border-gray-600 flex-shrink-0 mt-0.5
                                    peer-checked:bg-current transition-all flex items-center justify-center
                                    <?= $selectedRole === $roleKey ? $roleDef['color'] . ' border-current' : '' ?>">
                            <?php if ($selectedRole === $roleKey): ?>
                            <div class="w-1.5 h-1.5 rounded-full bg-current"></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="text-sm font-medium <?= $roleDef['color'] ?>"><?= $roleDef['label'] ?></p>
                            <p class="text-xs text-gray-600 mt-0.5 leading-relaxed"><?= $roleDef['desc'] ?></p>
                        </div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700
                               hover:from-red-500 hover:to-red-600 text-white font-display
                               tracking-[0.15em] text-sm rounded-lg uppercase transition-all
                               shadow-lg shadow-red-900/30 btn-glow">
                    <?= $isEditing ? __('btn.save_changes') : __('staff.create_btn') ?>
                </button>
                <a href="<?= BASE_URL ?>/staff"
                   class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-300 transition-colors">
                    <?= __('btn.cancel') ?>
                </a>
            </div>
        </form>
    </div>

    <!-- ── Info sidebar ───────────────────────────────────────────────────── -->
    <div class="space-y-4 card-in" style="animation-delay:80ms">
        <div class="bg-gray-900/40 border border-gray-800/60 rounded-xl p-5">
            <p class="brand-tagline mb-4">// Roles explicados</p>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span class="text-xs font-semibold text-red-400 uppercase tracking-wider">Owner</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">Acceso total. Gestiona usuarios, configuración del estudio y plan.</p>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                        <span class="text-xs font-semibold text-yellow-400 uppercase tracking-wider">Admin</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">Puede gestionar clientes, turnos y la configuración general. No puede gestionar usuarios.</p>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-xs font-semibold text-blue-400 uppercase tracking-wider">Staff</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">Solo puede ver y gestionar clientes y turnos. Sin acceso a configuración.</p>
                </div>
            </div>
        </div>

        <?php if (!$isEditing): ?>
        <div class="bg-gray-900/40 border border-gray-800/60 rounded-xl p-4">
            <p class="brand-tagline mb-2">// Acceso</p>
            <p class="text-xs text-gray-600 leading-relaxed">
                El nuevo usuario podrá iniciar sesión de inmediato con el email y contraseña que definas.
                Recomendamos pedirles que cambien la contraseña desde su perfil.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    var inp = document.getElementById(inputId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

// Live password match indicator
(function () {
    var n = document.getElementById('pwd_new');
    var c = document.getElementById('pwd_confirm');
    if (!n || !c) return;
    function check() {
        if (!c.value.length) { c.style.borderColor = ''; return; }
        c.style.borderColor = (c.value === n.value && c.value.length >= 8) ? '#22c55e' : '#ef4444';
    }
    c.addEventListener('input', check);
    n.addEventListener('input', check);

    // Visual radio indicator (pure CSS handles most, but JS improves the dot)
    document.querySelectorAll('input[name="rol"]').forEach(function(r) {
        r.addEventListener('change', function() {
            document.querySelectorAll('input[name="rol"]').forEach(function(rb) {
                var dot = rb.closest('label').querySelector('.rounded-full div');
                if (dot) { dot.style.display = rb.checked ? '' : 'none'; }
            });
        });
    });
})();
</script>
