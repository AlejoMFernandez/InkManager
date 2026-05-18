<?php
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
foreach ($flash as $type => $msg):
    $colors = match($type) {
        'success' => 'bg-green-500/10 border-green-500/30 text-green-400',
        'error'   => 'bg-red-500/10 border-red-500/30 text-red-400',
        'warning' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
        default   => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
    };
    $icon = match($type) {
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
        'error'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>',
        'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
        default   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    };
?>
<div class="flash-enter flex items-center gap-3 px-4 py-3 mb-4 rounded-lg border text-sm <?= $colors ?>"
     role="alert">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <?= $icon ?>
    </svg>
    <span><?= htmlspecialchars($msg) ?></span>
    <button onclick="this.closest('[role=alert]').remove()"
            class="ml-auto text-current opacity-50 hover:opacity-100 transition-opacity"
            aria-label="Cerrar">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<?php endforeach; ?>
