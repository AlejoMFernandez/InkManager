<?php
/** @var array  $fotos   Fotos con cliente_nombre */
/** @var string $estilo  Filtro activo ('' = todos) */
/** @var string $csrf    CSRF token */

use App\Models\Galeria;

$estilos    = Galeria::ESTILOS;
$imgBaseUrl = PUBLIC_URL . '/assets/uploads/gallery/';

// Etiquetas "bonitas" para mostrar en badges
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

<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Galería</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                <?= count($fotos) ?> foto<?= count($fotos) !== 1 ? 's' : '' ?>
                <?= $estilo !== '' ? '· filtrado por <span class="text-gray-300">' . htmlspecialchars($estiloLabel[$estilo] ?? $estilo) . '</span>' : '' ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/galeria/subir"
           class="flex items-center gap-2 px-4 py-2
                  bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                  text-white text-sm font-semibold rounded-lg transition-all active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Subir foto
        </a>
    </div>

    <!-- Filtros por estilo -->
    <div class="flex flex-wrap gap-2">
        <a href="<?= BASE_URL ?>/galeria"
           class="px-3 py-1.5 rounded-full border text-xs font-medium transition-colors
                  <?= $estilo === ''
                      ? 'bg-red-600/20 border-red-600/40 text-red-300'
                      : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white hover:border-gray-600' ?>">
            Todos
        </a>
        <?php foreach ($estilos as $e): ?>
        <a href="<?= BASE_URL ?>/galeria?estilo=<?= urlencode($e) ?>"
           class="px-3 py-1.5 rounded-full border text-xs font-medium transition-colors
                  <?= $estilo === $e
                      ? 'bg-red-600/20 border-red-600/40 text-red-300'
                      : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white hover:border-gray-600' ?>">
            <?= htmlspecialchars($estiloLabel[$e] ?? $e) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($fotos)): ?>
    <!-- Empty state -->
    <div class="py-20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-800 border border-gray-700
                    flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5
                         1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5
                         0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25
                         6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375
                         0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
        </div>
        <p class="text-gray-400 font-medium mb-1">
            <?= $estilo !== '' ? 'No hay fotos con ese estilo' : 'La galería está vacía' ?>
        </p>
        <p class="text-gray-600 text-sm mb-5">
            <?= $estilo !== '' ? 'Probá con otro filtro o subí una nueva foto.' : 'Empezá subiendo el primer trabajo.' ?>
        </p>
        <?php if ($estilo !== ''): ?>
        <a href="<?= BASE_URL ?>/galeria"
           class="text-sm text-red-400 hover:text-red-300 transition-colors">Ver todas las fotos</a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>/galeria/subir"
           class="px-5 py-2.5 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold rounded-lg transition-colors">
            Subir primera foto
        </a>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- Grid de fotos -->
    <div id="gallery-grid"
         class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        <?php foreach ($fotos as $i => $foto): ?>
        <?php $src = $imgBaseUrl . htmlspecialchars($foto['archivo']); ?>
        <div class="group relative aspect-square rounded-xl overflow-hidden
                    bg-gray-800 border border-gray-700/50 cursor-pointer
                    hover:border-gray-600 transition-all hover:scale-[1.02]"
             onclick="openLightbox(<?= $i ?>)"
             data-index="<?= $i ?>">
            <!-- Imagen -->
            <img src="<?= $src ?>"
                 alt="<?= htmlspecialchars($foto['titulo'] ?? 'Tatuaje') ?>"
                 loading="lazy"
                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">

            <!-- Overlay info -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent
                        opacity-0 group-hover:opacity-100 transition-opacity duration-200
                        flex flex-col justify-end p-2.5">
                <?php if ($foto['titulo']): ?>
                <p class="text-white text-xs font-medium leading-tight truncate">
                    <?= htmlspecialchars($foto['titulo']) ?>
                </p>
                <?php endif; ?>
                <div class="flex items-center justify-between mt-1 gap-1">
                    <?php if ($foto['estilo']): ?>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-white/15 text-white/80">
                        <?= htmlspecialchars($estiloLabel[$foto['estilo']] ?? $foto['estilo']) ?>
                    </span>
                    <?php else: ?>
                    <span></span>
                    <?php endif; ?>
                    <!-- Botón borrar (solo en hover) -->
                    <form method="POST" action="<?= BASE_URL ?>/galeria/<?= $foto['id'] ?>/borrar"
                          onsubmit="return confirm('¿Eliminar esta foto?')"
                          onclick="event.stopPropagation()">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit"
                                class="p-1 rounded bg-red-600/80 hover:bg-red-600
                                       text-white transition-colors"
                                title="Eliminar foto">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- ── Lightbox ──────────────────────────────────────────────────────────── -->
<?php if (!empty($fotos)): ?>
<div id="lb-backdrop"
     class="fixed inset-0 z-[300] bg-black/95 flex items-center justify-center hidden"
     onclick="closeLightbox()">

    <!-- Panel central -->
    <div class="relative flex flex-col items-center max-w-5xl w-full px-4"
         onclick="event.stopPropagation()">

        <!-- Imagen -->
        <img id="lb-img" src="" alt=""
             class="max-h-[80vh] max-w-full rounded-xl object-contain shadow-2xl">

        <!-- Meta debajo de la imagen -->
        <div id="lb-meta" class="mt-3 text-center">
            <p id="lb-title" class="text-white text-sm font-medium"></p>
            <p id="lb-sub"   class="text-gray-500 text-xs mt-0.5"></p>
        </div>

        <!-- Contador -->
        <p id="lb-counter" class="absolute top-3 right-0 text-gray-600 text-xs tabular-nums"></p>

        <!-- Flecha anterior -->
        <button id="lb-prev"
                onclick="moveLightbox(-1)"
                class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full
                       bg-gray-800/80 hover:bg-gray-700 text-gray-400 hover:text-white
                       flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
        </button>

        <!-- Flecha siguiente -->
        <button id="lb-next"
                onclick="moveLightbox(1)"
                class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full
                       bg-gray-800/80 hover:bg-gray-700 text-gray-400 hover:text-white
                       flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>
        </button>

        <!-- Cerrar -->
        <button onclick="closeLightbox()"
                class="absolute -top-10 right-0 w-8 h-8 rounded-full bg-gray-800/80
                       hover:bg-gray-700 text-gray-400 hover:text-white
                       flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>

<?php
// Serializar datos de las fotos para el lightbox JS
$lbData = array_map(function ($f) use ($imgBaseUrl, $estiloLabel) {
    return [
        'src'     => $imgBaseUrl . $f['archivo'],
        'title'   => $f['titulo'] ?? '',
        'cliente' => $f['cliente_nombre'] ?? '',
        'estilo'  => isset($f['estilo']) ? ($estiloLabel[$f['estilo']] ?? $f['estilo']) : '',
        'fecha'   => $f['created_at'] ? date('d/m/Y', strtotime($f['created_at'])) : '',
    ];
}, $fotos);
?>
<script>
(function () {
    var photos  = <?= json_encode($lbData, JSON_UNESCAPED_UNICODE) ?>;
    var current = 0;
    var bd      = document.getElementById('lb-backdrop');
    var img     = document.getElementById('lb-img');
    var title   = document.getElementById('lb-title');
    var sub     = document.getElementById('lb-sub');
    var counter = document.getElementById('lb-counter');

    function render(idx) {
        var p = photos[idx];
        img.src     = p.src;
        img.alt     = p.title || 'Tatuaje';
        title.textContent = p.title;
        title.style.display = p.title ? '' : 'none';

        var parts = [];
        if (p.cliente) parts.push(p.cliente);
        if (p.estilo)  parts.push(p.estilo);
        if (p.fecha)   parts.push(p.fecha);
        sub.textContent     = parts.join(' · ');
        sub.style.display   = parts.length ? '' : 'none';
        counter.textContent = (idx + 1) + ' / ' + photos.length;

        document.getElementById('lb-prev').style.display = photos.length > 1 ? '' : 'none';
        document.getElementById('lb-next').style.display = photos.length > 1 ? '' : 'none';
    }

    window.openLightbox = function (idx) {
        current = idx;
        render(current);
        bd.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    window.closeLightbox = function () {
        bd.classList.add('hidden');
        document.body.style.overflow = '';
        img.src = '';
    };
    window.moveLightbox = function (dir) {
        current = (current + dir + photos.length) % photos.length;
        render(current);
    };

    // Teclado: ← → ESC
    document.addEventListener('keydown', function (e) {
        if (bd.classList.contains('hidden')) return;
        if (e.key === 'ArrowLeft')  moveLightbox(-1);
        if (e.key === 'ArrowRight') moveLightbox(1);
        if (e.key === 'Escape')     closeLightbox();
    });
})();
</script>
<?php endif; ?>
