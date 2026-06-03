<?php $pageTitle = 'Clientes'; ?>

<!-- Header row -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 page-enter">
    <div>
        <p class="brand-tagline mb-1"><?= __('client.crm_tag') ?></p>
        <h2 class="section-heading"><?= __('nav.clients') ?> <span class="accent">/ <?= number_format($total) ?></span></h2>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/export/clientes"
           title="Descargar CSV"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm
                  bg-gray-800 border border-gray-700 hover:border-gray-600
                  text-gray-400 hover:text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 12l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            CSV
        </a>
        <a href="<?= BASE_URL ?>/clientes/nuevo"
           class="btn-glow inline-flex items-center gap-2 px-4 py-2
                  bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                  text-white text-sm font-semibold rounded-lg transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <?= __('client.new') ?>
        </a>
    </div>
</div>

<!-- Search + filter row -->
<form method="GET" action="<?= BASE_URL ?>/clientes" class="mb-4">
    <div class="flex flex-wrap gap-2 items-center">
        <!-- Text search -->
        <div class="relative flex-1 min-w-[200px] max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input type="search" name="q" value="<?= htmlspecialchars($term) ?>"
                   placeholder="<?= htmlspecialchars(__('client.search_ph')) ?>"
                   class="w-full pl-9 pr-4 py-2 bg-gray-900 border border-gray-700 rounded-lg
                          text-sm text-white placeholder-gray-600
                          focus:outline-none focus:ring-2 focus:ring-red-600/50 focus:border-red-600/50">
        </div>

        <!-- Tag filter -->
        <?php if (!empty($etiquetas)): ?>
        <div class="flex items-center gap-1.5 flex-wrap">
            <?php if ($etiquetaId > 0): ?>
            <a href="<?= BASE_URL ?>/clientes<?= $term ? '?q=' . urlencode($term) : '' ?>"
               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg
                      bg-gray-800 text-gray-400 hover:text-white text-xs border border-gray-700
                      transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
                <?= __('tag.filter') ?>
            </a>
            <?php endif; ?>
            <?php foreach ($etiquetas as $et): ?>
            <a href="?<?= http_build_query(array_filter(['q' => $term, 'etiqueta' => $et['id'] === $etiquetaId ? 0 : $et['id']])) ?>"
               class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border
                      transition-all hover:opacity-80 <?= $et['id'] === $etiquetaId ? 'ring-2 ring-white/20' : '' ?>"
               style="color:<?= htmlspecialchars($et['color']) ?>;
                      background:<?= htmlspecialchars($et['color']) ?>1a;
                      border-color:<?= htmlspecialchars($et['color']) ?>40;">
                <?= htmlspecialchars($et['nombre']) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</form>

<!-- Table -->
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="text-left px-4 py-3 text-gray-500 font-medium"><?= __('client.col_name') ?></th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium hidden sm:table-cell">Instagram</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium hidden md:table-cell"><?= __('client.col_phone') ?></th>
                    <th class="text-left px-4 py-3 text-gray-500 font-medium hidden lg:table-cell"><?= __('client.col_first_visit') ?></th>
                    <th class="text-center px-4 py-3 text-gray-500 font-medium"><?= __('client.col_tattoos') ?></th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
            <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <svg class="w-10 h-10 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor"
                             stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0
                                     0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        <p class="text-gray-600">
                            <?= $term !== '' ? htmlspecialchars(__('client.no_results_pre')) . ' "' . htmlspecialchars($term) . '"' : __('client.empty') ?>
                        </p>
                        <?php if ($term === ''): ?>
                        <a href="<?= BASE_URL ?>/clientes/nuevo"
                           class="mt-3 inline-block text-sm text-red-400 hover:text-red-300">
                            <?= __('client.add_first') ?>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($clientes as $c): ?>
                <tr class="table-row-hover hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <?php if (!empty($c['foto_perfil'])): ?>
                            <img src="<?= PUBLIC_URL ?>/assets/uploads/avatars/<?= htmlspecialchars($c['foto_perfil']) ?>"
                                 alt=""
                                 class="w-7 h-7 rounded-full flex-shrink-0 object-cover border border-gray-700">
                            <?php else: ?>
                            <div class="w-7 h-7 rounded-full flex-shrink-0
                                        bg-gradient-to-br from-red-600/60 to-red-900/60
                                        border border-red-500/20 flex items-center justify-center
                                        text-xs font-bold text-red-300">
                                <?= htmlspecialchars(strtoupper(mb_substr($c['nombre'], 0, 1))) ?>
                            </div>
                            <?php endif; ?>
                            <div>
                        <a href="<?= BASE_URL ?>/clientes/<?= $c['id'] ?>"
                           class="font-medium text-white hover:text-red-400 transition-colors">
                            <?= htmlspecialchars($c['nombre']) ?>
                        </a>
                        <?php if ($c['notas']): ?>
                        <p class="text-gray-600 text-xs truncate max-w-xs mt-0.5">
                            <?= htmlspecialchars($c['notas']) ?>
                        </p>
                        <?php endif; ?>
                        <?php
                        // Render tag badges from GROUP_CONCAT columns
                        if (!empty($c['tag_ids'])) {
                            $tagIds     = explode(',',  $c['tag_ids']);
                            $tagNombres = explode('||', $c['tag_nombres'] ?? '');
                            $tagColores = explode(',',  $c['tag_colores'] ?? '');
                        ?>
                        <div class="flex flex-wrap gap-1 mt-1">
                            <?php foreach ($tagIds as $ti => $tid): ?>
                            <span class="inline-block px-1.5 py-px rounded-full text-[10px] font-medium border"
                                  style="color:<?= htmlspecialchars($tagColores[$ti] ?? '#ef4444') ?>;
                                         background:<?= htmlspecialchars($tagColores[$ti] ?? '#ef4444') ?>1a;
                                         border-color:<?= htmlspecialchars($tagColores[$ti] ?? '#ef4444') ?>40;">
                                <?= htmlspecialchars($tagNombres[$ti] ?? '') ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php } ?>
                            </div><!-- /name block -->
                        </div><!-- /avatar row -->
                    </td>
                    <td class="px-4 py-3 text-gray-400 hidden sm:table-cell">
                        <?php if ($c['instagram']): ?>
                        <span class="text-blue-400">
                            <?= htmlspecialchars($c['instagram']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-700">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-400 hidden md:table-cell">
                        <?= $c['telefono'] ? htmlspecialchars($c['telefono']) : '<span class="text-gray-700">—</span>' ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">
                        <?= $c['primera_visita']
                            ? date('d/m/Y', strtotime($c['primera_visita']))
                            : '<span class="text-gray-700">—</span>' ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if ((int)$c['total_tatuajes'] > 0): ?>
                        <span class="inline-flex items-center justify-center w-6 h-6
                                     bg-red-600/20 text-red-400 rounded-full text-xs font-semibold">
                            <?= $c['total_tatuajes'] ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-700 text-xs">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <!-- Ver ficha -->
                            <a href="<?= BASE_URL ?>/clientes/<?= $c['id'] ?>"
                               title="<?= htmlspecialchars(__('client.view_file')) ?>"
                               class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                            <!-- Editar -->
                            <a href="<?= BASE_URL ?>/clientes/<?= $c['id'] ?>/editar"
                               title="<?= htmlspecialchars(__('btn.edit')) ?>"
                               class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-700 rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0
                                             01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                </svg>
                            </a>
                            <!-- Borrar -->
                            <form method="POST"
                                  action="<?= BASE_URL ?>/clientes/<?= $c['id'] ?>/borrar"
                                  onsubmit="return confirm('<?= htmlspecialchars(__('client.delete_confirm', ['name' => addslashes($c['nombre'])])) ?>')">
                                <input type="hidden" name="_csrf" value="<?= \App\Core\Auth::csrfToken() ?>">
                                <button type="submit"
                                        title="<?= htmlspecialchars(__('btn.delete')) ?>"
                                        class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
                                                 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244
                                                 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456
                                                 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
                                                 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164
                                                 -2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09
                                                 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div class="px-4 py-3 border-t border-gray-800 flex items-center justify-between text-sm">
        <p class="text-gray-500 text-xs">
            <?= __('common.showing') ?> <?= number_format(($page - 1) * 20 + 1) ?>–<?= number_format(min($page * 20, $total)) ?>
            <?= __('common.of') ?> <?= number_format($total) ?>
        </p>
        <div class="flex items-center gap-1">
            <?php
            $pageBase = '?' . http_build_query(array_filter(['q' => $term ?: null, 'etiqueta' => $etiquetaId ?: null]));
            $pageSep  = str_contains($pageBase, '?') && strlen($pageBase) > 1 ? '&' : '?';
            ?>
            <?php if ($page > 1): ?>
            <a href="<?= $pageBase ?><?= $pageSep ?>page=<?= $page - 1 ?>"
               class="px-2.5 py-1 rounded-md bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700">←</a>
            <?php endif; ?>

            <?php
            $from = max(1, $page - 2);
            $to   = min($pages, $page + 2);
            for ($p = $from; $p <= $to; $p++): ?>
            <a href="<?= $pageBase ?><?= $pageSep ?>page=<?= $p ?>"
               class="px-2.5 py-1 rounded-md text-sm
                      <?= $p === $page ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
            <a href="<?= $pageBase ?><?= $pageSep ?>page=<?= $page + 1 ?>"
               class="px-2.5 py-1 rounded-md bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700">→</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
