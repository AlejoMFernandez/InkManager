/**
 * InkManager — Fase 4: Raycaster + Markers + Modals
 *
 * Extiende window.__bodymap (creado por bodymap.js) con:
 *  - Markers (esferas rojas) para tatuajes existentes con posición 3D
 *  - Click en cuerpo vacío → modal Crear Tatuaje
 *  - Click en marker       → modal Detalle / Editar / Borrar
 *  - Animación de pulso en markers
 *  - Cursor inteligente según hover
 */

import * as THREE from 'three';

const CFG = window.BODYMAP_CONFIG ?? {};
const BASE = CFG.base ?? '';  // BASE_URL desde PHP

// Estado local de tatuajes (mutable; se actualiza en create/update/delete)
const tatuajesMap = new Map(); // tatuajeId → data object
(CFG.tatuajes ?? []).forEach(t => tatuajesMap.set(t.id, { ...t }));

// ── Esperar a que bodymap.js esté listo ──────────────────────────────────────
function init() {
    const bm = window.__bodymap;
    if (!bm) { console.error('[markers] __bodymap no disponible'); return; }

    const { scene, camera, controls, bodyMeshes, markersGroup, canvas } = bm;
    const raycaster = new THREE.Raycaster();
    const pointer   = new THREE.Vector2();

    // ── Materials ─────────────────────────────────────────────────────────────
    const coreMat = new THREE.MeshBasicMaterial({ color: 0xff1a1a });
    const haloMat = new THREE.MeshBasicMaterial({
        color: 0xff3333, transparent: true, opacity: 0.28, depthWrite: false,
    });
    const hoverMat = new THREE.MeshBasicMaterial({ color: 0xff6600 });

    // ── Crear marker visual ───────────────────────────────────────────────────
    function makeMarker(t) {
        const g = new THREE.Group();
        g.userData.tatuajeId = t.id;
        g.userData.isMarker  = true;

        const core = new THREE.Mesh(new THREE.SphereGeometry(0.026, 16, 12), coreMat.clone());
        const halo = new THREE.Mesh(new THREE.SphereGeometry(0.046, 16, 12), haloMat.clone());
        halo.userData.isHalo = true;

        g.add(core, halo);

        // Offset ligero a lo largo de la normal para evitar z-fighting
        const nx = t.normal_x ?? 0;
        const ny = t.normal_y ?? 0;
        const nz = t.normal_z ?? 1;
        const OFFSET = 0.015;
        g.position.set(
            t.pos_x + nx * OFFSET,
            t.pos_y + ny * OFFSET,
            t.pos_z + nz * OFFSET
        );

        markersGroup.add(g);
        return g;
    }

    // Cargar markers existentes
    tatuajesMap.forEach(t => {
        if (t.pos_x !== null) makeMarker(t);
    });

    // ── Animación de pulso ───────────────────────────────────────────────────
    let tick = 0;
    bm.renderHooks.push(() => {
        tick += 0.022;
        markersGroup.children.forEach((m, i) => {
            const s = 1 + Math.sin(tick * 2.4 + i * 1.3) * 0.13;
            m.scale.setScalar(s);
        });
    });

    // ── Detección de arrastre vs click ───────────────────────────────────────
    let mouseDown = null;
    let dragging  = false;

    canvas.addEventListener('mousedown',  e => { mouseDown = { x: e.clientX, y: e.clientY }; dragging = false; });
    canvas.addEventListener('mousemove',  e => {
        if (mouseDown && Math.hypot(e.clientX - mouseDown.x, e.clientY - mouseDown.y) > 4)
            dragging = true;
    });
    canvas.addEventListener('mouseup',   () => mouseDown = null);

    // Touch
    let touchStart = null;
    let touchDrag  = false;
    canvas.addEventListener('touchstart', e => {
        touchStart = { x: e.touches[0].clientX, y: e.touches[0].clientY };
        touchDrag = false;
    }, { passive: true });
    canvas.addEventListener('touchmove', e => {
        if (!touchStart) return;
        if (Math.hypot(e.touches[0].clientX - touchStart.x, e.touches[0].clientY - touchStart.y) > 8)
            touchDrag = true;
    }, { passive: true });
    canvas.addEventListener('touchend', e => {
        if (touchDrag || !touchStart) { touchStart = null; return; }
        const touch = e.changedTouches[0];
        setPointer(touch.clientX, touch.clientY);
        handleRaycast();
        touchStart = null;
    });

    // ── Cursor hover ──────────────────────────────────────────────────────────
    let hoveredMarker = null;
    canvas.addEventListener('mousemove', e => {
        if (dragging) return;
        setPointer(e.clientX, e.clientY);
        raycaster.setFromCamera(pointer, camera);

        const markerMeshes = collectMarkerMeshes();
        const mHits = raycaster.intersectObjects(markerMeshes);
        const bHits = mHits.length === 0 ? raycaster.intersectObjects(bodyMeshes, true) : [];

        if (mHits.length > 0) {
            canvas.style.cursor = 'pointer';
            const newHovered = mHits[0].object.parent;
            if (newHovered !== hoveredMarker) {
                if (hoveredMarker) unhighlightMarker(hoveredMarker);
                highlightMarker(newHovered);
                hoveredMarker = newHovered;
            }
        } else {
            if (hoveredMarker) { unhighlightMarker(hoveredMarker); hoveredMarker = null; }
            canvas.style.cursor = bHits.length > 0 ? 'crosshair' : 'grab';
        }
    });

    // ── Click handler ─────────────────────────────────────────────────────────
    canvas.addEventListener('click', () => {
        if (dragging) return;
        handleRaycast();
    });

    function handleRaycast() {
        raycaster.setFromCamera(pointer, camera);

        // Prioridad: markers
        const markerMeshes = collectMarkerMeshes();
        const mHits = raycaster.intersectObjects(markerMeshes);
        if (mHits.length > 0) {
            const id = mHits[0].object.parent.userData.tatuajeId;
            openDetailModal(id);
            return;
        }

        // Cuerpo
        const bHits = raycaster.intersectObjects(bodyMeshes, true);
        if (bHits.length > 0) {
            const hit = bHits[0];
            const wn  = hit.face.normal.clone()
                .transformDirection(hit.object.matrixWorld).normalize();
            openFormModal(null, {
                pos_x: hit.point.x, pos_y: hit.point.y, pos_z: hit.point.z,
                normal_x: wn.x,     normal_y: wn.y,     normal_z: wn.z,
            });
        }
    }

    function setPointer(cx, cy) {
        const r = canvas.getBoundingClientRect();
        pointer.x = ((cx - r.left) / r.width)  *  2 - 1;
        pointer.y = ((cy - r.top)  / r.height) * -2 + 1;
    }

    function collectMarkerMeshes() {
        const out = [];
        markersGroup.children.forEach(g =>
            g.children.forEach(m => { if (!m.userData.isHalo) out.push(m); })
        );
        return out;
    }

    function highlightMarker(g) {
        g.children.forEach(m => { if (!m.userData.isHalo) m.material.color.set(0xff6600); });
    }
    function unhighlightMarker(g) {
        g.children.forEach(m => { if (!m.userData.isHalo) m.material.color.set(0xff1a1a); });
    }

    // ── MODAL: Crear / Editar tatuaje ────────────────────────────────────────
    const modalForm    = document.getElementById('modal-form-tatuaje');
    const formOverlay  = document.getElementById('modal-form-overlay');
    const formTitle    = document.getElementById('form-modal-title');
    const formElem     = document.getElementById('tatuaje-form');
    const fotoInput    = document.getElementById('form-foto');
    const fotoPreview  = document.getElementById('form-foto-preview');
    const fotoDropzone = document.getElementById('form-foto-dropzone');
    const btnCancelForm = document.getElementById('btn-cancel-form');

    let currentCoords = null;
    let editingId     = null;

    function openFormModal(tatuajeId, coords) {
        editingId     = tatuajeId;
        currentCoords = coords ?? null;

        formTitle.textContent = tatuajeId ? 'Editar tatuaje' : 'Nuevo tatuaje';
        formElem.reset();
        resetFotoPreview();

        if (tatuajeId) {
            const t = tatuajesMap.get(tatuajeId);
            if (t) prefillForm(t);
        }

        showModal(modalForm, formOverlay);
    }

    function prefillForm(t) {
        setVal('form-estilo',    t.estilo_id   ?? '');
        setVal('form-fecha',     t.fecha       ?? '');
        setVal('form-precio',    t.precio      ?? '');
        setVal('form-ses-total', t.sesiones_totales ?? 1);
        setVal('form-ses-hechas',t.sesiones_hechas  ?? 0);
        setVal('form-notas',     t.notas       ?? '');
        if (t.foto_url) {
            fotoPreview.src = t.foto_url;
            fotoPreview.classList.remove('hidden');
            fotoDropzone.classList.add('hidden');
        }
    }

    // Foto preview
    fotoInput?.addEventListener('change', () => {
        const file = fotoInput.files[0];
        if (!file) return;
        fotoPreview.src = URL.createObjectURL(file);
        fotoPreview.classList.remove('hidden');
        fotoDropzone.classList.add('hidden');
    });

    // Drag-and-drop en dropzone
    fotoDropzone?.addEventListener('dragover', e => { e.preventDefault(); fotoDropzone.classList.add('border-red-500'); });
    fotoDropzone?.addEventListener('dragleave', () => fotoDropzone.classList.remove('border-red-500'));
    fotoDropzone?.addEventListener('drop', e => {
        e.preventDefault();
        fotoDropzone.classList.remove('border-red-500');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fotoInput.files = dt.files;
            fotoPreview.src = URL.createObjectURL(file);
            fotoPreview.classList.remove('hidden');
            fotoDropzone.classList.add('hidden');
        }
    });

    btnCancelForm?.addEventListener('click', () => closeModal(modalForm, formOverlay));
    formOverlay?.addEventListener('click',   () => closeModal(modalForm, formOverlay));

    // Submit del form
    formElem?.addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(formElem);
        fd.set('_csrf', CFG.csrf);
        fd.set('cliente_id', String(CFG.clienteId));

        if (currentCoords && !editingId) {
            for (const [k, v] of Object.entries(currentCoords)) fd.set(k, String(v));
        }

        const url = editingId
            ? `${BASE}/api/tatuajes/${editingId}/actualizar`
            : `${BASE}/api/tatuajes`;

        const btnSubmit = formElem.querySelector('[type=submit]');
        setLoading(btnSubmit, true);

        try {
            const res  = await fetch(url, { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) { showToast(data.error ?? 'Error', 'error'); return; }

            const t = data.tatuaje;
            tatuajesMap.set(t.id, t);

            if (editingId) {
                updateSidebarItem(t);
                showToast('Tatuaje actualizado', 'success');
            } else {
                if (t.pos_x !== null) makeMarker(t);
                addSidebarItem(t);
                showToast('Tatuaje guardado', 'success');
            }

            closeModal(modalForm, formOverlay);
        } catch {
            showToast('Error de red. Intentá de nuevo.', 'error');
        } finally {
            setLoading(btnSubmit, false);
        }
    });

    // ── MODAL: Detalle ────────────────────────────────────────────────────────
    const modalDetail   = document.getElementById('modal-detalle-tatuaje');
    const detailOverlay = document.getElementById('modal-detalle-overlay');
    const btnCloseDetail = document.getElementById('btn-close-detail');
    const btnEditDetail  = document.getElementById('btn-edit-detail');
    const btnDelDetail   = document.getElementById('btn-delete-detail');

    function openDetailModal(tatuajeId) {
        const t = tatuajesMap.get(tatuajeId);
        if (!t) return;

        document.getElementById('detail-estilo').textContent =
            t.estilo_nombre ?? '—';
        document.getElementById('detail-fecha').textContent =
            t.fecha ? fmtFecha(t.fecha) : '—';
        document.getElementById('detail-precio').textContent =
            t.precio != null ? '$' + fmtNum(t.precio) : '—';
        document.getElementById('detail-sesiones').textContent =
            `${t.sesiones_hechas} de ${t.sesiones_totales} sesiones`;

        const progress = document.getElementById('detail-progress');
        if (progress) {
            const pct = t.sesiones_totales > 0
                ? Math.round((t.sesiones_hechas / t.sesiones_totales) * 100) : 0;
            progress.style.width = pct + '%';
        }

        document.getElementById('detail-notas').textContent = t.notas ?? '';
        document.getElementById('detail-notas-wrap').classList.toggle('hidden', !t.notas);

        const img     = document.getElementById('detail-foto');
        const noPhoto = document.getElementById('detail-no-foto');
        if (t.foto_url) {
            img.src = t.foto_url;
            img.classList.remove('hidden');
            noPhoto?.classList.add('hidden');
        } else {
            img.classList.add('hidden');
            noPhoto?.classList.remove('hidden');
        }

        btnEditDetail.dataset.id  = tatuajeId;
        btnDelDetail.dataset.id   = tatuajeId;

        showModal(modalDetail, detailOverlay);
    }

    btnCloseDetail?.addEventListener('click', () => closeModal(modalDetail, detailOverlay));
    detailOverlay?.addEventListener('click',  () => closeModal(modalDetail, detailOverlay));

    btnEditDetail?.addEventListener('click', () => {
        const id = parseInt(btnEditDetail.dataset.id);
        const t  = tatuajesMap.get(id);
        closeModal(modalDetail, detailOverlay);
        openFormModal(id, t ? { pos_x: t.pos_x, pos_y: t.pos_y, pos_z: t.pos_z,
                                normal_x: t.normal_x, normal_y: t.normal_y, normal_z: t.normal_z } : null);
    });

    btnDelDetail?.addEventListener('click', async () => {
        const id = parseInt(btnDelDetail.dataset.id);
        const t  = tatuajesMap.get(id);
        const nombre = t?.estilo_nombre ?? 'este tatuaje';
        if (!confirm(`¿Eliminar ${nombre}? Esta acción no se puede deshacer.`)) return;

        setLoading(btnDelDetail, true);
        try {
            const fd = new FormData();
            fd.set('_csrf', CFG.csrf);
            const res  = await fetch(`${BASE}/api/tatuajes/${id}/borrar`, { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) { showToast(data.error ?? 'Error', 'error'); return; }

            // Quitar marker de la escena
            const markerGroup = markersGroup.children.find(g => g.userData.tatuajeId === id);
            if (markerGroup) markersGroup.remove(markerGroup);

            tatuajesMap.delete(id);
            removeSidebarItem(id);
            closeModal(modalDetail, detailOverlay);
            showToast('Tatuaje eliminado', 'success');
        } catch {
            showToast('Error de red.', 'error');
        } finally {
            setLoading(btnDelDetail, false);
        }
    });

    // ── Sidebar: actualizar lista sin recargar ────────────────────────────────
    function addSidebarItem(t) {
        const list = document.getElementById('sidebar-tatuajes-list');
        if (!list) return;
        list.insertAdjacentHTML('afterbegin', buildSidebarItem(t));
        updateTatuajesCount(1);
    }

    function updateSidebarItem(t) {
        const item = document.querySelector(`[data-tatuaje-id="${t.id}"]`);
        if (!item) return;
        item.outerHTML = buildSidebarItem(t);
    }

    function removeSidebarItem(id) {
        document.querySelector(`[data-tatuaje-id="${id}"]`)?.remove();
        updateTatuajesCount(-1);
    }

    function updateTatuajesCount(delta) {
        const counter = document.getElementById('tatuajes-count');
        if (counter) counter.textContent = Math.max(0, (parseInt(counter.textContent) || 0) + delta);
        const mapCount = document.getElementById('bodymap-marked-count');
        if (mapCount && delta > 0) mapCount.textContent =
            Math.max(0, (parseInt(mapCount.textContent) || 0) + delta);
    }

    function buildSidebarItem(t) {
        const thumb = t.foto_url
            ? `<img src="${t.foto_url}" alt="" class="w-full h-full object-cover">`
            : `<div class="w-full h-full flex items-center justify-center">
                 <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                 </svg>
               </div>`;
        const fecha  = t.fecha  ? fmtFecha(t.fecha) : '—';
        const precio = t.precio != null ? ' · $' + fmtNum(t.precio) : '';
        const dot    = t.pos_x !== null
            ? `<div class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></div>` : '';

        return `<div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800/30 transition-colors cursor-pointer"
                     data-tatuaje-id="${t.id}">
            <div class="w-10 h-10 rounded-lg bg-gray-800 border border-gray-700 flex-shrink-0 overflow-hidden">
                ${thumb}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">${escHtml(t.estilo_nombre ?? 'Sin estilo')}</p>
                <p class="text-gray-600 text-xs">${fecha}${escHtml(precio)}</p>
            </div>
            ${dot}
        </div>`;
    }

    // Delegación de click en la lista del sidebar
    document.getElementById('sidebar-tatuajes-list')?.addEventListener('click', e => {
        const item = e.target.closest('[data-tatuaje-id]');
        if (!item) return;
        const id = parseInt(item.dataset.tatuajeId);
        if (tatuajesMap.has(id)) openDetailModal(id);
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function showModal(modal, overlay) {
        overlay.classList.remove('hidden');
        modal.classList.remove('hidden');
        modal.classList.add('modal-enter');
        setTimeout(() => modal.classList.remove('modal-enter'), 300);
    }
    function closeModal(modal, overlay) {
        overlay.classList.add('hidden');
        modal.classList.add('hidden');
    }
    function resetFotoPreview() {
        fotoPreview?.classList.add('hidden');
        fotoDropzone?.classList.remove('hidden');
        if (fotoInput) fotoInput.value = '';
    }
    function setVal(id, v) {
        const el = document.getElementById(id);
        if (el) el.value = v;
    }
    function setLoading(btn, on) {
        if (!btn) return;
        btn.disabled = on;
        btn.dataset.label ??= btn.textContent;
        btn.textContent = on ? 'Guardando…' : btn.dataset.label;
    }
    function fmtFecha(d) {
        const [y, m, day] = d.split('-');
        return `${day}/${m}/${y}`;
    }
    function fmtNum(n) {
        return Number(n).toLocaleString('es-AR', { maximumFractionDigits: 0 });
    }
    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Toast notifications ───────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const colors = type === 'success'
            ? 'bg-green-500/15 border-green-500/30 text-green-300'
            : 'bg-red-500/15 border-red-500/30 text-red-300';

        const toast = document.createElement('div');
        toast.className = `fixed bottom-5 right-5 z-50 flex items-center gap-2.5 px-4 py-3
                           rounded-xl border text-sm font-medium shadow-xl
                           transition-all duration-300 translate-y-2 opacity-0 ${colors}`;
        toast.innerHTML = `<span>${escHtml(msg)}</span>`;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        });
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Exponer para uso externo si se necesita
    window.__bodymap.markers = { openFormModal, openDetailModal, showToast };
}

// ── Arrancar cuando bodymap:ready se dispare ─────────────────────────────────
// Si el modelo procedurar ya cargó sincrónicamente antes de que este módulo
// ejecutara, el flag .ready ya está en true — llamamos init() directamente.
if (window.__bodymap?.ready) {
    init();
} else {
    window.addEventListener('bodymap:ready', init, { once: true });
}
