<?php
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
if (empty($flash)) return;
?>
<script>
// Esperar a que showToast esté definido (se carga al final de body)
document.addEventListener('DOMContentLoaded', function () {
    <?php foreach ($flash as $type => $msg): ?>
    window.showToast && window.showToast(<?= json_encode(htmlspecialchars($msg, ENT_QUOTES)) ?>, <?= json_encode($type) ?>);
    <?php endforeach; ?>
});
</script>
