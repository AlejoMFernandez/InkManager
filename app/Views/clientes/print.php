<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha — <?= htmlspecialchars($cliente['nombre']) ?> · <?= htmlspecialchars($studioNombre) ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #111827;
            background: #fff;
            padding: 32px;
            max-width: 820px;
            margin: 0 auto;
        }

        /* ── Action bar (pantalla) ─────────────────────────────────── */
        .action-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .btn-print {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 20px;
            background: #111827; color: #fff;
            border: none; border-radius: 7px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            letter-spacing: 0.04em;
            transition: background .15s;
        }
        .btn-print:hover { background: #1f2937; }
        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            background: transparent; color: #6b7280;
            border: 1px solid #e5e7eb; border-radius: 7px;
            font-size: 12px; text-decoration: none;
            transition: color .15s, border-color .15s;
        }
        .btn-back:hover { color: #374151; border-color: #9ca3af; }

        /* ── Header del documento ─────────────────────────────────── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 14px;
            margin-bottom: 20px;
            border-bottom: 2.5px solid #111827;
        }
        .doc-header h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .doc-header .studio-name {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-top: 5px;
        }
        .doc-header .doc-meta {
            text-align: right;
            font-size: 10px;
            color: #9ca3af;
            line-height: 1.8;
        }

        /* ── Secciones ────────────────────────────────────────────── */
        .section { margin-bottom: 22px; }
        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #9ca3af;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        /* ── Grid de datos del cliente ────────────────────────────── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px 16px;
        }
        .info-item label {
            display: block;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .info-item .val {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }
        .info-item .val-light {
            font-size: 12px;
            font-weight: 400;
            color: #374151;
        }
        .notes-block {
            margin-top: 12px;
            padding: 10px 14px;
            background: #f9fafb;
            border-left: 3px solid #d1d5db;
            border-radius: 0 6px 6px 0;
        }
        .notes-block label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .notes-block p {
            font-size: 12px;
            color: #374151;
            white-space: pre-wrap;
        }

        /* ── Tabla de tatuajes ────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table thead th {
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 10px 6px 0;
        }
        table tbody td {
            padding: 7px 10px 7px 0;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: top;
        }
        table tbody tr:last-child td { border-bottom: none; }

        /* ── Timeline de turnos ───────────────────────────────────── */
        .timeline { display: flex; flex-direction: column; gap: 1px; }
        .apt-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 7px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .apt-row:last-child { border-bottom: none; }
        .apt-date {
            flex-shrink: 0;
            min-width: 96px;
            font-size: 11px;
            color: #6b7280;
            padding-top: 1px;
        }
        .apt-badge {
            flex-shrink: 0;
            display: inline-block;
            min-width: 74px;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: center;
        }
        .badge-hecho      { background: #dcfce7; color: #15803d; }
        .badge-confirmado { background: #dbeafe; color: #1d4ed8; }
        .badge-agendado   { background: #fef9c3; color: #a16207; }
        .badge-cancelado  { background: #fee2e2; color: #b91c1c; }
        .apt-detail { flex: 1; font-size: 12px; color: #374151; }
        .apt-detail .detail-line { display: flex; gap: 6px; flex-wrap: wrap; }
        .apt-detail .apt-notes {
            margin-top: 2px;
            font-size: 11px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ── Stats row ───────────────────────────────────────────── */
        .stats-row {
            display: flex;
            gap: 16px;
            margin-bottom: 14px;
        }
        .stat-chip {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            min-width: 80px;
        }
        .stat-chip .stat-num {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            line-height: 1;
        }
        .stat-chip .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
            margin-top: 3px;
        }

        /* ── Footer ──────────────────────────────────────────────── */
        .doc-footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #c9d0d8;
        }

        /* ── Print styles ────────────────────────────────────────── */
        @media print {
            body { padding: 16px; font-size: 11px; }
            .action-bar { display: none; }
            .doc-header h1 { font-size: 20px; }
            .stat-chip .stat-num { font-size: 16px; }
            @page { margin: 12mm 14mm; }
        }
    </style>
</head>
<body>

<!-- ── Barra de acciones (solo en pantalla) ─────────────────────────── -->
<div class="action-bar">
    <button class="btn-print" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 14h12v8H6z"/>
        </svg>
        Imprimir
    </button>
    <a href="<?= BASE_URL ?>/clientes/<?= (int) $cliente['id'] ?>" class="btn-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Volver a la ficha
    </a>
</div>

<!-- ── Encabezado del documento ─────────────────────────────────────── -->
<div class="doc-header">
    <div>
        <h1><?= htmlspecialchars($cliente['nombre']) ?></h1>
        <p class="studio-name"><?= htmlspecialchars($studioNombre) ?> · Ficha de cliente</p>
    </div>
    <div class="doc-meta">
        <div>Generado: <?= date('d/m/Y H:i') ?></div>
        <div>ID cliente: #<?= (int) $cliente['id'] ?></div>
    </div>
</div>

<?php
// Stats rápidas
$totalTats    = count($cliente['tatuajes']);
$totalTurnos  = count($historial);
$sesionesHechas = array_sum(array_column($cliente['tatuajes'], 'sesiones_hechas'));
$sesionesTotales = array_sum(array_column($cliente['tatuajes'], 'sesiones_totales'));
?>

<!-- ── Stats rápidas ─────────────────────────────────────────────────── -->
<div class="stats-row">
    <div class="stat-chip">
        <span class="stat-num"><?= $totalTats ?></span>
        <span class="stat-label">Tatuajes</span>
    </div>
    <div class="stat-chip">
        <span class="stat-num"><?= $totalTurnos ?></span>
        <span class="stat-label">Turnos</span>
    </div>
    <div class="stat-chip">
        <span class="stat-num"><?= $sesionesHechas ?>/<?= $sesionesTotales ?: $totalTats ?></span>
        <span class="stat-label">Sesiones</span>
    </div>
</div>

<!-- ── Datos personales ──────────────────────────────────────────────── -->
<div class="section">
    <p class="section-title">// Datos del cliente</p>
    <div class="info-grid">
        <div class="info-item">
            <label>Teléfono</label>
            <span class="val"><?= htmlspecialchars($cliente['telefono'] ?? '—') ?></span>
        </div>
        <div class="info-item">
            <label>Instagram</label>
            <span class="val"><?= $cliente['instagram']
                ? htmlspecialchars('@' . ltrim($cliente['instagram'], '@'))
                : '—' ?></span>
        </div>
        <div class="info-item">
            <label>Primera visita</label>
            <span class="val"><?= $cliente['primera_visita']
                ? date('d/m/Y', strtotime($cliente['primera_visita']))
                : '—' ?></span>
        </div>
        <div class="info-item">
            <label>Body map</label>
            <span class="val-light"><?= ucfirst(htmlspecialchars($cliente['genero'] ?? '—')) ?></span>
        </div>
        <div class="info-item">
            <label>Registrado en</label>
            <span class="val-light"><?= isset($cliente['created_at'])
                ? date('d/m/Y', strtotime($cliente['created_at']))
                : '—' ?></span>
        </div>
    </div>

    <?php if (!empty($cliente['notas'])): ?>
    <div class="notes-block">
        <label>Notas</label>
        <p><?= nl2br(htmlspecialchars($cliente['notas'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- ── Tatuajes ──────────────────────────────────────────────────────── -->
<?php if (!empty($cliente['tatuajes'])): ?>
<div class="section">
    <p class="section-title">// Tatuajes (<?= $totalTats ?>)</p>
    <table>
        <thead>
            <tr>
                <th>Estilo</th>
                <th>Fecha</th>
                <th>Precio</th>
                <th>Sesiones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cliente['tatuajes'] as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['estilo_nombre'] ?? '—') ?></td>
                <td><?= ($t['fecha'] ?? null)
                    ? date('d/m/Y', strtotime($t['fecha']))
                    : '—' ?></td>
                <td><?= ($t['precio'] !== null)
                    ? '$ ' . number_format((float) $t['precio'], 0, ',', '.')
                    : '—' ?></td>
                <td><?= (int) ($t['sesiones_hechas'] ?? 0) ?> / <?= (int) ($t['sesiones_totales'] ?? 1) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- ── Historial de turnos ───────────────────────────────────────────── -->
<?php if (!empty($historial)): ?>
<div class="section">
    <p class="section-title">// Historial de turnos (<?= count($historial) ?>)</p>
    <div class="timeline">
    <?php foreach ($historial as $h):
        $badgeClass = 'badge-' . htmlspecialchars($h['estado'] ?? 'agendado');
    ?>
        <div class="apt-row">
            <span class="apt-date">
                <?= ($h['fecha_inicio'] ?? null)
                    ? date('d/m/Y H:i', strtotime($h['fecha_inicio']))
                    : '—' ?>
            </span>
            <span class="apt-badge <?= $badgeClass ?>">
                <?= htmlspecialchars(ucfirst($h['estado'] ?? '')) ?>
            </span>
            <div class="apt-detail">
                <div class="detail-line">
                    <?php if ($h['estilo_nombre'] ?? null): ?>
                    <strong><?= htmlspecialchars($h['estilo_nombre']) ?></strong>
                    <span>·</span>
                    <?php endif; ?>
                    <span><?= (int) ($h['duracion_min'] ?? 0) ?> min</span>
                    <?php if (($h['sena'] ?? null) !== null): ?>
                    <span>·</span>
                    <span>Seña: $<?= number_format((float) $h['sena'], 0, ',', '.') ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($h['notas'])): ?>
                <p class="apt-notes"><?= htmlspecialchars($h['notas']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (empty($cliente['tatuajes']) && empty($historial)): ?>
<p style="color:#9ca3af; font-size:12px; text-align:center; padding:24px 0;">
    Este cliente aún no tiene tatuajes ni turnos registrados.
</p>
<?php endif; ?>

<!-- ── Footer ───────────────────────────────────────────────────────── -->
<div class="doc-footer">
    <span>InkManager Studio System</span>
    <span><?= htmlspecialchars($studioNombre) ?> · Ficha #<?= (int) $cliente['id'] ?></span>
</div>

</body>
</html>
