<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($presupuesto['numero']) ?> — <?= htmlspecialchars($studio['nombre'] ?? 'Presupuesto') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --accent:   #dc2626;
            --accent-2: #991b1b;
            --dark:     #0f172a;
            --gray-800: #1e293b;
            --gray-700: #334155;
            --gray-500: #64748b;
            --gray-400: #94a3b8;
            --gray-200: #e2e8f0;
            --gray-100: #f1f5f9;
            --white:    #ffffff;
        }

        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            font-size: 12.5px;
            line-height: 1.6;
            color: var(--dark);
            background: var(--white);
        }

        /* ── Page wrapper ───────────────────────────────────────────────── */
        .page {
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
            position: relative;
        }

        /* ── Accent bar ─────────────────────────────────────────────────── */
        .accent-bar {
            height: 5px;
            background: linear-gradient(to right, var(--accent), #f87171);
        }

        /* ── Main content padding ───────────────────────────────────────── */
        .content { padding: 40px 44px 36px; }

        /* ── Header ─────────────────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 28px;
        }
        .brand {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .brand-dot {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .brand-dot svg { display: block; }
        .brand-name {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.4px;
            color: var(--dark);
            line-height: 1.2;
        }
        .brand-studio {
            font-size: 11.5px;
            color: var(--gray-500);
            margin-top: 2px;
        }
        .brand-studio strong { color: var(--dark); font-weight: 600; }
        .studio-info {
            text-align: right;
            font-size: 11px;
            color: var(--gray-500);
            line-height: 1.85;
        }
        .studio-info strong { color: var(--dark); font-size: 12px; font-weight: 600; }

        /* ── Doc title row ───────────────────────────────────────────────── */
        .doc-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }
        .doc-title {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1.2px;
            color: var(--dark);
            line-height: 1.15;
        }
        .doc-subtitle {
            color: var(--gray-500);
            font-size: 12.5px;
            margin-top: 5px;
        }
        .doc-status-wrap { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
        .status-badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .status-borrador  { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
        .status-enviado   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .status-aceptado  { background: #f0fdf4; color: #15803d; border: 1px solid #86efac; }
        .status-rechazado { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5; }

        .numero-block { text-align: right; flex-shrink: 0; }
        .numero-label {
            font-size: 9px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--gray-400);
            margin-bottom: 3px;
        }
        .numero-value {
            font-family: 'Courier Prime', 'Courier New', monospace;
            font-size: 16px;
            font-weight: 700;
            color: var(--accent);
        }

        /* ── Meta strip ──────────────────────────────────────────────────── */
        .meta-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .meta-cell {
            padding: 11px 15px;
            border-right: 1px solid var(--gray-200);
        }
        .meta-cell:last-child { border-right: none; }
        .meta-label {
            font-size: 8.5px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--gray-400);
            margin-bottom: 3px;
        }
        .meta-value {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--dark);
        }
        .meta-value.mono { font-family: 'Courier Prime', monospace; }

        /* ── Parties ─────────────────────────────────────────────────────── */
        .parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 24px;
        }
        .party-box {
            padding: 13px 15px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: var(--gray-100);
        }
        .party-role {
            font-size: 8.5px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--gray-400);
            margin-bottom: 4px;
        }
        .party-name {
            font-size: 14.5px;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.3;
        }
        .party-detail {
            font-size: 11px;
            color: var(--gray-500);
            margin-top: 2px;
        }

        /* ── Section ─────────────────────────────────────────────────────── */
        .section { margin-bottom: 22px; }
        .section-label {
            font-size: 9px;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--gray-400);
            padding-bottom: 7px;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 12px;
        }
        .text-box {
            padding: 14px 16px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            color: var(--gray-700);
            white-space: pre-wrap;
            line-height: 1.75;
            font-size: 12.5px;
        }

        /* ── Bottom section: price + QR ─────────────────────────────────── */
        .bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 24px;
        }
        .price-block { flex-shrink: 0; }
        .price-table {
            width: 230px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            overflow: hidden;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 14px;
            border-bottom: 1px solid var(--gray-200);
            font-size: 12px;
        }
        .price-row:last-child { border-bottom: none; }
        .price-row.total {
            background: var(--dark);
            color: var(--white);
        }
        .price-row .p-label { color: var(--gray-500); }
        .price-row.total .p-label { color: var(--gray-400); }
        .p-amount { font-weight: 700; font-family: 'Courier Prime', monospace; }
        .price-na {
            padding: 14px 16px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            font-size: 11.5px;
            color: var(--gray-500);
            width: 230px;
        }
        .price-na strong { color: var(--dark); }

        /* ── QR code ─────────────────────────────────────────────────────── */
        .qr-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }
        .qr-wrap img {
            display: block;
            width: 76px;
            height: 76px;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            padding: 3px;
            background: white;
        }
        .qr-label {
            font-size: 9px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--gray-400);
            text-align: center;
        }

        /* ── Validity notice ─────────────────────────────────────────────── */
        .notice {
            padding: 11px 15px;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-left: 3px solid var(--accent);
            border-radius: 0 8px 8px 0;
            font-size: 11px;
            color: var(--gray-500);
            margin-bottom: 28px;
            line-height: 1.7;
        }
        .notice strong { color: var(--dark); }

        /* ── Signatures ──────────────────────────────────────────────────── */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 36px;
        }
        .sig-area {
            height: 52px;
            border-bottom: 1px solid var(--dark);
            margin-bottom: 6px;
        }
        .sig-label { font-size: 10px; color: var(--gray-500); }
        .sig-name  { font-size: 11.5px; font-weight: 600; color: var(--dark); margin-top: 2px; }

        /* ── Footer ──────────────────────────────────────────────────────── */
        .print-footer {
            border-top: 1px solid var(--gray-200);
            padding-top: 14px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: var(--gray-400);
        }

        /* ── Status watermark ─────────────────────────────────────────────── */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 92px;
            font-weight: 900;
            letter-spacing: -2px;
            opacity: 0.045;
            pointer-events: none;
            text-transform: uppercase;
            white-space: nowrap;
            z-index: 0;
            user-select: none;
        }
        .watermark.aceptado  { color: #16a34a; }
        .watermark.rechazado { color: #dc2626; }

        /* ── Floating controls (hidden on print) ──────────────────────────── */
        .controls {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            gap: 8px;
            z-index: 100;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 150ms;
        }
        .btn:hover { opacity: .85; }
        .btn-back  { background: #f1f5f9; color: #334155; }
        .btn-print { background: #0f172a; color: #fff; }

        @media print {
            .controls { display: none !important; }
            .watermark { position: fixed; }
            body { padding: 0; }
            .page { max-width: 100%; margin: 0; }
            .content { padding: 36px 40px 28px; }
        }
    </style>
</head>
<body>

<?php
$fechaExpira = date('d/m/Y', strtotime($presupuesto['fecha'] . ' +' . ($presupuesto['validez_dias'] ?? 30) . ' days'));
$estadoNom   = ['borrador'=>'Borrador','enviado'=>'Enviado','aceptado'=>'Aceptado','rechazado'=>'Rechazado'];
$estado      = $presupuesto['estado'];
$studioNom   = htmlspecialchars($studio['nombre'] ?? 'InkManager Studio');

// URL canónica para el QR (puede no ser accesible desde fuera, pero queda para uso interno)
$qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=76x76&margin=2&data='
        . rawurlencode(BASE_URL . '/presupuestos/' . $presupuesto['id']);
?>

<!-- Marca de agua (solo aceptado/rechazado) -->
<?php if (in_array($estado, ['aceptado', 'rechazado'], true)): ?>
<div class="watermark <?= $estado ?>"><?= $estadoNom[$estado] ?></div>
<?php endif; ?>

<div class="page">
    <div class="accent-bar"></div>
    <div class="content">

        <!-- ── Letterhead ─────────────────────────────────────────────── -->
        <div class="header">
            <div class="brand">
                <div class="brand-dot">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-name">InkManager</div>
                    <div class="brand-studio">
                        <strong><?= $studioNom ?></strong>
                        <span style="color:#94a3b8"> · Studio System</span>
                    </div>
                </div>
            </div>
            <div class="studio-info">
                <?php if ($studio): ?>
                <?php if (!empty($studio['direccion'])): ?>
                <?= htmlspecialchars($studio['direccion']) ?><br>
                <?php endif; ?>
                <?php if (!empty($studio['telefono'])): ?>
                Tel: <?= htmlspecialchars($studio['telefono']) ?><br>
                <?php endif; ?>
                <?php if (!empty($studio['instagram'])): ?>
                @<?= htmlspecialchars($studio['instagram']) ?><br>
                <?php endif; ?>
                <?php if (!empty($studio['website'])): ?>
                <?= htmlspecialchars($studio['website']) ?>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Document title row ─────────────────────────────────────── -->
        <div class="doc-row">
            <div>
                <div class="doc-title"><?= htmlspecialchars($presupuesto['titulo']) ?></div>
                <div class="doc-subtitle">
                    <?= date('j \d\e F \d\e Y', strtotime($presupuesto['fecha'])) ?>
                </div>
                <div class="doc-status-wrap">
                    <span class="status-badge status-<?= htmlspecialchars($estado) ?>">
                        <?= htmlspecialchars($estadoNom[$estado] ?? $estado) ?>
                    </span>
                </div>
            </div>
            <div class="numero-block">
                <div class="numero-label">Presupuesto</div>
                <div class="numero-value"><?= htmlspecialchars($presupuesto['numero']) ?></div>
            </div>
        </div>

        <!-- ── Meta strip ─────────────────────────────────────────────── -->
        <div class="meta-strip">
            <div class="meta-cell">
                <div class="meta-label">Número</div>
                <div class="meta-value mono"><?= htmlspecialchars($presupuesto['numero']) ?></div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Fecha</div>
                <div class="meta-value"><?= date('d/m/Y', strtotime($presupuesto['fecha'])) ?></div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Válido hasta</div>
                <div class="meta-value"><?= $fechaExpira ?></div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Validez</div>
                <div class="meta-value"><?= (int)($presupuesto['validez_dias'] ?? 30) ?> días</div>
            </div>
        </div>

        <!-- ── Parties ────────────────────────────────────────────────── -->
        <div class="parties">
            <div class="party-box">
                <div class="party-role">Para · Cliente</div>
                <div class="party-name"><?= htmlspecialchars($presupuesto['cliente_nombre']) ?></div>
                <?php if (!empty($presupuesto['cliente_instagram'])): ?>
                <div class="party-detail">@<?= htmlspecialchars($presupuesto['cliente_instagram']) ?></div>
                <?php endif; ?>
                <?php if (!empty($presupuesto['cliente_telefono'])): ?>
                <div class="party-detail"><?= htmlspecialchars($presupuesto['cliente_telefono']) ?></div>
                <?php endif; ?>
            </div>
            <div class="party-box">
                <div class="party-role">De · Artista / Estudio</div>
                <div class="party-name"><?= $studioNom ?></div>
                <?php if ($studio && !empty($studio['instagram'])): ?>
                <div class="party-detail">@<?= htmlspecialchars($studio['instagram']) ?></div>
                <?php endif; ?>
                <?php if ($studio && !empty($studio['telefono'])): ?>
                <div class="party-detail"><?= htmlspecialchars($studio['telefono']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Description ────────────────────────────────────────────── -->
        <?php if (!empty($presupuesto['descripcion'])): ?>
        <div class="section">
            <div class="section-label">Descripción del trabajo</div>
            <div class="text-box"><?= htmlspecialchars($presupuesto['descripcion']) ?></div>
        </div>
        <?php endif; ?>

        <!-- ── Notas internas (si existen) ───────────────────────────── -->
        <?php if (!empty($presupuesto['notas'])): ?>
        <div class="section">
            <div class="section-label">Observaciones</div>
            <div class="text-box"><?= htmlspecialchars($presupuesto['notas']) ?></div>
        </div>
        <?php endif; ?>

        <!-- ── Price + QR ─────────────────────────────────────────────── -->
        <div class="bottom-row">
            <!-- QR code (internal link) -->
            <div class="qr-wrap">
                <img src="<?= htmlspecialchars($qrUrl) ?>"
                     alt="QR presupuesto" width="76" height="76"
                     onerror="this.style.display='none'">
                <div class="qr-label">Ver online</div>
            </div>

            <!-- Price table -->
            <?php if ($presupuesto['monto'] !== null && $presupuesto['monto'] !== '' && (float)$presupuesto['monto'] > 0): ?>
            <div class="price-block">
                <div class="price-table">
                    <div class="price-row">
                        <span class="p-label">Servicio</span>
                        <span class="p-amount">$<?= number_format((float)$presupuesto['monto'], 2, ',', '.') ?></span>
                    </div>
                    <div class="price-row total">
                        <span class="p-label">Total estimado</span>
                        <span class="p-amount">$<?= number_format((float)$presupuesto['monto'], 2, ',', '.') ?></span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="price-na">
                <strong>Monto a convenir</strong><br>
                El valor final se acordará según el diseño definitivo y la cantidad de sesiones necesarias.
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Validity notice ────────────────────────────────────────── -->
        <div class="notice">
            Este presupuesto es válido por
            <strong><?= (int)($presupuesto['validez_dias'] ?? 30) ?> días</strong>
            a partir del <strong><?= date('d/m/Y', strtotime($presupuesto['fecha'])) ?></strong>,
            hasta el <strong><?= $fechaExpira ?></strong>.
            Los precios pueden estar sujetos a cambios luego del vencimiento.
        </div>

        <!-- ── Signatures ─────────────────────────────────────────────── -->
        <div class="signatures">
            <div>
                <div class="sig-area"></div>
                <div class="sig-label">Firma del cliente</div>
                <div class="sig-name"><?= htmlspecialchars($presupuesto['cliente_nombre']) ?></div>
            </div>
            <div>
                <div class="sig-area"></div>
                <div class="sig-label">Firma del artista / estudio</div>
                <div class="sig-name"><?= $studioNom ?></div>
            </div>
        </div>

        <!-- ── Footer ─────────────────────────────────────────────────── -->
        <div class="print-footer">
            <span>Generado con InkManager Studio System</span>
            <span><?= htmlspecialchars($presupuesto['numero']) ?> · <?= date('d/m/Y H:i') ?></span>
        </div>

    </div><!-- /content -->
</div><!-- /page -->

<!-- ── Controls (hidden on print) ────────────────────────────────────────── -->
<div class="controls">
    <a href="javascript:history.back()" class="btn btn-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
        </svg>
        Volver
    </a>
    <button onclick="printDoc()" class="btn btn-print">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2
                     m2 4h6a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2z
                     m8-12V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4h10z"/>
        </svg>
        Imprimir / PDF
    </button>
</div>

<script>
function printDoc() {
    // Set document title for PDF filename suggestion
    var orig = document.title;
    document.title = 'Presupuesto-<?= htmlspecialchars($presupuesto['numero']) ?>-<?= htmlspecialchars(preg_replace('/[^a-zA-Z0-9]/', '_', $presupuesto['cliente_nombre'])) ?>';
    window.print();
    document.title = orig;
}
</script>

</body>
</html>
