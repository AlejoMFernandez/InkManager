<?php
$pageTitle = 'Turnos';
$csrf      = \App\Core\Auth::csrfToken();

ob_start();
?>
<style>
    /* ── FullCalendar dark theme ────────────────────────────────────────── */
    .fc {
        --fc-border-color:                 #1f2937;
        --fc-page-bg-color:                transparent;
        --fc-neutral-bg-color:             #111827;
        --fc-list-event-hover-bg-color:    #1f2937;
        --fc-today-bg-color:               rgba(220,38,38,.07);
        --fc-now-indicator-color:          #dc2626;
        font-size: .8rem;
    }
    .fc-theme-standard td, .fc-theme-standard th    { border-color:#1f2937; }
    .fc-col-header-cell-cushion,
    .fc-daygrid-day-number,
    .fc-timegrid-slot-label-cushion                 { color:#9ca3af !important; text-decoration:none; }
    .fc-button-primary                              { background:#374151 !important; border-color:#4b5563 !important; color:#f9fafb !important; font-size:.74rem !important; }
    .fc-button-primary:hover                        { background:#4b5563 !important; }
    .fc-button-primary:not(:disabled).fc-button-active,
    .fc-button-primary:not(:disabled):active        { background:#dc2626 !important; border-color:#dc2626 !important; }
    .fc-toolbar-title                               { color:#f9fafb; font-size:.95rem !important; font-weight:600; }
    .fc-event                                       { border-radius:5px !important; font-size:.72rem !important; padding:1px 5px !important; cursor:pointer; }
    .fc-timegrid-event .fc-event-title              { font-weight:500; }
    .fc-timegrid-now-indicator-line                 { border-color:#dc2626; }
    .fc-scrollgrid                                  { border-color:#1f2937 !important; }
    .fc-list-day-cushion                            { background:#111827 !important; }
    .fc-list-event-title a                          { color:#e5e7eb !important; }
    .fc-list-event-dot                              { border-width:6px !important; }

    /* ── Event popover ─────────────────────────────────────────────────── */
    #event-popover {
        width: 248px;
        animation: popIn .14s cubic-bezier(.16,1,.3,1) both;
        box-shadow: 0 20px 60px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.06);
    }
    @keyframes popIn {
        from { opacity:0; transform:scale(.94) translateY(-6px); }
        to   { opacity:1; transform:scale(1); }
    }

    /* ── Filter chips ──────────────────────────────────────────────────── */
    .fc-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .25rem .65rem; border-radius: 9999px; font-size: .7rem;
        font-weight: 600; border: 1px solid transparent;
        color: #6b7280; background: transparent; cursor: pointer;
        transition: all 120ms; user-select: none;
    }
    .fc-chip:hover   { background:#1f2937; color:#d1d5db; }
    .fc-chip.active  { background:rgba(239,68,68,.12); border-color:rgba(239,68,68,.3); color:#fca5a5; }

    /* ── Today panel items ─────────────────────────────────────────────── */
    .today-item {
        display: flex; align-items: flex-start; gap: .6rem;
        padding: .55rem .875rem; cursor: pointer;
        transition: background 100ms;
    }
    .today-item:hover { background: #1f2937; }
    .today-item.highlighted { background: rgba(220,38,38,.08); }
</style>
<?php
$extraHead = ob_get_clean();

ob_start();
?>
<script>
const TURNOS_CFG = {
    feedUrl:      '<?= BASE_URL ?>/api/turnos',
    crearUrl:     '<?= BASE_URL ?>/api/turnos',
    reagendarUrl: '<?= BASE_URL ?>/api/turnos/{id}/reagendar',
    estadoUrl:    '<?= BASE_URL ?>/api/turnos/{id}/estado',
    nuevoUrl:     '<?= BASE_URL ?>/turnos/nuevo',
    verUrl:       '<?= BASE_URL ?>/turnos/{id}',
    editarUrl:    '<?= BASE_URL ?>/turnos/{id}/editar',
    csrf:         <?= json_encode($csrf) ?>,
};
const CLIENTES_DATA = <?= json_encode(array_map(fn($c) => [
    'id'    => (int) $c['id'],
    'label' => $c['nombre'] . ($c['instagram'] ? ' (' . $c['instagram'] . ')' : ''),
], $clientes), JSON_UNESCAPED_UNICODE) ?>;
const ESTADO_COLORS = {
    agendado:   ['#3b82f6', '#1d4ed8'],
    confirmado: ['#22c55e', '#15803d'],
    hecho:      ['#6b7280', '#374151'],
    cancelado:  ['#ef4444', '#b91c1c'],
};
const ESTADO_LABELS = {
    agendado:   'Agendado',
    confirmado: 'Confirmado',
    hecho:      'Hecho',
    cancelado:  'Cancelado',
};
const ESTADO_BADGE = {
    agendado:   'text-blue-400  bg-blue-500/10  border-blue-500/25',
    confirmado: 'text-green-400 bg-green-500/10 border-green-500/25',
    hecho:      'text-gray-400  bg-gray-500/10  border-gray-500/25',
    cancelado:  'text-red-400   bg-red-500/10   border-red-500/25',
};
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── FullCalendar ────────────────────────────────────────────────────────
    var cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView:   window.innerWidth < 640 ? 'listWeek' : 'timeGridWeek',
        locale:        'es',
        firstDay:      1,
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

        events: {
            url:         TURNOS_CFG.feedUrl,
            method:      'GET',
            extraParams: function() { return { csrf: TURNOS_CFG.csrf }; },
            failure:     function() { showToast('Error al cargar los turnos', 'error'); },
        },

        eventsSet: function() {
            // Re-apply active filter after refetch
            applyActiveFilter();
        },

        // Click en evento → popover
        eventClick: function(info) {
            info.jsEvent.stopPropagation();
            showPopover(info.event, info.jsEvent);
        },

        // Click en slot → modal de creación rápida
        dateClick: function(info) {
            openQuickCreate(info.dateStr);
        },

        // Drag & drop
        editable: true,
        eventDrop: async function(info) {
            var url = TURNOS_CFG.reagendarUrl.replace('{id}', info.event.id);
            var dur = Math.round((info.event.end - info.event.start) / 60000);
            var ok  = await apiPostJson(url, {
                fecha_inicio: info.event.start.toISOString(),
                duracion_min: dur,
            });
            if (ok) { showToast('Turno reagendado', 'success'); loadTodayPanel(); }
            else    { info.revert(); showToast('Error al reagendar', 'error'); }
        },

        // Resize → cambiar duración
        eventResize: async function(info) {
            var url = TURNOS_CFG.reagendarUrl.replace('{id}', info.event.id);
            var dur = Math.round((info.event.end - info.event.start) / 60000);
            var ok  = await apiPostJson(url, {
                fecha_inicio: info.event.start.toISOString(),
                duracion_min: dur,
            });
            if (ok) { showToast('Duración actualizada', 'success'); }
            else    { info.revert(); showToast('Error al guardar', 'error'); }
        },

        eventMouseEnter: function(info) {
            var p = info.event.extendedProps;
            info.el.title = info.event.title + ' · ' + p.duracion + 'min · ' + (ESTADO_LABELS[p.estado] || p.estado);
        },
    });

    cal.render();
    document.getElementById('btn-nuevo-turno')?.addEventListener('click', function() {
        window.location.href = TURNOS_CFG.nuevoUrl;
    });

    // ── Today panel ─────────────────────────────────────────────────────────
    function loadTodayPanel() {
        var today = new Date();
        var yyyy  = today.getFullYear();
        var mm    = String(today.getMonth()+1).padStart(2,'0');
        var dd    = String(today.getDate()).padStart(2,'0');
        var start = yyyy + '-' + mm + '-' + dd;
        var end   = yyyy + '-' + mm + '-' + dd + 'T23:59:59';

        // Date label
        var lbl = document.getElementById('today-date-label');
        if (lbl) {
            lbl.textContent = today.toLocaleDateString('es-AR', {
                weekday:'long', day:'numeric', month:'long'
            });
        }

        var list = document.getElementById('today-list');
        if (!list) return;
        list.innerHTML = '<div class="py-8 flex justify-center"><div class="w-5 h-5 border-2 border-gray-700 border-t-red-500 rounded-full animate-spin"></div></div>';

        fetch(TURNOS_CFG.feedUrl + '?start=' + start + '&end=' + end + '&csrf=' + encodeURIComponent(TURNOS_CFG.csrf))
            .then(function(r){ return r.json(); })
            .then(function(events) {
                events.sort(function(a,b){ return a.start < b.start ? -1 : 1; });
                if (!events.length) {
                    list.innerHTML = '<p class="text-gray-600 text-xs text-center py-8 px-4">Sin turnos para hoy</p>';
                    var sum = document.getElementById('today-summary');
                    if (sum) sum.textContent = 'Día libre';
                    return;
                }
                list.innerHTML = events.map(function(ev) {
                    var p     = ev.extendedProps || {};
                    var badge = ESTADO_BADGE[p.estado] || 'text-gray-500 bg-gray-800 border-gray-700';
                    var time  = new Date(ev.start).toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'});
                    return '<div class="today-item" data-event-id="' + ev.id + '">' +
                        '<div class="flex-shrink-0 pt-0.5">' +
                            '<span class="inline-block w-1.5 h-1.5 rounded-full mt-1" style="background:' + (ESTADO_COLORS[p.estado]?.[0] || '#6b7280') + '"></span>' +
                        '</div>' +
                        '<div class="flex-1 min-w-0">' +
                            '<p class="text-white text-xs font-medium truncate">' + escHtml(ev.title) + '</p>' +
                            '<p class="text-gray-500 text-[10px]">' + time + ' · ' + (p.duracion||'?') + 'min</p>' +
                        '</div>' +
                        '<span class="text-[10px] px-1.5 py-0.5 rounded-full border flex-shrink-0 ' + badge + '">' + (ESTADO_LABELS[p.estado]||p.estado) + '</span>' +
                    '</div>';
                }).join('');

                var sum = document.getElementById('today-summary');
                if (sum) {
                    var active = events.filter(function(e){ return (e.extendedProps||{}).estado !== 'cancelado'; }).length;
                    sum.textContent = active + ' turno' + (active !== 1 ? 's' : '');
                }

                // Click en item → highlight y abrir popover en calendario
                list.querySelectorAll('.today-item').forEach(function(el) {
                    el.addEventListener('click', function() {
                        var id  = el.dataset.eventId;
                        var ev  = cal.getEventById(id);
                        if (!ev) return;
                        // Navigate to event's date if needed, then open popover
                        cal.gotoDate(ev.start);
                        list.querySelectorAll('.today-item').forEach(function(x){ x.classList.remove('highlighted'); });
                        el.classList.add('highlighted');
                        // Fake a center-screen click to open popover
                        var fakeEvt = { clientX: window.innerWidth/2, clientY: window.innerHeight/2 };
                        showPopover(ev, fakeEvt);
                    });
                });
            })
            .catch(function() {
                var list2 = document.getElementById('today-list');
                if (list2) list2.innerHTML = '<p class="text-gray-600 text-xs text-center py-6 px-4">Error al cargar</p>';
            });
    }
    loadTodayPanel();

    // ── Event popover ────────────────────────────────────────────────────────
    var pop = document.getElementById('event-popover');

    function showPopover(event, jsEvent) {
        if (!pop) return;
        var p = event.extendedProps;

        pop.querySelector('.pop-name').textContent    = event.title;
        pop.querySelector('.pop-time').textContent    = fmtEventTime(event.start, p.duracion);
        var badgeEl = pop.querySelector('.pop-badge');
        badgeEl.textContent  = ESTADO_LABELS[p.estado] || p.estado;
        badgeEl.className    = 'pop-badge text-[11px] px-2 py-0.5 rounded-full border ' + (ESTADO_BADGE[p.estado] || '');
        pop.querySelector('.pop-notas').textContent   = p.notas || '';
        pop.querySelector('.pop-notas-wrap').classList.toggle('hidden', !p.notas);

        // Seña
        var senaWrap = pop.querySelector('.pop-sena-wrap');
        if (senaWrap) {
            senaWrap.classList.toggle('hidden', !p.sena);
            var senaEl = pop.querySelector('.pop-sena');
            if (senaEl) senaEl.textContent = p.sena ? '$' + Number(p.sena).toLocaleString('es-AR',{maximumFractionDigits:0}) : '';
        }

        // Quick-state buttons
        var btns = pop.querySelectorAll('.estado-btn');
        btns.forEach(function(btn) {
            btn.classList.toggle('ring-2', btn.dataset.estado === p.estado);
            btn.classList.toggle('ring-offset-1', btn.dataset.estado === p.estado);
            btn.classList.toggle('ring-offset-gray-900', btn.dataset.estado === p.estado);
        });

        // Links
        pop.querySelector('.pop-ver').href    = TURNOS_CFG.verUrl.replace('{id}',    event.id);
        pop.querySelector('.pop-editar').href = TURNOS_CFG.editarUrl.replace('{id}', event.id);

        pop.dataset.eventId = event.id;

        // Position
        positionPopover(jsEvent);
        pop.classList.remove('hidden');
        pop.style.animation = 'none';
        requestAnimationFrame(function(){ pop.style.animation = ''; });
    }

    function positionPopover(jsEvent) {
        var pw = 248, ph = 220;
        var vw = window.innerWidth, vh = window.innerHeight;
        var left = jsEvent.clientX + 14;
        var top  = jsEvent.clientY - ph / 2;
        if (left + pw > vw - 12) left = jsEvent.clientX - pw - 14;
        if (top < 8)              top  = 8;
        if (top + ph > vh - 8)    top  = vh - ph - 8;
        pop.style.left = left + 'px';
        pop.style.top  = top  + 'px';
    }

    function closePopover() {
        if (pop) pop.classList.add('hidden');
        document.querySelectorAll('.today-item').forEach(function(x){ x.classList.remove('highlighted'); });
    }

    // Close popover on outside click
    document.addEventListener('click', function(e) {
        if (pop && !pop.classList.contains('hidden') && !pop.contains(e.target)) {
            closePopover();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closePopover();
    });

    // Quick state change from popover
    if (pop) {
        pop.querySelectorAll('.estado-btn').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                var id       = pop.dataset.eventId;
                var newState = btn.dataset.estado;
                var url      = TURNOS_CFG.estadoUrl.replace('{id}', id);
                var ok       = await apiPostJson(url, { estado: newState });
                if (!ok) { showToast('Error al cambiar estado', 'error'); return; }

                // Update calendar event
                var ev = cal.getEventById(id);
                if (ev) {
                    ev.setProp('backgroundColor', ESTADO_COLORS[newState][0]);
                    ev.setProp('borderColor',      ESTADO_COLORS[newState][1]);
                    ev.setExtendedProp('estado', newState);
                }

                // Update badge in popover
                var badgeEl = pop.querySelector('.pop-badge');
                badgeEl.textContent = ESTADO_LABELS[newState];
                badgeEl.className   = 'pop-badge text-[11px] px-2 py-0.5 rounded-full border ' + (ESTADO_BADGE[newState] || '');
                pop.querySelectorAll('.estado-btn').forEach(function(b) {
                    b.classList.toggle('ring-2',               b.dataset.estado === newState);
                    b.classList.toggle('ring-offset-1',        b.dataset.estado === newState);
                    b.classList.toggle('ring-offset-gray-900', b.dataset.estado === newState);
                });

                showToast('Estado actualizado', 'success');
                loadTodayPanel();
            });
        });
        pop.querySelector('.pop-close')?.addEventListener('click', closePopover);
    }

    // ── Filtros de estado ────────────────────────────────────────────────────
    var activeFilter = 'all';

    function applyActiveFilter() {
        cal.getEvents().forEach(function(event) {
            var show = activeFilter === 'all' || event.extendedProps.estado === activeFilter;
            event.setProp('display', show ? 'auto' : 'none');
        });
    }

    document.querySelectorAll('.fc-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.fc-chip').forEach(function(c){ c.classList.remove('active'); });
            chip.classList.add('active');
            activeFilter = chip.dataset.filter;
            applyActiveFilter();
            closePopover();
        });
    });

    // ── Quick-create modal ──────────────────────────────────────────────────
    var qcOverlay = document.getElementById('qc-overlay');
    var qcModal   = document.getElementById('qc-modal');
    var qcForm    = document.getElementById('qc-form');

    // Populate client select
    var qcCliente = document.getElementById('qc-cliente');
    if (qcCliente) {
        CLIENTES_DATA.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value       = c.id;
            opt.textContent = c.label;
            qcCliente.appendChild(opt);
        });
    }

    function openQuickCreate(dateStr) {
        closePopover();
        // Pre-fill datetime
        var dtInput = document.getElementById('qc-fecha');
        if (dtInput && dateStr) {
            // dateStr may be "2026-05-31T14:00:00" or "2026-05-31"
            var iso = dateStr.length > 10 ? dateStr.substring(0,16) : dateStr + 'T09:00';
            dtInput.value = iso;
        }
        if (qcCliente) qcCliente.value = '';
        document.getElementById('qc-duracion').value  = '60';
        document.getElementById('qc-notas').value     = '';
        qcOverlay?.classList.remove('hidden');
        qcModal?.classList.remove('hidden');
        qcModal?.classList.remove('pointer-events-none');
        setTimeout(function(){ qcCliente?.focus(); }, 50);
    }

    function closeQuickCreate() {
        qcOverlay?.classList.add('hidden');
        qcModal?.classList.add('hidden');
    }

    document.getElementById('qc-cancel')?.addEventListener('click', closeQuickCreate);
    document.getElementById('qc-cancel-2')?.addEventListener('click', closeQuickCreate);
    qcOverlay?.addEventListener('click', closeQuickCreate);

    qcForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        var submitBtn = document.getElementById('qc-submit');
        var fd = new FormData(qcForm);
        fd.set('_csrf', TURNOS_CFG.csrf);

        submitBtn.disabled    = true;
        submitBtn.textContent = 'Agendando…';

        try {
            var res  = await fetch(TURNOS_CFG.crearUrl, { method:'POST', body:fd });
            var data = await res.json();
            if (!data.success) {
                showToast(data.error || 'Error al crear el turno.', 'error');
                return;
            }
            // Add event to calendar
            if (data.event) cal.addEvent(data.event);
            closeQuickCreate();
            showToast('Turno agendado', 'success');
            loadTodayPanel();
        } catch(err) {
            showToast('Error de red.', 'error');
        } finally {
            submitBtn.disabled    = false;
            submitBtn.textContent = 'Agendar';
        }
    });

    // ── Helpers ──────────────────────────────────────────────────────────────
    async function apiPostJson(url, body) {
        try {
            var res  = await fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ ...body, _csrf: TURNOS_CFG.csrf }),
            });
            var data = await res.json();
            return data.success === true;
        } catch { return false; }
    }

    function fmtEventTime(start, durMin) {
        var d   = new Date(start);
        var end = new Date(d.getTime() + (durMin||60)*60000);
        var fmt = function(dt) {
            return dt.toLocaleTimeString('es-AR',{hour:'2-digit',minute:'2-digit'});
        };
        var day = d.toLocaleDateString('es-AR',{weekday:'short',day:'numeric',month:'short'});
        return day + ' · ' + fmt(d) + ' – ' + fmt(end);
    }

    function escHtml(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function showToast(msg, type) {
        var c = type === 'success'
            ? 'bg-green-500/15 border-green-500/30 text-green-300'
            : 'bg-red-500/15   border-red-500/30   text-red-300';
        var t = document.createElement('div');
        t.className = 'fixed bottom-5 right-5 z-[200] px-4 py-3 rounded-xl border text-sm font-medium shadow-xl transition-all duration-300 translate-y-2 opacity-0 ' + c;
        t.textContent = msg;
        document.body.appendChild(t);
        requestAnimationFrame(function(){ t.classList.remove('translate-y-2','opacity-0'); });
        setTimeout(function(){ t.classList.add('translate-y-2','opacity-0'); setTimeout(function(){ t.remove(); },300); },3000);
    }
});
</script>
<?php
$extraScripts = ob_get_clean();
?>

<!-- ── Header ──────────────────────────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 page-enter">
    <div>
        <p class="brand-tagline mb-1">// Agenda</p>
        <h2 class="section-heading">Turnos <span class="accent">&amp; Sesiones</span></h2>
        <p class="text-gray-500 text-xs mt-1.5 hidden sm:block">Arrastrá para reagendar · Click en slot para agendar rápido</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/export/turnos"
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
</div>

<!-- ── Layout principal ────────────────────────────────────────────────────── -->
<div class="flex gap-4 items-start">

    <!-- Hoy panel (desktop only) -->
    <div class="hidden lg:flex flex-col w-56 flex-shrink-0">
        <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden card-in">

            <!-- Header del panel -->
            <div class="px-4 py-3 border-b border-gray-800">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Agenda de hoy</p>
                <p class="text-white text-sm font-semibold capitalize leading-tight" id="today-date-label">—</p>
            </div>

            <!-- Lista de turnos del día -->
            <div id="today-list"
                 class="divide-y divide-gray-800/60 min-h-[80px] max-h-[54vh] overflow-y-auto">
                <div class="py-8 flex justify-center">
                    <div class="w-5 h-5 border-2 border-gray-700 border-t-red-500 rounded-full animate-spin"></div>
                </div>
            </div>

            <!-- Footer con resumen -->
            <div class="px-4 py-2 border-t border-gray-800/60">
                <p class="text-gray-600 text-[10px]" id="today-summary">Cargando…</p>
            </div>
        </div>
    </div>

    <!-- Área del calendario -->
    <div class="flex-1 min-w-0 space-y-3">

        <!-- Filtros por estado -->
        <div class="flex flex-wrap items-center gap-1.5">
            <button class="fc-chip active" data-filter="all">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                Todos
            </button>
            <button class="fc-chip" data-filter="agendado"
                    style="--c:#3b82f6">
                <svg class="w-2.5 h-2.5 text-blue-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                Agendado
            </button>
            <button class="fc-chip" data-filter="confirmado">
                <svg class="w-2.5 h-2.5 text-green-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                Confirmado
            </button>
            <button class="fc-chip" data-filter="hecho">
                <svg class="w-2.5 h-2.5 text-gray-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                Hecho
            </button>
            <button class="fc-chip" data-filter="cancelado">
                <svg class="w-2.5 h-2.5 text-red-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                Cancelado
            </button>
        </div>

        <!-- Calendario -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-3 sm:p-4">
            <div id="calendar"></div>
        </div>

        <!-- Leyenda + hint -->
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 px-1">
            <?php foreach ([
                ['#3b82f6','Agendado'],
                ['#22c55e','Confirmado'],
                ['#6b7280','Hecho'],
                ['#ef4444','Cancelado'],
            ] as [$col,$lbl]): ?>
            <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-sm" style="background:<?= $col ?>"></div>
                <span class="text-gray-500 text-xs"><?= $lbl ?></span>
            </div>
            <?php endforeach; ?>
            <span class="ml-auto text-gray-700 text-xs hidden sm:block">
                Click en slot = agendar · Arrastrar = reagendar
            </span>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════
     POPOVER — Detalle de turno
════════════════════════════════════════════════════════════════════ -->
<div id="event-popover"
     class="hidden fixed z-[70] bg-gray-900 border border-gray-700 rounded-2xl overflow-hidden">

    <!-- Header: nombre + cerrar -->
    <div class="flex items-start justify-between gap-2 px-4 pt-4 pb-2">
        <div class="min-w-0">
            <p class="pop-name text-white font-semibold text-sm leading-tight truncate"></p>
            <p class="pop-time text-gray-500 text-xs mt-0.5"></p>
        </div>
        <button class="pop-close flex-shrink-0 w-6 h-6 rounded-full bg-gray-800
                        hover:bg-gray-700 flex items-center justify-center
                        text-gray-400 hover:text-white transition-colors mt-0.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Badge de estado actual -->
    <div class="px-4 pb-3 flex items-center gap-2">
        <span class="pop-badge text-[11px] px-2 py-0.5 rounded-full border"></span>
        <!-- Seña -->
        <span class="pop-sena-wrap hidden text-[11px] text-gray-500">
            Seña: <span class="pop-sena text-gray-300"></span>
        </span>
    </div>

    <!-- Notas -->
    <div class="pop-notas-wrap hidden px-4 pb-3">
        <p class="pop-notas text-gray-400 text-xs leading-relaxed italic line-clamp-2"></p>
    </div>

    <!-- Cambio rápido de estado -->
    <div class="px-4 pb-3">
        <p class="text-[10px] text-gray-600 uppercase tracking-wider font-semibold mb-2">Cambiar estado</p>
        <div class="grid grid-cols-2 gap-1.5">
            <button class="estado-btn text-xs py-1.5 rounded-lg
                           bg-blue-500/10 border border-blue-500/25 text-blue-400
                           hover:bg-blue-500/20 transition-colors ring-blue-500"
                    data-estado="agendado">Agendado</button>
            <button class="estado-btn text-xs py-1.5 rounded-lg
                           bg-green-500/10 border border-green-500/25 text-green-400
                           hover:bg-green-500/20 transition-colors ring-green-500"
                    data-estado="confirmado">Confirmado</button>
            <button class="estado-btn text-xs py-1.5 rounded-lg
                           bg-gray-500/10 border border-gray-500/25 text-gray-400
                           hover:bg-gray-500/20 transition-colors ring-gray-500"
                    data-estado="hecho">Hecho</button>
            <button class="estado-btn text-xs py-1.5 rounded-lg
                           bg-red-500/10 border border-red-500/25 text-red-400
                           hover:bg-red-500/20 transition-colors ring-red-500"
                    data-estado="cancelado">Cancelado</button>
        </div>
    </div>

    <!-- Acciones -->
    <div class="px-4 pb-4 flex gap-2 border-t border-gray-800 pt-3">
        <a class="pop-ver flex-1 text-center text-xs py-2 rounded-lg
                   bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white
                   transition-colors" href="#">
            Ver detalle
        </a>
        <a class="pop-editar flex-1 text-center text-xs py-2 rounded-lg
                   bg-red-600/10 hover:bg-red-600/20 text-red-400
                   border border-red-600/20 hover:border-red-600/40
                   transition-colors" href="#">
            Editar
        </a>
    </div>
</div>


<!-- ══════════════════════════════════════════════════════════════════
     MODAL — Creación rápida de turno
════════════════════════════════════════════════════════════════════ -->
<div id="qc-overlay"
     class="hidden fixed inset-0 z-[80] bg-black/70 backdrop-blur-sm"></div>

<div id="qc-modal"
     class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 pointer-events-none">
    <div class="pointer-events-auto w-full max-w-sm bg-gray-900 border border-gray-700
                rounded-2xl shadow-2xl overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-sm font-semibold text-white">Agendar turno</h3>
            </div>
            <button id="qc-cancel"
                    class="w-7 h-7 rounded-full bg-gray-800 hover:bg-gray-700
                           flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form id="qc-form" class="px-5 py-4 space-y-3">

            <!-- Cliente -->
            <div>
                <label for="qc-cliente" class="block text-xs font-medium text-gray-400 mb-1.5">
                    Cliente <span class="text-red-500">*</span>
                </label>
                <select id="qc-cliente" name="cliente_id" required
                        class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                               text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                    <option value="">Seleccioná un cliente…</option>
                    <!-- populated by JS from CLIENTES_DATA -->
                </select>
            </div>

            <!-- Fecha y hora -->
            <div>
                <label for="qc-fecha" class="block text-xs font-medium text-gray-400 mb-1.5">
                    Fecha y hora <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" id="qc-fecha" name="fecha_inicio" required
                       class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                              text-white text-sm [color-scheme:dark]
                              focus:outline-none focus:ring-2 focus:ring-red-600/50">
            </div>

            <!-- Duración + Estado (fila) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="qc-duracion" class="block text-xs font-medium text-gray-400 mb-1.5">Duración</label>
                    <select id="qc-duracion" name="duracion_min"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                        <?php foreach ([30,60,90,120,150,180,240,300,360] as $min): ?>
                        <option value="<?= $min ?>" <?= $min === 60 ? 'selected' : '' ?>>
                            <?= $min >= 60
                                ? ($min/60 == floor($min/60) ? ($min/60).'h' : floor($min/60).'h '.($min%60).'min')
                                : $min.'min' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="qc-estado" class="block text-xs font-medium text-gray-400 mb-1.5">Estado</label>
                    <select id="qc-estado" name="estado"
                            class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                   text-white text-sm focus:outline-none focus:ring-2 focus:ring-red-600/50">
                        <option value="agendado">Agendado</option>
                        <option value="confirmado">Confirmado</option>
                    </select>
                </div>
            </div>

            <!-- Notas -->
            <div>
                <label for="qc-notas" class="block text-xs font-medium text-gray-400 mb-1.5">Notas</label>
                <textarea id="qc-notas" name="notas" rows="2"
                          class="w-full px-3 py-2.5 bg-gray-800 border border-gray-700 rounded-lg
                                 text-white text-sm placeholder-gray-600 resize-none
                                 focus:outline-none focus:ring-2 focus:ring-red-600/50"
                          placeholder="Referencias, indicaciones…"></textarea>
            </div>

            <!-- Botones -->
            <div class="flex gap-2 pt-1">
                <button type="button" id="qc-cancel-2"
                        class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700
                               text-gray-300 text-sm rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="qc-submit"
                        class="btn-glow flex-1 py-2.5
                               bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600
                               text-white text-sm font-semibold rounded-lg
                               active:scale-[0.98] transition-all">
                    Agendar
                </button>
            </div>
        </form>
    </div>
</div>
