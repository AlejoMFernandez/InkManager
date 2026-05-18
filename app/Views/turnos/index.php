<?php
$pageTitle = 'Turnos';

ob_start();
?>
<style>
    /* FullCalendar dark theme overrides */
    .fc { --fc-border-color: #1f2937; --fc-page-bg-color: transparent;
          --fc-neutral-bg-color: #111827; --fc-list-event-hover-bg-color: #1f2937;
          --fc-today-bg-color: rgba(220,38,38,.08); --fc-now-indicator-color: #dc2626;
          font-size: 0.8rem; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #1f2937; }
    .fc-col-header-cell-cushion, .fc-daygrid-day-number,
    .fc-timegrid-slot-label-cushion { color: #9ca3af !important; text-decoration: none; }
    .fc-button-primary { background: #374151 !important; border-color: #4b5563 !important;
                          color: #f9fafb !important; font-size: .75rem !important; }
    .fc-button-primary:hover { background: #4b5563 !important; }
    .fc-button-primary:not(:disabled).fc-button-active,
    .fc-button-primary:not(:disabled):active { background: #dc2626 !important; border-color: #dc2626 !important; }
    .fc-toolbar-title { color: #f9fafb; font-size: 1rem !important; font-weight: 600; }
    .fc-event { border-radius: 5px !important; font-size: .72rem !important;
                padding: 1px 4px !important; cursor: pointer; }
    .fc-timegrid-event .fc-event-title { font-weight: 500; }
    .fc-timegrid-now-indicator-line { border-color: #dc2626; }
    .fc-scrollgrid { border-color: #1f2937 !important; }
</style>
<?php
$extraHead = ob_get_clean();

ob_start();
?>
<script>
const TURNOS_CFG = {
    feedUrl:      '<?= BASE_URL ?>/api/turnos',
    reagendarUrl: '<?= BASE_URL ?>/api/turnos/{id}/reagendar',
    estadoUrl:    '<?= BASE_URL ?>/api/turnos/{id}/estado',
    nuevoUrl:     '<?= BASE_URL ?>/turnos/nuevo',
    verUrl:       '<?= BASE_URL ?>/turnos/{id}',
    csrf:         '<?= \App\Core\Auth::csrfToken() ?>',
};
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView:   window.innerWidth < 640 ? 'listWeek' : 'timeGridWeek',
        locale:        'es',
        firstDay:      1,         // lunes
        slotMinTime:   '08:00:00',
        slotMaxTime:   '22:00:00',
        allDaySlot:    false,
        nowIndicator:  true,
        height:        'auto',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        },
        buttonText: { month:'Mes', week:'Semana', day:'Día', list:'Lista', today:'Hoy' },

        // Feed de eventos vía JSON
        events: {
            url:         TURNOS_CFG.feedUrl,
            method:      'GET',
            extraParams: () => ({ csrf: TURNOS_CFG.csrf }),
            failure:     () => showToast('Error al cargar los turnos', 'error'),
        },

        // Click en evento → ir al detalle
        eventClick: ({ event }) => {
            window.location.href = TURNOS_CFG.verUrl.replace('{id}', event.id);
        },

        // Click en slot vacío → nuevo turno con fecha pre-cargada
        dateClick: ({ dateStr }) => {
            window.location.href = TURNOS_CFG.nuevoUrl + '?fecha=' + encodeURIComponent(dateStr);
        },

        // Drag & drop para reagendar
        editable:    true,
        droppable:   false,
        eventDrop: async ({ event, revert }) => {
            const url = TURNOS_CFG.reagendarUrl.replace('{id}', event.id);
            const dur = Math.round((event.end - event.start) / 60000);
            const ok  = await apiPost(url, {
                fecha_inicio: event.start.toISOString(),
                duracion_min: dur,
            });
            if (!ok) { revert(); showToast('Error al reagendar', 'error'); }
            else showToast('Turno reagendado', 'success');
        },

        // Resize para cambiar duración
        eventResizableFromStart: false,
        eventResize: async ({ event, revert }) => {
            const url = TURNOS_CFG.reagendarUrl.replace('{id}', event.id);
            const dur = Math.round((event.end - event.start) / 60000);
            const ok  = await apiPost(url, {
                fecha_inicio: event.start.toISOString(),
                duracion_min: dur,
            });
            if (!ok) { revert(); showToast('Error al guardar', 'error'); }
        },

        // Tooltip al hover
        eventMouseEnter: ({ event, el }) => {
            const p = event.extendedProps;
            el.title = `${event.title} · ${p.duracion}min · ${p.estado}`;
        },
    });

    cal.render();

    // Botón "Nuevo turno"
    document.getElementById('btn-nuevo-turno')?.addEventListener('click', () => {
        window.location.href = TURNOS_CFG.nuevoUrl;
    });

    // ── helpers ─────────────────────────────────────────────────────────────
    async function apiPost(url, body) {
        try {
            const fd = new FormData();
            fd.set('_csrf', TURNOS_CFG.csrf);
            const res  = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...body, _csrf: TURNOS_CFG.csrf }),
            });
            const data = await res.json();
            return data.success === true;
        } catch { return false; }
    }

    function showToast(msg, type = 'success') {
        const c = type === 'success'
            ? 'bg-green-500/15 border-green-500/30 text-green-300'
            : 'bg-red-500/15 border-red-500/30 text-red-300';
        const t = document.createElement('div');
        t.className = `fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl border text-sm
                       font-medium shadow-xl transition-all duration-300
                       translate-y-2 opacity-0 ${c}`;
        t.textContent = msg;
        document.body.appendChild(t);
        requestAnimationFrame(() => t.classList.remove('translate-y-2','opacity-0'));
        setTimeout(() => { t.classList.add('translate-y-2','opacity-0'); setTimeout(() => t.remove(), 300); }, 3000);
    }
});
</script>
<?php
$extraScripts = ob_get_clean();
?>

<!-- Header -->
<div class="flex items-center justify-between mb-5 page-enter">
    <div>
        <p class="brand-tagline mb-1">// Agenda</p>
        <h2 class="section-heading">Turnos <span class="accent">&amp; Sesiones</span></h2>
        <p class="text-gray-500 text-xs mt-2">Arrastrá para reagendar · Click en slot para agendar</p>
    </div>
    <button id="btn-nuevo-turno"
            class="btn-glow inline-flex items-center gap-2 px-4 py-2
                   bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                   text-white text-sm font-semibold rounded-lg
                   active:scale-95 transition-all">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nuevo turno
    </button>
</div>

<!-- Calendario -->
<div class="bg-gray-900 border border-gray-800 rounded-xl p-4 sm:p-5">
    <div id="calendar"></div>
</div>

<!-- Leyenda de estados -->
<div class="flex flex-wrap items-center gap-4 mt-4 px-1">
    <?php
    $estados = [
        ['color'=>'#3b82f6','label'=>'Agendado'],
        ['color'=>'#22c55e','label'=>'Confirmado'],
        ['color'=>'#6b7280','label'=>'Hecho'],
        ['color'=>'#ef4444','label'=>'Cancelado'],
    ];
    foreach ($estados as $e): ?>
    <div class="flex items-center gap-1.5">
        <div class="w-2.5 h-2.5 rounded-sm" style="background:<?= $e['color'] ?>"></div>
        <span class="text-gray-500 text-xs"><?= $e['label'] ?></span>
    </div>
    <?php endforeach; ?>
</div>
