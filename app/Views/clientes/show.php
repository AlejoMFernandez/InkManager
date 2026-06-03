<?php
$pageTitle = htmlspecialchars($cliente['nombre']);
use App\Core\Auth;
$csrf = Auth::csrfToken();

// ── Cobertura corporal (Feature 1C) ───────────────────────────────────────────
// Asigna la posición 3D de cada tatuaje a una de las 12 zonas del body map.
// Umbrales calibrados para el modelo procedurar (~1.87m de altura en coords Three.js).
$detectZone = function(float $x, float $y, float $z): string {
    $absX = abs($x);
    if ($y > 1.62)                           return 'head';
    if ($y > 1.50)                           return 'neck';
    if ($absX > 0.23 && $y >= 0.82) {
        if ($absX > 0.38 && $y <= 0.92)     return $x < 0 ? 'hand-left' : 'hand-right';
        return $x < 0 ? 'arm-left' : 'arm-right';
    }
    if ($y <= 0.08)                          return $x <= 0 ? 'foot-left' : 'foot-right';
    if ($y <  0.82)                          return $x <= 0 ? 'leg-left'  : 'leg-right';
    return $z >= 0 ? 'chest' : 'back';
};

$zonasCubiertas = [];
foreach ($cliente['tatuajes'] as $t) {
    if ($t['pos_x'] !== null) {
        $zoneName = $detectZone((float)$t['pos_x'], (float)$t['pos_y'], (float)$t['pos_z']);
        $zonasCubiertas[$zoneName] = true;
    }
}

$totalZonas   = 12;
$numCubiertas = count($zonasCubiertas);
$coberturaPct = $totalZonas > 0 ? round($numCubiertas / $totalZonas * 100) : 0;

// SVG donut: r=14 → circumference ≈ 87.96
$donutR        = 14;
$circumference = 2 * M_PI * $donutR;
$dashOffset    = $circumference * (1 - $coberturaPct / 100);

$gruposZonas = [
    __('coverage.group_face')  => ['head', 'neck'],
    __('coverage.group_torso') => ['chest', 'back'],
    __('coverage.group_arms')  => ['arm-left', 'arm-right', 'hand-left', 'hand-right'],
    __('coverage.group_legs')  => ['leg-left', 'leg-right', 'foot-left', 'foot-right'],
];

// Pasar datos a JS
$tatuajesJs = array_map(fn($t) => [
    'id'       => (int) $t['id'],
    'pos_x'    => $t['pos_x'] !== null ? (float) $t['pos_x'] : null,
    'pos_y'    => $t['pos_y'] !== null ? (float) $t['pos_y'] : null,
    'pos_z'    => $t['pos_z'] !== null ? (float) $t['pos_z'] : null,
    'normal_x' => $t['normal_x'] !== null ? (float) $t['normal_x'] : null,
    'normal_y' => $t['normal_y'] !== null ? (float) $t['normal_y'] : null,
    'normal_z' => $t['normal_z'] !== null ? (float) $t['normal_z'] : null,
    'estilo'   => $t['estilo_nombre'] ?? null,
    'fecha'    => $t['fecha']   ?? null,
    'precio'   => $t['precio']  !== null ? (float) $t['precio'] : null,
    'foto'     => $t['foto_path'] ? PUBLIC_URL . '/assets/uploads/' . $t['foto_path'] : null,
], $cliente['tatuajes']);

// importmap + config van al <head> vía $extraHead
ob_start();
?>
<script type="importmap">
{
    "imports": {
        "three":           "https://cdn.jsdelivr.net/npm/three@0.169.0/build/three.module.js",
        "three/addons/":   "https://cdn.jsdelivr.net/npm/three@0.169.0/examples/jsm/"
    }
}
</script>
<style>
    #bodymap-canvas {
        display: block;
        width: 100%;
        height: 100%;
        cursor: grab;
    }
    #bodymap-canvas:active { cursor: grabbing; }
    @keyframes modalIn {
        from { opacity:0; transform:translateY(-8px) scale(.97); }
        to   { opacity:1; }
    }
    .modal-enter { animation: modalIn 0.22s cubic-bezier(.16,1,.3,1) both; }

    /* ── Zone navigation buttons ── */
    .zone-btn {
        display:        inline-flex;
        flex-direction: column;
        align-items:    center;
        gap:            0.22rem;
        padding:        0.42rem 0.4rem;
        border-radius:  0.5rem;
        border:         1px solid transparent;
        font-size:      0.58rem;
        font-weight:    600;
        letter-spacing: 0.01em;
        color:          #6b7280;
        cursor:         pointer;
        background:     transparent;
        transition:     background 120ms, border-color 120ms, color 120ms;
        user-select:    none;
        min-width:      38px;
        line-height:    1;
    }
    .zone-btn:hover:not(.active) {
        background:     rgb(31 41 55 / 1);
        border-color:   #374151;
        color:          #d1d5db;
    }
    .zone-btn.active {
        background:     rgba(239,68,68,.12);
        border-color:   rgba(239,68,68,.35);
        color:          #fff;
    }
    .zone-btn svg { flex-shrink: 0; }
</style>
<?php
$extraHead = ob_get_clean();

ob_start();
?>
<script>
window.BODYMAP_CONFIG = {
    modelUrlMasc: <?= json_encode(
        file_exists(PUBLIC_PATH . '/assets/models/body_male.glb')
            ? PUBLIC_URL . '/assets/models/body_male.glb'
            : (file_exists(PUBLIC_PATH . '/assets/models/body.glb')
                ? PUBLIC_URL . '/assets/models/body.glb'
                : null)
    ) ?>,
    modelUrlFem: <?= json_encode(
        file_exists(PUBLIC_PATH . '/assets/models/body_female.glb')
            ? PUBLIC_URL . '/assets/models/body_female.glb'
            : null
    ) ?>,
    genero:    <?= json_encode($cliente['genero'] ?? 'masculino') ?>,
    tatuajes:  <?= json_encode($tatuajesJs, JSON_UNESCAPED_UNICODE) ?>,
    clienteId: <?= (int) $cliente['id'] ?>,
    csrf:      <?= json_encode($csrf) ?>,
    base:      <?= json_encode(BASE_URL) ?>
};
</script>
<script type="module" src="<?= PUBLIC_URL ?>/assets/js/bodymap.js"></script>
<script type="module" src="<?= PUBLIC_URL ?>/assets/js/markers.js"></script>
<?php
$extraScripts = ob_get_clean();
?>

<!-- ── Breadcrumb + acciones ──────────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 page-enter">
    <div>
        <p class="brand-tagline mb-1"><?= __('client.file_tag') ?></p>
        <div class="flex items-center gap-2 text-sm">
            <a href="<?= BASE_URL ?>/clientes" class="text-gray-500 hover:text-white link-underline"><?= __('nav.clients') ?></a>
            <span class="text-gray-700">/</span>
            <span class="font-display text-xl text-white tracking-wider uppercase"><?= htmlspecialchars($cliente['nombre']) ?></span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/turnos/nuevo?cliente_id=<?= $cliente['id'] ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5
                  bg-gray-800 border border-gray-700 hover:border-gray-600
                  text-gray-300 hover:text-white text-sm rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
            <?= __('appointment.new') ?>
        </a>
        <a href="<?= BASE_URL ?>/clientes/<?= $cliente['id'] ?>/editar"
           class="inline-flex items-center gap-1.5 px-3 py-1.5
                  bg-gray-800 border border-gray-700 hover:border-gray-600
                  text-gray-300 hover:text-white text-sm rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
            </svg>
            <?= __('btn.edit') ?>
        </a>
        <a href="<?= BASE_URL ?>/clientes/<?= $cliente['id'] ?>/imprimir"
           target="_blank"
           title="Abrir ficha imprimible"
           class="inline-flex items-center gap-1.5 px-3 py-1.5
                  bg-gray-800 border border-gray-700 hover:border-gray-600
                  text-gray-400 hover:text-white text-sm rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 14h12v8H6z"/>
            </svg>
            <?= __('export.print_file') ?>
        </a>
        <?php if (!empty($cliente['telefono'])): ?>
        <?php
            $waPhone = ltrim(preg_replace('/[^\d]/', '', $cliente['telefono']), '0');
            $waMsg   = "Hola {$cliente['nombre']}! 👋";
            $waHref  = 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMsg);
        ?>
        <a href="<?= $waHref ?>" target="_blank" rel="noopener noreferrer"
           title="Abrir WhatsApp con <?= htmlspecialchars($cliente['nombre']) ?>"
           class="inline-flex items-center gap-1.5 px-3 py-1.5
                  bg-green-600/15 hover:bg-green-600/25 border border-green-600/30
                  text-green-400 hover:text-green-300 text-sm rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            WhatsApp
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Layout principal ───────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 xl:grid-cols-[280px_1fr] gap-4">

    <!-- ── Sidebar izquierdo ─────────────────────────────────────────────── -->
    <div class="space-y-4">

        <!-- Tarjeta de datos -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in">
            <div class="flex items-center gap-3 mb-4">

                <!-- Avatar clickable ─────────────────────────────────────── -->
                <div id="avatar-trigger"
                     class="relative flex-shrink-0 group/avatar cursor-pointer"
                     title="Cambiar foto de perfil">
                    <?php if (!empty($cliente['foto_perfil'])): ?>
                    <img id="avatar-preview"
                         src="<?= PUBLIC_URL ?>/assets/uploads/avatars/<?= htmlspecialchars($cliente['foto_perfil']) ?>"
                         alt="Avatar"
                         class="w-11 h-11 rounded-xl object-cover border border-red-500/25">
                    <?php else: ?>
                    <div id="avatar-initials"
                         class="w-11 h-11 rounded-xl
                                bg-gradient-to-br from-red-600/40 to-red-900/60
                                border border-red-500/25 shadow-inner
                                flex items-center justify-center text-red-300 text-base font-bold">
                        <?= strtoupper(mb_substr($cliente['nombre'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    <!-- Camera overlay on hover -->
                    <div class="absolute inset-0 rounded-xl bg-black/60
                                flex items-center justify-center
                                opacity-0 group-hover/avatar:opacity-100
                                transition-opacity duration-150 pointer-events-none">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175
                                     C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15
                                     A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169
                                     a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055
                                     l-.822-1.316a2.192 2.192 0 00-1.736-1.039 50.655 50.655 0 00-4.232 0
                                     2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z
                                     M18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                    </div>
                    <!-- Upload spinner -->
                    <div id="avatar-spinner"
                         class="absolute inset-0 rounded-xl bg-gray-950/80
                                items-center justify-center hidden">
                        <div class="w-4 h-4 border-2 border-gray-600 border-t-red-500
                                    rounded-full animate-spin"></div>
                    </div>
                </div>
                <input type="file" id="avatar-input"
                       accept="image/jpeg,image/png,image/webp" class="sr-only">
                <!-- /Avatar ─────────────────────────────────────────────── -->

                <div class="min-w-0">
                    <h2 class="text-base font-bold text-white leading-tight truncate">
                        <?= htmlspecialchars($cliente['nombre']) ?>
                    </h2>
                    <?php if ($cliente['instagram']): ?>
                    <p class="text-blue-400 text-xs truncate"><?= htmlspecialchars($cliente['instagram']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="space-y-2 text-sm">
                <?php if ($cliente['telefono']): ?>
                <div class="flex gap-2">
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5"><?= __('client.col_phone') ?></dt>
                    <dd class="text-gray-300 text-xs"><?= htmlspecialchars($cliente['telefono']) ?></dd>
                </div>
                <?php endif; ?>
                <div class="flex gap-2">
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5"><?= __('client.first_visit_abbr') ?></dt>
                    <dd class="text-gray-300 text-xs">
                        <?= $cliente['primera_visita']
                            ? date('d/m/Y', strtotime($cliente['primera_visita']))
                            : '<span class="text-gray-700">—</span>' ?>
                    </dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5"><?= __('client.tattoos_label') ?></dt>
                    <dd class="text-white font-semibold text-sm"><?= count($cliente['tatuajes']) ?></dd>
                </div>
            </dl>

            <?php if ($cliente['notas']): ?>
            <div class="mt-3 pt-3 border-t border-gray-800">
                <p class="text-xs text-gray-600 mb-1"><?= __('client.notes_label') ?></p>
                <p class="text-gray-400 text-xs leading-relaxed whitespace-pre-line">
                    <?= htmlspecialchars($cliente['notas']) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Cobertura corporal ── -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:60ms">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= __('coverage.title') ?></h3>
                <span class="text-xs font-bold <?= $coberturaPct > 0 ? 'text-red-400' : 'text-gray-600' ?>">
                    <?= $coberturaPct ?>%
                </span>
            </div>

            <!-- Donut + resumen -->
            <div class="flex items-center gap-3 mb-4">
                <div class="relative flex-shrink-0 w-[54px] h-[54px]">
                    <svg width="54" height="54" viewBox="0 0 36 36">
                        <!-- Track -->
                        <circle cx="18" cy="18" r="<?= $donutR ?>"
                                fill="none" stroke="#1f2937" stroke-width="3.5"/>
                        <!-- Progress -->
                        <?php if ($coberturaPct > 0): ?>
                        <circle cx="18" cy="18" r="<?= $donutR ?>"
                                fill="none" stroke="#ef4444" stroke-width="3.5"
                                stroke-linecap="round"
                                stroke-dasharray="<?= round($circumference, 2) ?>"
                                stroke-dashoffset="<?= round($dashOffset, 2) ?>"
                                transform="rotate(-90 18 18)"/>
                        <?php endif; ?>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-white text-[10px] font-bold leading-none"><?= $coberturaPct ?>%</span>
                    </div>
                </div>
                <div>
                    <p class="text-white text-sm font-semibold leading-tight">
                        <?= $numCubiertas ?> / <?= $totalZonas ?>
                        <span class="text-gray-500 font-normal"><?= __('coverage.zones') ?></span>
                    </p>
                    <p class="text-gray-600 text-xs mt-0.5">
                        <?php $nTat = count($cliente['tatuajes']); ?>
                        <?= $nTat ?> <?= $nTat !== 1 ? __('tattoo.plural') : __('tattoo.singular') ?> <?= __('bodymap.marked') ?>
                    </p>
                </div>
            </div>

            <!-- Barras por grupo -->
            <div class="space-y-2.5">
                <?php foreach ($gruposZonas as $grupo => $zonas):
                    $cubGrupo  = count(array_filter($zonas, fn($z) => isset($zonasCubiertas[$z])));
                    $totGrupo  = count($zonas);
                    $pctGrupo  = $totGrupo > 0 ? round($cubGrupo / $totGrupo * 100) : 0;
                    // primera zona del grupo para el clic de navegación
                    $zoneNav   = $zonas[0];
                ?>
                <div data-goto-zone="<?= $zoneNav ?>" class="group/cov cursor-pointer" title="Ir a <?= $grupo ?>">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] text-gray-500 group-hover/cov:text-gray-300 transition-colors">
                            <?= $grupo ?>
                        </span>
                        <span class="text-[11px] font-medium <?= $pctGrupo > 0 ? 'text-gray-400' : 'text-gray-700' ?>">
                            <?= $cubGrupo ?>/<?= $totGrupo ?>
                        </span>
                    </div>
                    <div class="h-1 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width:<?= $pctGrupo ?>%;<?= $pctGrupo > 0
                                 ? ' background:linear-gradient(to right,#dc2626,#ef4444)'
                                 : '' ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($numCubiertas === 0): ?>
            <p class="text-gray-700 text-[11px] text-center mt-3 leading-snug">
                <?= nl2br(htmlspecialchars(__('coverage.hint'))) ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Próximos turnos -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:120ms">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= __('appointment.upcoming') ?></h3>
                <a href="<?= BASE_URL ?>/turnos/nuevo?cliente_id=<?= $cliente['id'] ?>"
                   class="text-xs text-red-400 hover:text-red-300"><?= __('appointment.schedule') ?></a>
            </div>
            <?php if (empty($turnos)): ?>
            <p class="text-gray-700 text-xs py-3 text-center"><?= __('appointment.none') ?></p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($turnos as $t): ?>
                <div class="flex items-center gap-2 text-xs">
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></div>
                    <span class="text-gray-300">
                        <?= date('d/m H:i', strtotime($t['fecha_inicio'])) ?>
                    </span>
                    <span class="text-gray-600 ml-auto"><?= $t['duracion_min'] ?>min</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Etiquetas (Feature 2B) ─────────────────────────────────── -->
        <div id="tags-card" class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:150ms">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= __('tag.title') ?></h3>
                <button id="tags-add-btn"
                        class="text-xs text-red-400 hover:text-red-300 transition-colors"
                        title="<?= htmlspecialchars(__('tag.title')) ?>">
                    <?= __('tag.add') ?>
                </button>
            </div>

            <!-- Chips actuales -->
            <div id="tags-list" class="flex flex-wrap gap-1.5 min-h-[24px]">
                <?php if (empty($clienteTags)): ?>
                <span id="tags-empty" class="text-gray-700 text-xs"><?= __('tag.none') ?></span>
                <?php endif; ?>
                <?php foreach ($clienteTags as $tag): ?>
                <span class="tag-chip inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                             border cursor-default group/chip"
                      data-id="<?= $tag['id'] ?>"
                      style="color:<?= htmlspecialchars($tag['color']) ?>;
                             background:<?= htmlspecialchars($tag['color']) ?>1a;
                             border-color:<?= htmlspecialchars($tag['color']) ?>40;">
                    <?= htmlspecialchars($tag['nombre']) ?>
                    <button class="tag-remove opacity-0 group-hover/chip:opacity-100 transition-opacity
                                   ml-0.5 -mr-0.5 hover:scale-110"
                            data-id="<?= $tag['id'] ?>"
                            title="Quitar etiqueta">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                <?php endforeach; ?>
            </div>

            <!-- Popover de selección -->
            <div id="tags-popover"
                 class="hidden mt-3 pt-3 border-t border-gray-800">

                <!-- Buscador interno -->
                <input id="tags-search" type="text" placeholder="<?= htmlspecialchars(__('tag.search_ph')) ?>"
                       autocomplete="off"
                       class="w-full px-2.5 py-1.5 mb-2 bg-gray-800 border border-gray-700 rounded-lg
                              text-xs text-white placeholder-gray-600
                              focus:outline-none focus:ring-1 focus:ring-red-600/50">

                <!-- Lista de etiquetas disponibles -->
                <div id="tags-available" class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                    <?php foreach ($etiquetas as $tag): ?>
                    <button class="tag-option inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                                   text-xs font-medium border transition-all hover:opacity-80"
                            data-id="<?= $tag['id'] ?>"
                            data-nombre="<?= htmlspecialchars($tag['nombre']) ?>"
                            style="color:<?= htmlspecialchars($tag['color']) ?>;
                                   background:<?= htmlspecialchars($tag['color']) ?>1a;
                                   border-color:<?= htmlspecialchars($tag['color']) ?>40;">
                        <?= htmlspecialchars($tag['nombre']) ?>
                    </button>
                    <?php endforeach; ?>
                    <?php if (empty($etiquetas)): ?>
                    <p class="text-gray-600 text-xs w-full text-center py-1"><?= __('tag.no_tags_available') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Crear nueva -->
                <div id="tags-create-row" class="hidden mt-2 flex items-center gap-2">
                    <div id="tags-color-picker" class="flex gap-1 flex-wrap">
                        <?php foreach (\App\Models\Etiqueta::PRESET_COLORS as $c): ?>
                        <button class="color-dot w-4 h-4 rounded-full border-2 border-transparent
                                       hover:scale-110 transition-transform"
                                data-color="<?= $c ?>"
                                style="background:<?= $c ?>"
                                title="<?= $c ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <button id="tags-create-btn"
                            class="ml-auto flex-shrink-0 px-2.5 py-1 bg-red-600 hover:bg-red-500
                                   text-white text-xs rounded-lg transition-colors">
                        <?= __('tag.create_new') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tatuajes list -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-in" style="animation-delay:180ms">
            <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= __('tattoo.list_title') ?></h3>
                <span id="tatuajes-count" class="text-xs text-gray-600"><?= count($cliente['tatuajes']) ?></span>
            </div>
            <div id="sidebar-tatuajes-list" class="divide-y divide-gray-800">
                <?php if (empty($cliente['tatuajes'])): ?>
                <p class="text-gray-700 text-xs py-5 text-center">
                    <?= __('tattoo.add_hint') ?>
                </p>
                <?php endif; ?>
                <?php foreach ($cliente['tatuajes'] as $tat): ?>
                <div class="flex items-center gap-3 px-4 py-3
                             hover:bg-gray-800/50 hover:pl-5 transition-all duration-200 cursor-pointer
                             group/tat"
                     data-tatuaje-id="<?= $tat['id'] ?>">
                    <!-- Thumb -->
                    <div class="w-10 h-10 rounded-lg bg-gray-800 border border-gray-700
                                flex-shrink-0 overflow-hidden">
                        <?php if ($tat['foto_path']): ?>
                        <img src="<?= PUBLIC_URL ?>/assets/uploads/<?= htmlspecialchars($tat['foto_path']) ?>"
                             alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor"
                                 stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5
                                         l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5
                                         0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5
                                         1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375
                                         0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-medium truncate">
                            <?= htmlspecialchars($tat['estilo_nombre'] ?? __('tattoo.no_style')) ?>
                        </p>
                        <p class="text-gray-600 text-xs">
                            <?= $tat['fecha'] ? date('d/m/Y', strtotime($tat['fecha'])) : '—' ?>
                            <?php if ($tat['precio']): ?>
                            · $<?= number_format((float)$tat['precio'], 0, ',', '.') ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <!-- Indicator: tiene posición 3D -->
                    <?php if ($tat['pos_x'] !== null): ?>
                    <div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"
                         title="Posición en body map"></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ── Body Map (columna principal) ─────────────────────────────────── -->
    <div class="flex flex-col gap-4">

        <div id="bodymap-container"
             class="bg-gray-950 border border-gray-800 rounded-xl overflow-hidden relative
                    shadow-[0_0_40px_rgba(220,38,38,0.06)_inset]"
             style="height: clamp(380px, 55vh, 640px);">

            <!-- Canvas Three.js -->
            <canvas id="bodymap-canvas" class="w-full h-full"></canvas>

            <?php if (($cliente['genero'] ?? 'masculino') === 'otro'): ?>
            <!-- Toggle masculino/femenino (solo para clientes con género "otro") -->
            <div class="absolute top-3 left-3 flex items-center
                        bg-black/60 backdrop-blur-sm rounded-full border border-gray-800 p-0.5 z-10">
                <button data-gender-btn="masculino"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-full
                               text-xs font-medium transition-all duration-200 select-none">
                    ♂ Masc
                </button>
                <button data-gender-btn="femenino"
                        class="flex items-center gap-1.5 px-3 py-1 rounded-full
                               text-xs font-medium transition-all duration-200 select-none">
                    ♀ Fem
                </button>
            </div>
            <?php endif; ?>

            <!-- Loader overlay -->
            <div id="bodymap-loader"
                 class="absolute inset-0 flex flex-col items-center justify-center
                        bg-gray-950/80 backdrop-blur-sm"
                 style="display:none">
                <div class="w-8 h-8 rounded-full border-2 border-gray-700
                            border-t-red-500 animate-spin mb-3"></div>
                <p class="text-gray-500 text-xs"><?= __('bodymap.loading') ?></p>
            </div>

            <!-- Hint overlay (fade on first interact) -->
            <div id="bodymap-hint"
                 class="absolute bottom-3 left-0 right-0 flex justify-center
                        pointer-events-none transition-opacity duration-500">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full
                            bg-black/50 backdrop-blur-sm border border-gray-800">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227
                                 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25
                                 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59"/>
                    </svg>
                    <span class="text-gray-400 text-xs"><?= __('bodymap.hint') ?></span>
                </div>
            </div>

            <!-- Badge: modelo activo -->
            <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1
                        bg-black/60 backdrop-blur-sm rounded-full border border-gray-800">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-gray-400 text-xs" id="bodymap-status"><?= __('bodymap.status') ?></span>
            </div>

        </div>

        <!-- ── Panel de navegación por zonas ─────────────────────────── -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-2.5">
            <div class="flex flex-wrap gap-1">

                <?php
                // [zone_key, label, svg_path_content]
                $zones = [
                    // Vista general
                    ['full', 'Todo',
                        '<circle cx="12" cy="7" r="4"/>
                         <path d="M4 21v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2"/>'],

                    // Cabeza
                    ['head', 'Cara',
                        '<path d="M12 3C8.5 3 6 6 6 9.5c0 3 1.7 5.5 4 6.8V18h4v-1.7c2.3-1.3 4-3.8 4-6.8C18 6 15.5 3 12 3z"/>'],

                    // Cuello
                    ['neck', 'Cuello',
                        '<rect x="8.5" y="2" width="7" height="20" rx="3.5"/>'],

                    // Pecho (front torso)
                    ['chest', 'Pecho',
                        '<path d="M7 4h10l-1.5 16h-7L7 4z"/>
                         <line x1="12" y1="4" x2="12" y2="20"/>'],

                    // Espalda (rear torso — dashed spine)
                    ['back', 'Espalda',
                        '<path d="M7 4h10l-1.5 16h-7L7 4z"/>
                         <path d="M12 6v12" stroke-dasharray="2 1.5"/>'],

                    // Brazo izquierdo (capsule angled left)
                    ['arm-left', 'Br. Izq',
                        '<rect x="7" y="2" width="10" height="20" rx="5" transform="rotate(-20 12 12)"/>'],

                    // Brazo derecho (capsule angled right)
                    ['arm-right', 'Br. Der',
                        '<rect x="7" y="2" width="10" height="20" rx="5" transform="rotate(20 12 12)"/>'],

                    // Mano izquierda (palm + thumb extending right = anatomical left)
                    ['hand-left', 'Mn. Izq',
                        '<rect x="8" y="7" width="8" height="12" rx="4"/>
                         <rect x="7" y="2" width="2.5" height="7" rx="1.25"/>
                         <rect x="10" y="1" width="2.5" height="7" rx="1.25"/>
                         <rect x="13" y="2" width="2.5" height="7" rx="1.25"/>
                         <path d="M8 11a3 3 0 0 0-3 3" stroke-linecap="round"/>'],

                    // Mano derecha (thumb extending left = anatomical right)
                    ['hand-right', 'Mn. Der',
                        '<rect x="8" y="7" width="8" height="12" rx="4"/>
                         <rect x="8.5" y="2" width="2.5" height="7" rx="1.25"/>
                         <rect x="11.5" y="1" width="2.5" height="7" rx="1.25"/>
                         <rect x="14.5" y="2" width="2.5" height="7" rx="1.25"/>
                         <path d="M16 11a3 3 0 0 1 3 3" stroke-linecap="round"/>'],

                    // Pierna izquierda (cápsula larga, ligeramente a la izq)
                    ['leg-left', 'Pr. Izq',
                        '<rect x="4.5" y="2" width="11" height="20" rx="5.5"/>'],

                    // Pierna derecha (cápsula larga, ligeramente a la der)
                    ['leg-right', 'Pr. Der',
                        '<rect x="8.5" y="2" width="11" height="20" rx="5.5"/>'],

                    // Pie izquierdo (tobillo izq + planta extiende a derecha)
                    ['foot-left', 'Pie Izq',
                        '<rect x="5" y="2" width="7" height="14" rx="3.5"/>
                         <rect x="5" y="14" width="14" height="7" rx="3.5"/>'],

                    // Pie derecho (tobillo der + planta extiende a izquierda)
                    ['foot-right', 'Pie Der',
                        '<rect x="12" y="2" width="7" height="14" rx="3.5"/>
                         <rect x="5" y="14" width="14" height="7" rx="3.5"/>'],
                ];
                ?>

                <?php foreach ($zones as [$zoneKey, $label, $svgContent]): ?>
                <button data-zone="<?= $zoneKey ?>"
                        class="zone-btn <?= $zoneKey === 'full' ? 'active' : '' ?>"
                        title="<?= htmlspecialchars($label) ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <?= $svgContent ?>
                    </svg>
                    <span><?= $label ?></span>
                </button>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- Info bar debajo del canvas -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-3
                    flex flex-wrap items-center gap-4 text-xs text-gray-500">
            <?php $nTatTotal = count($cliente['tatuajes']); ?>
            <span>
                <span class="text-white font-semibold"><?= $nTatTotal ?></span>
                <?= $nTatTotal === 1 ? __('tattoo.singular') : __('tattoo.plural') ?>
            </span>
            <span class="text-gray-700">|</span>
            <?php
            $conPos = count(array_filter($cliente['tatuajes'], fn($t) => $t['pos_x'] !== null));
            ?>
            <span>
                <span class="text-white font-semibold"><?= $conPos ?></span>
                <?= __('bodymap.marked') ?>
            </span>
            <span class="text-gray-700">|</span>
            <?php
            $ingTotal = array_sum(array_column($cliente['tatuajes'], 'precio'));
            ?>
            <span>
                <?= __('common.total') ?>:
                <span class="text-white font-semibold">
                    $<?= number_format((float) $ingTotal, 0, ',', '.') ?>
                </span>
            </span>
            <span id="bodymap-marked-count" class="hidden"><?= $conPos ?></span>
            <span class="ml-auto text-gray-600 text-xs hidden sm:block">
                <?= __('bodymap.click_hint') ?>
            </span>
        </div>

    </div>
</div>

<?php
// ── Feature 2C: Timeline de sesiones ─────────────────────────────────────────
$showTimeline = !empty($historial) || !empty($cliente['primera_visita']);
?>
<?php if ($showTimeline): ?>
<div class="mt-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="brand-tagline mb-1"><?= __('timeline.tag') ?></p>
            <h3 class="section-heading text-base">
                <?= __('timeline.title') ?>
            </h3>
        </div>
        <?php if (!empty($historial)): ?>
        <?php $nH = count($historial); ?>
        <span class="text-xs text-gray-600">
            <?= $nH ?> <?= $nH !== 1 ? __('appointment.plural') : __('appointment.singular') ?>
        </span>
        <?php endif; ?>
    </div>

    <div class="relative">
        <!-- Línea vertical de conexión -->
        <div class="absolute left-[19px] top-3 bottom-3 w-px bg-gray-800/80 pointer-events-none"></div>

        <div class="space-y-2.5">

            <?php foreach ($historial as $h):
                $ts       = strtotime($h['fecha_inicio']);
                $isPast   = $ts < time();
                $estado   = $h['estado'];
                $fechaFmt = date('d M Y', $ts);
                $horaFmt  = date('H:i', $ts);

                // Dot color + badge style + translated label by estado
                $estadoLabel = __('status.' . $estado) ?: ucfirst($estado);
                [$dotCls, $badgeCls] = match ($estado) {
                    'hecho'      => ['bg-green-500',      'text-green-400 bg-green-500/10 border-green-500/20'],
                    'confirmado' => ['bg-blue-500',       'text-blue-400  bg-blue-500/10  border-blue-500/20'],
                    'agendado'   => ['bg-yellow-400',     'text-yellow-400 bg-yellow-500/10 border-yellow-500/20'],
                    'cancelado'  => ['bg-gray-600',       'text-gray-500  bg-gray-500/10  border-gray-500/20'],
                    default      => ['bg-gray-600',       'text-gray-500  bg-gray-500/10  border-gray-500/20'],
                };
                $cardOpacity  = $estado === 'cancelado' ? 'opacity-50' : '';
                $cardBorder   = !$isPast ? 'border-dashed' : '';
            ?>
            <div class="flex gap-3 <?= $cardOpacity ?>">

                <!-- Dot -->
                <div class="relative flex-shrink-0 w-10 flex justify-center pt-[14px]">
                    <div class="w-2.5 h-2.5 rounded-full <?= $dotCls ?> ring-2 ring-gray-950 z-10
                                <?= $estado === 'agendado' && !$isPast ? 'animate-pulse' : '' ?>"></div>
                </div>

                <!-- Card -->
                <div class="flex-1 min-w-0 pb-0.5">
                    <div class="bg-gray-900 border border-gray-800 <?= $cardBorder ?> rounded-xl
                                px-4 py-3 hover:border-gray-700 transition-colors">

                        <!-- Top row: fecha + badge -->
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-white text-sm font-semibold"><?= $fechaFmt ?></span>
                                <span class="text-gray-600 text-xs"><?= $horaFmt ?></span>
                                <?php if (!$isPast): ?>
                                <span class="text-[10px] text-yellow-400/80 bg-yellow-500/10
                                             px-1.5 py-0.5 rounded-full border border-yellow-500/20">
                                    <?= __('appointment.upcoming_chip') ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] px-2 py-0.5 rounded-full border flex-shrink-0 <?= $badgeCls ?>">
                                <?= htmlspecialchars($estadoLabel) ?>
                            </span>
                        </div>

                        <!-- Meta info -->
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500">
                            <span><?= $h['duracion_min'] ?> min</span>

                            <?php if ($h['estilo_nombre']): ?>
                            <span class="text-gray-700">·</span>
                            <span class="text-gray-400"><?= htmlspecialchars($h['estilo_nombre']) ?></span>
                            <?php endif; ?>

                            <?php if ($h['sena']): ?>
                            <span class="text-gray-700">·</span>
                            <span>Seña <span class="text-gray-400">$<?= number_format((float)$h['sena'], 0, ',', '.') ?></span></span>
                            <?php endif; ?>

                            <?php if ($h['tat_precio'] && $estado === 'hecho'): ?>
                            <span class="text-gray-700">·</span>
                            <span class="text-green-400/80 font-medium">
                                $<?= number_format((float)$h['tat_precio'], 0, ',', '.') ?>
                            </span>
                            <?php endif; ?>

                            <?php if ($h['tat_id']): ?>
                            <span class="text-gray-700">·</span>
                            <a href="#"
                               onclick="event.preventDefault(); document.querySelector('[data-tatuaje-id=\'<?= $h['tat_id'] ?>\']')?.click()"
                               class="text-red-400/70 hover:text-red-400 transition-colors">
                                <?= __('appointment.view_tattoo') ?>
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Multi-sesión progress bar -->
                        <?php if ($h['tat_id'] && (int)$h['sesiones_totales'] > 1):
                            $pctSes = (int)$h['sesiones_totales'] > 0
                                ? round((int)$h['sesiones_hechas'] / (int)$h['sesiones_totales'] * 100)
                                : 0;
                        ?>
                        <div class="mt-2.5 pt-2.5 border-t border-gray-800/70">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] text-gray-600"><?= __('appointment.progress') ?></span>
                                <span class="text-[11px] text-gray-500 tabular-nums">
                                    <?= $h['sesiones_hechas'] ?>/<?= $h['sesiones_totales'] ?> <?= __('appointment.sessions') ?>
                                    <span class="text-gray-700 ml-1">(<?= $pctSes ?>%)</span>
                                </span>
                            </div>
                            <div class="h-1 bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full"
                                     style="width:<?= $pctSes ?>%;
                                            background:linear-gradient(to right,#dc2626,#ef4444)">
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Notas -->
                        <?php if ($h['notas']): ?>
                        <p class="mt-2 text-xs text-gray-600 italic leading-snug line-clamp-2">
                            "<?= htmlspecialchars($h['notas']) ?>"
                        </p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Primera visita (anclaje al fondo de la línea) -->
            <?php if (!empty($cliente['primera_visita'])): ?>
            <div class="flex gap-3">
                <div class="relative flex-shrink-0 w-10 flex justify-center pt-[14px]">
                    <div class="w-3 h-3 rounded-full bg-red-600 ring-2 ring-gray-950 z-10"></div>
                </div>
                <div class="flex-1 pb-2">
                    <div class="bg-gray-900/40 border border-gray-800/50 rounded-xl
                                px-4 py-3 flex items-center gap-3">
                        <svg class="w-4 h-4 text-red-500/70 flex-shrink-0" fill="none"
                             stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0
                                     00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563
                                     0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563
                                     0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562
                                     0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563
                                     0 00.475-.345L11.48 3.5z"/>
                        </svg>
                        <div>
                            <span class="text-gray-300 text-sm font-medium">
                                <?= date('d M Y', strtotime($cliente['primera_visita'])) ?>
                            </span>
                            <span class="text-gray-600 text-xs ml-2"><?= __('timeline.first_visit') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Empty state (sin turnos ni primera visita) -->
            <?php if (empty($historial) && empty($cliente['primera_visita'])): ?>
            <div class="flex gap-3">
                <div class="w-10 flex justify-center pt-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-gray-700 ring-2 ring-gray-950"></div>
                </div>
                <div class="flex-1 py-3">
                    <p class="text-gray-700 text-sm"><?= __('timeline.empty') ?></p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<?php endif; ?>

<?php
// ── Feature 11: Galería de fotos ──────────────────────────────────────────────
$tatuajesConFoto = array_filter($cliente['tatuajes'], fn($t) => !empty($t['foto_path']));
?>
<?php if (!empty($tatuajesConFoto)): ?>
<div class="mt-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="brand-tagline mb-1">GALERÍA</p>
            <h3 class="section-heading text-base">Fotos de tatuajes</h3>
        </div>
        <?php $nFotos = count($tatuajesConFoto); ?>
        <span class="text-xs text-gray-600">
            <?= $nFotos ?> foto<?= $nFotos !== 1 ? 's' : '' ?>
        </span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
        <?php foreach ($tatuajesConFoto as $tat):
            $fotoUrl = PUBLIC_URL . '/assets/uploads/' . $tat['foto_path'];
        ?>
        <div class="gallery-item group/gal relative bg-gray-900 border border-gray-800
                    rounded-xl overflow-hidden cursor-zoom-in
                    hover:border-red-600/40 transition-colors"
             data-tat-id="<?= (int) $tat['id'] ?>"
             onclick="window.openLightbox(<?= json_encode($fotoUrl) ?>)">

            <div class="aspect-square overflow-hidden">
                <img src="<?= htmlspecialchars($fotoUrl) ?>"
                     alt="<?= htmlspecialchars($tat['estilo_nombre'] ?? '') ?>"
                     loading="lazy"
                     class="w-full h-full object-cover
                            group-hover/gal:scale-105 transition-transform duration-300">
            </div>

            <!-- Overlay con info al hover -->
            <div class="absolute inset-0
                        bg-gradient-to-t from-black/75 via-black/20 to-transparent
                        opacity-0 group-hover/gal:opacity-100 transition-opacity duration-200
                        flex items-end p-2.5 pointer-events-none">
                <div class="min-w-0">
                    <p class="text-white text-xs font-semibold leading-tight truncate">
                        <?= htmlspecialchars($tat['estilo_nombre'] ?? 'Sin estilo') ?>
                    </p>
                    <?php if ($tat['fecha']): ?>
                    <p class="text-gray-300 text-[10px] mt-0.5">
                        <?= date('d/m/Y', strtotime($tat['fecha'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Presupuestos del cliente ───────────────────────────────────────────── -->
<div class="mt-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="brand-tagline mb-1">// Presupuestos</p>
            <h3 class="section-heading text-base">Presupuestos</h3>
        </div>
        <a href="<?= BASE_URL ?>/presupuestos/nuevo?cliente_id=<?= $cliente['id'] ?>"
           class="text-xs text-red-400 hover:text-red-300 transition-colors">
            + Nuevo →
        </a>
    </div>

    <?php if (empty($presupuestos)): ?>
    <div class="bg-gray-900/40 border border-gray-800/50 rounded-xl py-8 text-center">
        <p class="text-gray-600 text-sm">Sin presupuestos todavía.</p>
        <a href="<?= BASE_URL ?>/presupuestos/nuevo?cliente_id=<?= $cliente['id'] ?>"
           class="text-xs text-red-400 hover:text-red-300 mt-2 inline-block transition-colors">
            Crear presupuesto →
        </a>
    </div>
    <?php else: ?>
    <?php
    $estadoBadge = [
        'borrador'  => 'text-gray-400 bg-gray-500/10 border-gray-500/25',
        'enviado'   => 'text-blue-400 bg-blue-500/10 border-blue-500/25',
        'aceptado'  => 'text-green-400 bg-green-500/10 border-green-500/25',
        'rechazado' => 'text-red-400 bg-red-500/10 border-red-500/25',
    ];
    $estadoLabel = [
        'borrador'  => 'Borrador',
        'enviado'   => 'Enviado',
        'aceptado'  => 'Aceptado',
        'rechazado' => 'Rechazado',
    ];
    ?>
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="divide-y divide-gray-800/70">
            <?php foreach ($presupuestos as $pr):
                $badgeCls = $estadoBadge[$pr['estado']] ?? $estadoBadge['borrador'];
            ?>
            <div class="flex items-center gap-3 px-4 py-3
                        hover:bg-gray-800/30 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-gray-600 text-xs font-mono">
                            <?= htmlspecialchars($pr['numero']) ?>
                        </span>
                        <a href="<?= BASE_URL ?>/presupuestos/<?= $pr['id'] ?>"
                           class="text-white text-sm font-medium hover:text-red-400
                                  transition-colors truncate max-w-[260px]">
                            <?= htmlspecialchars($pr['titulo']) ?>
                        </a>
                    </div>
                    <p class="text-gray-600 text-xs mt-0.5">
                        <?= date('d/m/Y', strtotime($pr['fecha'])) ?>
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-gray-300 text-sm font-mono tabular-nums">
                        <?= !empty($pr['monto']) && (float)$pr['monto'] > 0
                            ? '$&nbsp;' . number_format((float)$pr['monto'], 0, ',', '.')
                            : '<span class="text-gray-600 text-xs">A convenir</span>' ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-full border text-[11px] font-medium <?= $badgeCls ?>">
                        <?= $estadoLabel[$pr['estado']] ?? ucfirst($pr['estado']) ?>
                    </span>
                    <a href="<?= BASE_URL ?>/presupuestos/<?= $pr['id'] ?>"
                       class="text-gray-600 hover:text-white transition-colors p-1 -mr-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ── Historial de cobros (Caja) ─────────────────────────────────────────── -->
<div class="mt-6 mb-2">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="brand-tagline mb-1">// Caja</p>
            <h3 class="section-heading text-base">Historial de cobros</h3>
        </div>
        <a href="<?= BASE_URL ?>/caja/nuevo?cliente_id=<?= $cliente['id'] ?>"
           class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">
            + Registrar cobro →
        </a>
    </div>

    <?php if (empty($pagos)): ?>
    <div class="bg-gray-900/40 border border-gray-800/50 rounded-xl py-8 text-center">
        <p class="text-gray-600 text-sm">Sin cobros registrados todavía.</p>
        <a href="<?= BASE_URL ?>/caja/nuevo?cliente_id=<?= $cliente['id'] ?>"
           class="text-xs text-emerald-400 hover:text-emerald-300 mt-2 inline-block transition-colors">
            Registrar primer cobro →
        </a>
    </div>
    <?php else: ?>
    <?php
    $totalIng  = array_sum(array_map(fn($p) => $p['tipo'] === 'ingreso' ? (float)$p['monto'] : 0, $pagos));
    $totalEgr  = array_sum(array_map(fn($p) => $p['tipo'] === 'egreso'  ? (float)$p['monto'] : 0, $pagos));
    $metIcons  = ['efectivo' => '💵', 'transferencia' => '🏦',
                  'tarjeta' => '💳', 'sena' => '📌', 'otro' => '⚙️'];
    $metLabels = ['efectivo' => 'Efectivo', 'transferencia' => 'Transfer.',
                  'tarjeta' => 'Tarjeta', 'sena' => 'Seña', 'otro' => 'Otro'];
    ?>
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <div class="divide-y divide-gray-800/70">
            <?php foreach ($pagos as $pago): ?>
            <div class="flex items-center gap-3 px-4 py-3">
                <!-- Icono tipo -->
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0
                            <?= $pago['tipo'] === 'ingreso'
                                ? 'bg-emerald-500/10 border border-emerald-500/20'
                                : 'bg-red-500/10 border border-red-500/20' ?>">
                    <svg class="w-3.5 h-3.5 <?= $pago['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-red-400' ?>"
                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <?php if ($pago['tipo'] === 'ingreso'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/>
                        <?php endif; ?>
                    </svg>
                </div>
                <!-- Concepto -->
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm truncate"><?= htmlspecialchars($pago['concepto']) ?></p>
                    <p class="text-gray-600 text-xs mt-0.5">
                        <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                        <span class="text-gray-700 mx-1">·</span>
                        <?= ($metIcons[$pago['metodo']] ?? '') . ' ' . ($metLabels[$pago['metodo']] ?? ucfirst($pago['metodo'])) ?>
                        <?php if (!empty($pago['notas'])): ?>
                        <span class="text-gray-700 mx-1">·</span>
                        <span class="italic"><?= htmlspecialchars(mb_substr($pago['notas'], 0, 40)) ?><?= mb_strlen($pago['notas']) > 40 ? '…' : '' ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <!-- Monto -->
                <span class="text-sm font-mono font-semibold tabular-nums flex-shrink-0
                             <?= $pago['tipo'] === 'ingreso' ? 'text-emerald-400' : 'text-red-400' ?>">
                    <?= $pago['tipo'] === 'ingreso' ? '+' : '−' ?>$<?= number_format((float)$pago['monto'], 0, ',', '.') ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Fila de totales -->
        <div class="px-4 py-3 border-t border-gray-800 bg-gray-950/40
                    flex items-center justify-between">
            <span class="text-xs text-gray-500">Total · <?= count($pagos) ?> registro<?= count($pagos) !== 1 ? 's' : '' ?></span>
            <div class="flex items-center gap-4 text-sm font-mono tabular-nums">
                <?php if ($totalEgr > 0): ?>
                <span class="text-red-400 text-xs">
                    −$<?= number_format($totalEgr, 0, ',', '.') ?>
                </span>
                <?php endif; ?>
                <span class="text-emerald-400 font-semibold">
                    $<?= number_format($totalIng, 0, ',', '.') ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/clientes/_modals.php'; ?>

<script>
// Ocultar hint después de primera interacción
(function () {
    const hint = document.getElementById('bodymap-hint');
    if (!hint) return;
    const hide = () => {
        hint.style.opacity = '0';
        setTimeout(() => hint.remove(), 500);
        ['mousedown','touchstart','wheel'].forEach(e =>
            document.getElementById('bodymap-canvas')?.removeEventListener(e, hide)
        );
    };
    ['mousedown','touchstart','wheel'].forEach(e =>
        document.getElementById('bodymap-canvas')?.addEventListener(e, hide, { once: true })
    );
    // Auto-ocultar a los 5s
    setTimeout(hide, 5000);
})();

// ── Feature 1C: navegación desde el panel de cobertura ────────────────────────
(function () {
    function goTo(zone) {
        if (window.__bodymap?.goToZone) {
            window.__bodymap.goToZone(zone);
        } else {
            document.addEventListener('bodymap:ready', () => window.__bodymap?.goToZone(zone), { once: true });
        }
    }
    document.querySelectorAll('[data-goto-zone]').forEach(el => {
        el.addEventListener('click', function () { goTo(this.dataset.gotoZone); });
    });
})();

// ── Feature 2B: Tags/Etiquetas ────────────────────────────────────────────────
(function () {
    var CLIENTE_ID = <?= (int) $cliente['id'] ?>;
    var CSRF       = <?= json_encode($csrf) ?>;
    var BASE       = <?= json_encode(BASE_URL) ?>;

    var addBtn     = document.getElementById('tags-add-btn');
    var popover    = document.getElementById('tags-popover');
    var searchEl   = document.getElementById('tags-search');
    var available  = document.getElementById('tags-available');
    var createRow  = document.getElementById('tags-create-row');
    var createBtn  = document.getElementById('tags-create-btn');
    var tagsList   = document.getElementById('tags-list');
    var emptyEl    = document.getElementById('tags-empty');

    // IDs currently assigned (track locally to avoid re-fetch)
    var assigned = new Set(
        Array.from(document.querySelectorAll('.tag-chip[data-id]'))
            .map(function (el) { return parseInt(el.dataset.id); })
    );

    // Selected color for new tag
    var selectedColor = '<?= \App\Models\Etiqueta::PRESET_COLORS[0] ?>';
    var colorDots = document.querySelectorAll('.color-dot');
    colorDots.forEach(function (dot) {
        if (dot.dataset.color === selectedColor) dot.classList.add('border-white');
        dot.addEventListener('click', function () {
            colorDots.forEach(function (d) { d.classList.remove('border-white'); });
            dot.classList.add('border-white');
            selectedColor = dot.dataset.color;
        });
    });

    // Toggle popover
    addBtn && addBtn.addEventListener('click', function () {
        popover.classList.toggle('hidden');
        if (!popover.classList.contains('hidden')) {
            searchEl.focus();
            filterOptions('');
        }
    });

    // Close popover on outside click
    document.addEventListener('click', function (e) {
        var card = document.getElementById('tags-card');
        if (card && !card.contains(e.target)) {
            popover.classList.add('hidden');
        }
    });

    // Search filter
    searchEl && searchEl.addEventListener('input', function () {
        filterOptions(this.value.trim());
    });

    function filterOptions(term) {
        var opts = available.querySelectorAll('.tag-option');
        var lc   = term.toLowerCase();
        var anyVisible = false;
        opts.forEach(function (btn) {
            var nome = btn.dataset.nombre.toLowerCase();
            var show = !term || nome.includes(lc);
            btn.style.display = show ? '' : 'none';
            if (show) anyVisible = true;
        });
        // Show create row when term is non-empty
        if (term.length > 0) {
            createRow.classList.remove('hidden');
            createRow.classList.add('flex');
        } else {
            createRow.classList.add('hidden');
            createRow.classList.remove('flex');
        }
    }

    // Assign tag
    available && available.addEventListener('click', function (e) {
        var btn = e.target.closest('.tag-option');
        if (!btn) return;
        var id = parseInt(btn.dataset.id);
        if (assigned.has(id)) { return; }
        apiPost(BASE + '/api/clientes/' + CLIENTE_ID + '/etiquetas/' + id + '/asignar', {}, function (d) {
            if (!d.ok) { window.showToast && window.showToast(d.error || 'Error.', 'error'); return; }
            assigned.add(id);
            addChip(id, btn.dataset.nombre, btn.style.color,
                btn.style.background, btn.style.borderColor);
            popover.classList.add('hidden');
        });
    });

    // Remove tag (delegated)
    tagsList && tagsList.addEventListener('click', function (e) {
        var btn = e.target.closest('.tag-remove');
        if (!btn) return;
        var id  = parseInt(btn.dataset.id);
        apiPost(BASE + '/api/clientes/' + CLIENTE_ID + '/etiquetas/' + id + '/quitar', {}, function (d) {
            if (!d.ok) { window.showToast && window.showToast(d.error || 'Error.', 'error'); return; }
            assigned.delete(id);
            var chip = tagsList.querySelector('.tag-chip[data-id="' + id + '"]');
            if (chip) chip.remove();
            updateEmpty();
        });
    });

    // Create new tag
    createBtn && createBtn.addEventListener('click', function () {
        var nombre = searchEl.value.trim();
        if (!nombre) return;
        apiPost(BASE + '/api/etiquetas', { nombre: nombre, color: selectedColor }, function (d) {
            if (!d.ok) { window.showToast && window.showToast(d.error || 'Error al crear.', 'error'); return; }
            var tag = d.etiqueta;
            // Add to available list
            var newBtn = document.createElement('button');
            newBtn.className = 'tag-option inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border transition-all hover:opacity-80';
            newBtn.dataset.id     = tag.id;
            newBtn.dataset.nombre = tag.nombre;
            newBtn.style.color        = tag.color;
            newBtn.style.background   = tag.color + '1a';
            newBtn.style.borderColor  = tag.color + '40';
            newBtn.textContent = tag.nombre;
            available.appendChild(newBtn);

            // Immediately assign
            apiPost(BASE + '/api/clientes/' + CLIENTE_ID + '/etiquetas/' + tag.id + '/asignar', {}, function (d2) {
                if (!d2.ok) return;
                assigned.add(parseInt(tag.id));
                addChip(tag.id, tag.nombre, tag.color, tag.color + '1a', tag.color + '40');
            });

            searchEl.value = '';
            createRow.classList.add('hidden');
            popover.classList.add('hidden');
        });
    });

    function addChip(id, nombre, color, bg, borderColor) {
        if (emptyEl) { emptyEl.remove(); emptyEl = null; }
        var span = document.createElement('span');
        span.className = 'tag-chip inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium border cursor-default group/chip';
        span.dataset.id = id;
        span.style.color       = color;
        span.style.background  = bg;
        span.style.borderColor = borderColor;
        span.innerHTML =
            nombre +
            '<button class="tag-remove opacity-0 group-hover/chip:opacity-100 transition-opacity ml-0.5 -mr-0.5 hover:scale-110" data-id="' + id + '" title="Quitar etiqueta">' +
            '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>' +
            '</button>';
        tagsList.appendChild(span);
    }

    function updateEmpty() {
        var chips = tagsList.querySelectorAll('.tag-chip');
        if (chips.length === 0 && !emptyEl) {
            emptyEl = document.createElement('span');
            emptyEl.id = 'tags-empty';
            emptyEl.className = 'text-gray-700 text-xs';
            emptyEl.textContent = <?= json_encode(__('tag.none')) ?>;
            tagsList.appendChild(emptyEl);
        }
    }

    function apiPost(url, data, cb) {
        var fd = new FormData();
        fd.append('_csrf', CSRF);
        Object.keys(data).forEach(function (k) { fd.append(k, data[k]); });
        fetch(url, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(cb)
            .catch(function () { window.showToast && window.showToast('Error de red.', 'error'); });
    }
})();

// ── Feature 2A: Avatar upload ──────────────────────────────────────────────────
(function () {
    var trigger = document.getElementById('avatar-trigger');
    var input   = document.getElementById('avatar-input');
    var spinner = document.getElementById('avatar-spinner');
    if (!trigger || !input) return;

    var CSRF     = <?= json_encode($csrf) ?>;
    var ENDPOINT = <?= json_encode(BASE_URL . '/api/clientes/' . (int)$cliente['id'] . '/avatar') ?>;
    var hadPhoto = <?= json_encode(!empty($cliente['foto_perfil'])) ?>;
    var tempImg  = false; // we created the <img> (no previous photo existed)

    trigger.addEventListener('click', function () { input.click(); });

    input.addEventListener('change', function () {
        var file = input.files[0];
        if (!file) return;
        input.value = ''; // allow re-selecting same file

        // Snapshot current state for revert
        var prevEl  = document.getElementById('avatar-preview');
        var prevSrc = prevEl ? prevEl.src : null;

        // Instant local preview
        var blobUrl = URL.createObjectURL(file);
        applyPreview(blobUrl, prevEl);
        spinnerShow(true);

        // Upload
        var fd = new FormData();
        fd.append('avatar', file);
        fd.append('_csrf', CSRF);

        fetch(ENDPOINT, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                spinnerShow(false);
                URL.revokeObjectURL(blobUrl);
                if (d.ok) {
                    // Replace blob with permanent server URL
                    var img = document.getElementById('avatar-preview');
                    if (img) img.src = d.url;
                    hadPhoto = true;
                    tempImg  = false;
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
    });

    function applyPreview(url, prevEl) {
        if (prevEl) {
            prevEl.src = url;
        } else {
            // No previous photo: create <img>, hide initials
            var img = document.createElement('img');
            img.id        = 'avatar-preview';
            img.className = 'w-11 h-11 rounded-xl object-cover border border-red-500/25';
            img.alt       = '';
            img.src       = url;
            trigger.insertBefore(img, trigger.firstChild);
            var ini = document.getElementById('avatar-initials');
            if (ini) ini.style.display = 'none';
            tempImg = true;
        }
    }

    function revertPreview(prevSrc) {
        if (tempImg && !hadPhoto) {
            // Remove the temp <img> we created, show initials again
            var img = document.getElementById('avatar-preview');
            if (img) img.remove();
            tempImg = false;
            var ini = document.getElementById('avatar-initials');
            if (ini) ini.style.display = '';
        } else if (prevSrc) {
            var img = document.getElementById('avatar-preview');
            if (img) img.src = prevSrc;
        }
    }

    function spinnerShow(show) {
        if (!spinner) return;
        if (show) { spinner.classList.remove('hidden'); spinner.style.display = 'flex'; }
        else       { spinner.classList.add('hidden');   spinner.style.display = ''; }
    }
})();
</script>
