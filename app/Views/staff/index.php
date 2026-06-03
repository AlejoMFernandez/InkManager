<?php
use App\Core\Auth;
use App\Models\Studio;

$csrf = Auth::csrfToken();

// Role badge config
$roleBadge = [
    'owner' => ['label' => 'Owner', 'bg' => 'bg-red-600/15',    'border' => 'border-red-600/30',    'text' => 'text-red-400'],
    'admin' => ['label' => 'Admin', 'bg' => 'bg-yellow-500/10', 'border' => 'border-yellow-500/25', 'text' => 'text-yellow-400'],
    'staff' => ['label' => 'Staff', 'bg' => 'bg-blue-500/10',   'border' => 'border-blue-500/25',   'text' => 'text-blue-400'],
];
?>

<!-- ── Page header ──────────────────────────────────────────────────────── -->
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="brand-tagline mb-1"><?= __('staff.tag') ?></p>
        <h2 class="font-display text-3xl text-white tracking-wider uppercase">
            <?= __('staff.title') ?>
        </h2>
    </div>

    <!-- Plan usage + CTA -->
    <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5
                    bg-gray-900/60 border border-gray-800 rounded-lg text-xs text-gray-500">
            <span class="font-mono"><?= count($users) ?> / <?= $maxUsers >= PHP_INT_MAX ? '∞' : $maxUsers ?></span>
            <span class="brand-tagline"><?= __('staff.plan_usage') ?></span>
            <?php if ($plan !== 'free'): ?>
            <span class="text-xs bg-red-600/20 border border-red-600/40 text-red-400 px-1.5 py-0.5 rounded font-mono ml-1">Pro</span>
            <?php endif; ?>
        </div>

        <?php if ($limitReached && $plan === 'free'): ?>
        <a href="<?= BASE_URL ?>/configuracion?tab=studio"
           class="flex items-center gap-2 px-4 py-2 bg-gray-800 border border-gray-700
                  hover:border-red-600/40 text-gray-400 hover:text-red-400 text-xs
                  font-display tracking-wider uppercase rounded-lg transition-all"
           title="<?= htmlspecialchars(__('staff.limit_reached')) ?>">
            Upgrade a Pro
        </a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/staff/nuevo"
           class="flex items-center gap-2 px-4 py-2
                  bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                  text-white font-display tracking-[0.12em] text-sm uppercase
                  rounded-lg transition-all shadow-lg shadow-red-900/30 btn-glow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <?= __('staff.new') ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Plan limit warning banner ────────────────────────────────────────── -->
<?php if ($limitReached && $plan === 'free'): ?>
<div class="flex items-start gap-3 px-4 py-3 mb-5 rounded-xl
            bg-yellow-500/8 border border-yellow-500/20 text-yellow-400 text-sm">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732
                 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
    </svg>
    <span><?= __('staff.limit_reached') ?></span>
</div>
<?php endif; ?>

<!-- ── User table card ───────────────────────────────────────────────────── -->
<div class="bg-gray-900/60 border border-gray-800 rounded-2xl overflow-hidden card-in">

    <?php if (empty($users)): ?>
    <!-- Empty state (shouldn't happen — owner always exists) -->
    <div class="py-16 text-center">
        <p class="text-gray-600 text-sm"><?= __('staff.empty') ?></p>
    </div>

    <?php else: ?>
    <!-- Desktop table -->
    <table class="w-full hidden sm:table">
        <thead>
            <tr class="border-b border-gray-800/80">
                <th class="text-left px-6 py-3.5 text-xs font-medium text-gray-600 uppercase tracking-wider">
                    <?= __('staff.col_name') ?>
                </th>
                <th class="text-left px-4 py-3.5 text-xs font-medium text-gray-600 uppercase tracking-wider hidden md:table-cell">
                    <?= __('staff.col_email') ?>
                </th>
                <th class="text-left px-4 py-3.5 text-xs font-medium text-gray-600 uppercase tracking-wider">
                    <?= __('staff.col_role') ?>
                </th>
                <th class="text-left px-4 py-3.5 text-xs font-medium text-gray-600 uppercase tracking-wider hidden lg:table-cell">
                    <?= __('staff.col_joined') ?>
                </th>
                <th class="px-4 py-3.5 w-24"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/60">
            <?php foreach ($users as $u):
                $isMe    = (int) $u['id'] === $currentId;
                $isOwner = $u['rol'] === 'owner';
                $badge   = $roleBadge[$u['rol']] ?? $roleBadge['staff'];
                $initial = strtoupper(mb_substr($u['nombre'] ?? '?', 0, 1));
            ?>
            <tr class="table-row-hover transition-colors hover:bg-gray-800/20">
                <!-- Avatar + name -->
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center
                                    bg-gradient-to-br from-gray-700 to-gray-800
                                    border border-gray-700 text-sm font-bold text-white select-none">
                            <?= htmlspecialchars($initial) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-white text-sm font-medium truncate">
                                <?= htmlspecialchars($u['nombre']) ?>
                                <?php if ($isMe): ?>
                                <span class="ml-1 text-xs text-gray-600 font-normal">(vos)</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-gray-600 text-xs truncate md:hidden">
                                <?= htmlspecialchars($u['email']) ?>
                            </p>
                        </div>
                    </div>
                </td>
                <!-- Email -->
                <td class="px-4 py-4 hidden md:table-cell">
                    <span class="text-gray-400 text-sm"><?= htmlspecialchars($u['email']) ?></span>
                </td>
                <!-- Role badge -->
                <td class="px-4 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono
                                 <?= $badge['bg'] ?> <?= $badge['border'] ?> <?= $badge['text'] ?> border">
                        <?= $badge['label'] ?>
                    </span>
                </td>
                <!-- Date -->
                <td class="px-4 py-4 hidden lg:table-cell">
                    <span class="text-gray-600 text-xs font-mono">
                        <?= isset($u['created_at'])
                            ? date('d/m/Y', strtotime((string) $u['created_at']))
                            : '—' ?>
                    </span>
                </td>
                <!-- Actions -->
                <td class="px-4 py-4">
                    <?php if (!$isOwner): ?>
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="<?= BASE_URL ?>/staff/<?= $u['id'] ?>/editar"
                           class="p-1.5 rounded-lg text-gray-600 hover:text-gray-300
                                  hover:bg-gray-700/50 transition-colors"
                           title="<?= __('btn.edit') ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                                         m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <?php if (!$isMe): ?>
                        <form method="POST" action="<?= BASE_URL ?>/staff/<?= $u['id'] ?>/borrar"
                              onsubmit="return confirm('<?= addslashes(__('staff.delete_confirm', ['name' => $u['nombre']])) ?>')">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit"
                                    class="p-1.5 rounded-lg text-gray-700 hover:text-red-400
                                           hover:bg-red-500/10 transition-colors"
                                    title="<?= __('btn.delete') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0
                                             01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0
                                             00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Mobile card list -->
    <div class="sm:hidden divide-y divide-gray-800/60">
        <?php foreach ($users as $u):
            $isMe    = (int) $u['id'] === $currentId;
            $isOwner = $u['rol'] === 'owner';
            $badge   = $roleBadge[$u['rol']] ?? $roleBadge['staff'];
            $initial = strtoupper(mb_substr($u['nombre'] ?? '?', 0, 1));
        ?>
        <div class="flex items-center gap-3 px-5 py-4">
            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center
                        bg-gradient-to-br from-gray-700 to-gray-800 border border-gray-700
                        text-sm font-bold text-white select-none">
                <?= htmlspecialchars($initial) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-medium truncate">
                    <?= htmlspecialchars($u['nombre']) ?>
                    <?php if ($isMe): ?>
                    <span class="text-gray-600 text-xs font-normal ml-1">(vos)</span>
                    <?php endif; ?>
                </p>
                <p class="text-gray-500 text-xs truncate"><?= htmlspecialchars($u['email']) ?></p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-mono
                         <?= $badge['bg'] ?> <?= $badge['border'] ?> <?= $badge['text'] ?> border flex-shrink-0">
                <?= $badge['label'] ?>
            </span>
            <?php if (!$isOwner && !$isMe): ?>
            <a href="<?= BASE_URL ?>/staff/<?= $u['id'] ?>/editar"
               class="p-1.5 text-gray-600 hover:text-gray-300 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5
                             m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── Info callout ──────────────────────────────────────────────────────── -->
<div class="mt-4 flex items-start gap-3 px-4 py-3 rounded-xl
            bg-gray-900/40 border border-gray-800/50 text-xs text-gray-600">
    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>
        <strong class="text-gray-500">Admin</strong> — puede gestionar clientes, turnos y configuración. &nbsp;
        <strong class="text-gray-500">Staff</strong> — acceso solo a clientes y turnos, sin settings.
    </span>
</div>
