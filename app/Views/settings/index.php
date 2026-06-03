<?php
use App\Core\Auth;
use App\Models\Studio;

$csrf = Auth::csrfToken();

$planKey = $studio['plan'] ?? 'free';
$planDef = Studio::PLANS[$planKey] ?? Studio::PLANS['free'];
$maxCli  = $planDef['max_clientes'];
$pct     = ($maxCli < PHP_INT_MAX && $maxCli > 0)
    ? min(100, (int) round($clientCount / $maxCli * 100))
    : 0;
$pctColor = $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500');

$tabs = [
    'studio'   => __('settings.tab_studio'),
    'perfil'   => __('settings.tab_profile'),
    'password' => __('settings.tab_password'),
];
?>

<!-- ── Page header ──────────────────────────────────────────────────────── -->
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="brand-tagline mb-1"><?= __('settings.tag') ?></p>
        <h2 class="font-display text-3xl text-white tracking-wider uppercase">
            <?= __('settings.title') ?>
        </h2>
    </div>
</div>

<!-- ── Main grid ────────────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    <!-- ══ Left card — Tabs (2/3) ══════════════════════════════════════════ -->
    <div class="lg:col-span-2 bg-gray-900/60 border border-gray-800 rounded-2xl overflow-hidden card-in">

        <!-- Tab bar -->
        <div class="flex border-b border-gray-800">
            <?php foreach ($tabs as $key => $label): ?>
            <a href="?tab=<?= $key ?>"
               class="px-5 py-3.5 text-sm font-medium transition-colors whitespace-nowrap
                      <?= $activeTab === $key
                          ? 'text-white border-b-2 border-red-500 -mb-px bg-gray-800/40'
                          : 'text-gray-500 hover:text-gray-300' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Tab content -->
        <div class="p-6">

            <?php if ($activeTab === 'studio'): ?>
            <!-- ── Tab: Studio info ─────────────────────────────────────── -->
            <form method="POST" action="<?= BASE_URL ?>/configuracion/estudio" class="space-y-4">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.studio_name') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" maxlength="100" required
                           value="<?= htmlspecialchars($studio['nombre'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm focus:outline-none focus:ring-2
                                  focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            <?= __('settings.studio_phone') ?>
                        </label>
                        <input type="tel" name="telefono" maxlength="20"
                               value="<?= htmlspecialchars($studio['telefono'] ?? '') ?>"
                               placeholder="+54 11 1234-5678"
                               class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600 focus:outline-none
                                      focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            <?= __('settings.studio_instagram') ?>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm select-none">@</span>
                            <input type="text" name="instagram" maxlength="100"
                                   value="<?= htmlspecialchars($studio['instagram'] ?? '') ?>"
                                   placeholder="inkstudio"
                                   class="w-full pl-7 pr-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                          text-white text-sm placeholder-gray-600 focus:outline-none
                                          focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.studio_website') ?>
                    </label>
                    <input type="url" name="website" maxlength="255"
                           value="<?= htmlspecialchars($studio['website'] ?? '') ?>"
                           placeholder="https://miestudio.com"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600 focus:outline-none
                                  focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.studio_address') ?>
                    </label>
                    <input type="text" name="direccion" maxlength="255"
                           value="<?= htmlspecialchars($studio['direccion'] ?? '') ?>"
                           placeholder="Av. Corrientes 1234, Buenos Aires"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600 focus:outline-none
                                  focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700
                                   hover:from-red-500 hover:to-red-600 text-white font-display
                                   tracking-[0.15em] text-sm rounded-lg uppercase transition-all
                                   shadow-lg shadow-red-900/30 hover:shadow-red-800/50 btn-glow">
                        <?= __('settings.save_studio') ?>
                    </button>
                </div>
            </form>

            <?php elseif ($activeTab === 'perfil'): ?>
            <!-- ── Tab: Profile ─────────────────────────────────────────── -->
            <form method="POST" action="<?= BASE_URL ?>/configuracion/perfil" class="space-y-4">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.your_name') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" required autocomplete="name"
                           value="<?= htmlspecialchars($user['nombre'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm focus:outline-none focus:ring-2
                                  focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.your_email') ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" required autocomplete="email"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm focus:outline-none focus:ring-2
                                  focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.your_role') ?>
                    </label>
                    <div class="px-4 py-2.5 bg-gray-950/50 border border-gray-800 rounded-lg">
                        <span class="text-gray-400 text-sm capitalize">
                            <?= htmlspecialchars($user['rol'] ?? 'owner') ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-700 mt-1.5">El rol no puede cambiarse desde aquí.</p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700
                                   hover:from-red-500 hover:to-red-600 text-white font-display
                                   tracking-[0.15em] text-sm rounded-lg uppercase transition-all
                                   shadow-lg shadow-red-900/30 hover:shadow-red-800/50 btn-glow">
                        <?= __('settings.save_profile') ?>
                    </button>
                </div>
            </form>

            <?php else: // password ?>
            <!-- ── Tab: Password ────────────────────────────────────────── -->
            <form method="POST" action="<?= BASE_URL ?>/configuracion/password" class="space-y-4">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                        <?= __('settings.current_password') ?>
                    </label>
                    <input type="password" name="password_actual" required
                           autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                  text-white text-sm placeholder-gray-600 focus:outline-none
                                  focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            <?= __('settings.new_password') ?>
                        </label>
                        <input type="password" id="pwd_new" name="password_nuevo"
                               required minlength="8" autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600 focus:outline-none
                                      focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">
                            <?= __('settings.confirm_password') ?>
                        </label>
                        <input type="password" id="pwd_confirm" name="password_confirm"
                               required minlength="8" autocomplete="new-password"
                               placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-gray-950 border border-gray-700 rounded-lg
                                      text-white text-sm placeholder-gray-600 focus:outline-none
                                      focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50 transition-all">
                    </div>
                </div>

                <p class="text-xs text-gray-700">Mínimo 8 caracteres.</p>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-red-600 to-red-700
                                   hover:from-red-500 hover:to-red-600 text-white font-display
                                   tracking-[0.15em] text-sm rounded-lg uppercase transition-all
                                   shadow-lg shadow-red-900/30 hover:shadow-red-800/50 btn-glow">
                        <?= __('settings.save_password') ?>
                    </button>
                </div>
            </form>

            <script>
            (function () {
                var nc = document.getElementById('pwd_confirm');
                var np = document.getElementById('pwd_new');
                if (!nc || !np) return;
                function check() {
                    if (!nc.value.length) { nc.style.borderColor = ''; return; }
                    var ok = nc.value === np.value && nc.value.length >= 8;
                    nc.style.borderColor = ok ? '#22c55e' : '#ef4444';
                }
                nc.addEventListener('input', check);
                np.addEventListener('input', check);
            })();
            </script>
            <?php endif; ?>

        </div><!-- /tab content -->
    </div><!-- /left card -->

    <!-- ══ Right sidebar (1/3) ══════════════════════════════════════════════ -->
    <div class="space-y-4 card-in" style="animation-delay:80ms">

        <!-- Plan card -->
        <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-5">
            <p class="brand-tagline mb-3"><?= __('settings.plan_card_title') ?></p>

            <div class="flex items-center gap-2.5 mb-4">
                <span class="font-display text-2xl text-white tracking-wider uppercase">
                    <?= htmlspecialchars($planKey) ?>
                </span>
                <?php if ($planKey === 'free'): ?>
                <span class="text-xs bg-gray-700/80 text-gray-400 px-2 py-0.5 rounded font-mono border border-gray-700">Free</span>
                <?php else: ?>
                <span class="text-xs bg-red-600/20 border border-red-600/40 text-red-400 px-2 py-0.5 rounded font-mono">Pro</span>
                <?php endif; ?>
            </div>

            <!-- Client usage bar -->
            <div class="mb-1">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                    <span><?= __('settings.plan_clients') ?></span>
                    <span class="font-mono">
                        <?= $clientCount ?>
                        <?= $maxCli < PHP_INT_MAX ? ' / ' . $maxCli : '' ?>
                    </span>
                </div>
                <?php if ($maxCli < PHP_INT_MAX): ?>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all <?= $pctColor ?>"
                         style="width:<?= $pct ?>%"></div>
                </div>
                <p class="text-xs text-gray-700 mt-1.5"><?= $pct ?>% utilizado</p>
                <?php else: ?>
                <p class="text-xs text-gray-600 mt-1">Sin límite de clientes</p>
                <?php endif; ?>
            </div>

            <?php if ($planKey === 'free'): ?>
            <div class="mt-4 pt-4 border-t border-gray-800/80">
                <p class="text-xs text-gray-500 mb-3"><?= __('settings.plan_upgrade_desc') ?></p>
                <a href="#"
                   class="flex items-center justify-center gap-2 w-full py-2.5 px-4
                          bg-gradient-to-r from-red-600/15 to-red-700/15
                          hover:from-red-600/25 hover:to-red-700/25
                          border border-red-600/35 hover:border-red-500/60
                          text-red-400 hover:text-red-300 text-xs font-display
                          tracking-[0.12em] uppercase rounded-lg transition-all">
                    <?= __('settings.plan_upgrade_cta') ?> →
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Studio meta card -->
        <div class="bg-gray-900/40 border border-gray-800/60 rounded-xl p-4">
            <p class="brand-tagline mb-3"><?= __('settings.studio_info') ?></p>
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600">Studio ID</span>
                    <span class="text-gray-400 font-mono">#<?= htmlspecialchars((string) ($studio['id'] ?? '—')) ?></span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600">Slug</span>
                    <span class="text-gray-400 font-mono truncate ml-2 max-w-[120px]">
                        <?= htmlspecialchars($studio['slug'] ?? '—') ?>
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-600"><?= __('settings.since') ?></span>
                    <span class="text-gray-400 font-mono">
                        <?= isset($studio['created_at'])
                            ? date('d/m/Y', strtotime((string) $studio['created_at']))
                            : '—' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /grid -->
