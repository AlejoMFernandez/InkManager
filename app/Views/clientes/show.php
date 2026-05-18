<?php
$pageTitle = htmlspecialchars($cliente['nombre']);
use App\Core\Auth;
$csrf = Auth::csrfToken();

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
        from { opacity:0; transform:translateY(-10px) scale(.96); }
        to   { opacity:1; transform:translateY(0)     scale(1);   }
    }
    .modal-enter { animation: modalIn 0.22s cubic-bezier(.16,1,.3,1) both; }
</style>
<?php
$extraHead = ob_get_clean();

ob_start();
?>
<script>
window.BODYMAP_CONFIG = {
    modelUrl: <?= file_exists(PUBLIC_PATH . '/assets/models/body.glb')
        ? json_encode(PUBLIC_URL . '/assets/models/body.glb')
        : 'null' ?>,
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
        <p class="brand-tagline mb-1">// Client File</p>
        <div class="flex items-center gap-2 text-sm">
            <a href="<?= BASE_URL ?>/clientes" class="text-gray-500 hover:text-white link-underline">Clientes</a>
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
            Nuevo turno
        </a>
        <a href="<?= BASE_URL ?>/clientes/<?= $cliente['id'] ?>/editar"
           class="inline-flex items-center gap-1.5 px-3 py-1.5
                  bg-gray-800 border border-gray-700 hover:border-gray-600
                  text-gray-300 hover:text-white text-sm rounded-lg transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
            </svg>
            Editar
        </a>
    </div>
</div>

<!-- ── Layout principal ───────────────────────────────────────────────────── -->
<div class="grid grid-cols-1 xl:grid-cols-[280px_1fr] gap-4">

    <!-- ── Sidebar izquierdo ─────────────────────────────────────────────── -->
    <div class="space-y-4">

        <!-- Tarjeta de datos -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl
                            bg-gradient-to-br from-red-600/40 to-red-900/60
                            border border-red-500/25 shadow-inner
                            flex items-center justify-center text-red-300 text-base font-bold flex-shrink-0">
                    <?= strtoupper(mb_substr($cliente['nombre'], 0, 1)) ?>
                </div>
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
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5">Teléfono</dt>
                    <dd class="text-gray-300 text-xs"><?= htmlspecialchars($cliente['telefono']) ?></dd>
                </div>
                <?php endif; ?>
                <div class="flex gap-2">
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5">1ª visita</dt>
                    <dd class="text-gray-300 text-xs">
                        <?= $cliente['primera_visita']
                            ? date('d/m/Y', strtotime($cliente['primera_visita']))
                            : '<span class="text-gray-700">—</span>' ?>
                    </dd>
                </div>
                <div class="flex gap-2">
                    <dt class="text-gray-600 w-24 flex-shrink-0 text-xs pt-0.5">Tatuajes</dt>
                    <dd class="text-white font-semibold text-sm"><?= count($cliente['tatuajes']) ?></dd>
                </div>
            </dl>

            <?php if ($cliente['notas']): ?>
            <div class="mt-3 pt-3 border-t border-gray-800">
                <p class="text-xs text-gray-600 mb-1">Notas</p>
                <p class="text-gray-400 text-xs leading-relaxed whitespace-pre-line">
                    <?= htmlspecialchars($cliente['notas']) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Próximos turnos -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 card-in" style="animation-delay:60ms">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Próximos turnos</h3>
                <a href="<?= BASE_URL ?>/turnos/nuevo?cliente_id=<?= $cliente['id'] ?>"
                   class="text-xs text-red-400 hover:text-red-300">+ Agendar</a>
            </div>
            <?php if (empty($turnos)): ?>
            <p class="text-gray-700 text-xs py-3 text-center">Sin turnos próximos</p>
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

        <!-- Tatuajes list -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-in" style="animation-delay:120ms">
            <div class="px-5 py-3 border-b border-gray-800 flex items-center justify-between">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tatuajes</h3>
                <span id="tatuajes-count" class="text-xs text-gray-600"><?= count($cliente['tatuajes']) ?></span>
            </div>
            <div id="sidebar-tatuajes-list" class="divide-y divide-gray-800">
                <?php if (empty($cliente['tatuajes'])): ?>
                <p class="text-gray-700 text-xs py-5 text-center">
                    Hacé click en el cuerpo 3D para agregar
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
                            <?= htmlspecialchars($tat['estilo_nombre'] ?? 'Sin estilo') ?>
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

            <!-- Loader overlay -->
            <div id="bodymap-loader"
                 class="absolute inset-0 flex flex-col items-center justify-center
                        bg-gray-950/80 backdrop-blur-sm"
                 style="display:none">
                <div class="w-8 h-8 rounded-full border-2 border-gray-700
                            border-t-red-500 animate-spin mb-3"></div>
                <p class="text-gray-500 text-xs">Cargando modelo…</p>
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
                    <span class="text-gray-400 text-xs">Arrastrá para rotar · Scroll para zoom</span>
                </div>
            </div>

            <!-- Badge: modelo activo -->
            <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1
                        bg-black/60 backdrop-blur-sm rounded-full border border-gray-800">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-gray-400 text-xs" id="bodymap-status">Body Map 3D</span>
            </div>

        </div>

        <!-- Info bar debajo del canvas -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl px-5 py-3
                    flex flex-wrap items-center gap-4 text-xs text-gray-500">
            <span>
                <span class="text-white font-semibold"><?= count($cliente['tatuajes']) ?></span>
                <?= count($cliente['tatuajes']) === 1 ? 'tatuaje' : 'tatuajes' ?>
            </span>
            <span class="text-gray-700">|</span>
            <?php
            $conPos = count(array_filter($cliente['tatuajes'], fn($t) => $t['pos_x'] !== null));
            ?>
            <span>
                <span class="text-white font-semibold"><?= $conPos ?></span>
                marcados en el body map
            </span>
            <span class="text-gray-700">|</span>
            <?php
            $ingTotal = array_sum(array_column($cliente['tatuajes'], 'precio'));
            ?>
            <span>
                Total:
                <span class="text-white font-semibold">
                    $<?= number_format((float) $ingTotal, 0, ',', '.') ?>
                </span>
            </span>
            <span id="bodymap-marked-count" class="hidden"><?= $conPos ?></span>
            <span class="ml-auto text-gray-600 text-xs hidden sm:block">
                Click en el cuerpo → agregar · Click en marker rojo → ver detalle
            </span>
        </div>

    </div>
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
</script>
