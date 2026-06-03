/**
 * InkManager — Markers + Zone Overlays
 *
 * Extends window.__bodymap (created by bodymap.js) with:
 *  - Scaled marker spheres per tattoo size (tamano: xs/s/m/l/xl)
 *  - Semi-transparent zone overlay meshes for coverage tattoos (zona)
 *  - Ink-type coloring (tinta: negro/gris/color)
 *  - Click on body → Create Tattoo modal
 *  - Click on marker → Detail/Edit/Delete modal
 *  - Pulsing animation for markers
 *  - Smart cursor on hover
 */

import * as THREE from 'three';

const CFG  = window.BODYMAP_CONFIG ?? {};
const BASE = CFG.base ?? '';

// ── Local tattoo state ────────────────────────────────────────────────────────
const tatuajesMap = new Map();
(CFG.tatuajes ?? []).forEach(t => tatuajesMap.set(t.id, { ...t }));

// ── Visual constants ──────────────────────────────────────────────────────────

// Core and halo radii indexed by tamano
const TAMANO_CORE = { xs: 0.018, s: 0.026, m: 0.040, l: 0.065, xl: 0.095 };
const TAMANO_HALO = { xs: 0.030, s: 0.046, m: 0.068, l: 0.108, xl: 0.155 };

// Marker sphere colors by ink type
const TINTA_MARKER = {
    negro: 0x2a2a5a,   // dark indigo — visible on the dark body
    gris:  0x9090c0,   // medium gray
    color: 0xff1a1a,   // red (existing default)
};

// Overlay mesh colors by ink type
const TINTA_OVERLAY = {
    negro: 0x090912,   // near-black with a blue tint
    gris:  0x28284a,   // dark blue-gray
    color: 0x5a0f38,   // deep wine/magenta
};
const TINTA_OPACITY = { negro: 0.82, gris: 0.60, color: 0.50 };

// CSS dot colors for the detail modal
const TINTA_DOT_CSS = { negro: '#2a2a5a', gris: '#9090c0', color: '#ff3366' };

// Human-readable labels
const TAMANO_LABELS = { xs: 'XS', s: 'S', m: 'M', l: 'L', xl: 'XL' };
const TINTA_LABELS  = { negro: 'Blackout / Negro', gris: 'Gris / B&W', color: 'Color' };
const ZONA_LABELS   = {
    'arm-left':      'Brazo izq.',
    'arm-right':     'Brazo der.',
    'forearm-left':  'Antebrazo izq.',
    'forearm-right': 'Antebrazo der.',
    'sleeve-left':   'Manga completa izq.',
    'sleeve-right':  'Manga completa der.',
    'chest':         'Pecho',
    'back':          'Espalda',
    'abdomen':       'Abdomen',
    'leg-left':      'Muslo izq.',
    'leg-right':     'Muslo der.',
    'calf-left':     'Pantorrilla izq.',
    'calf-right':    'Pantorrilla der.',
    'neck':          'Cuello / Nuca',
};

// Zone overlay geometry definitions (proportional to model height H)
// type 'cap' → CapsuleGeometry(r*H, l*H)
// type 'box' → BoxGeometry(w*H, h*H, d*H)
// cx/cy/cz are proportional coordinates; Y applied as botY + cy*H
const ZONE_DEFS = {
    'arm-left':      { type:'cap', r:0.058, l:0.40, cx:-0.204, cy:0.540, cz: 0      },
    'arm-right':     { type:'cap', r:0.058, l:0.40, cx: 0.204, cy:0.540, cz: 0      },
    'forearm-left':  { type:'cap', r:0.045, l:0.22, cx:-0.220, cy:0.398, cz: 0      },
    'forearm-right': { type:'cap', r:0.045, l:0.22, cx: 0.220, cy:0.398, cz: 0      },
    'sleeve-left':   { type:'cap', r:0.058, l:0.60, cx:-0.213, cy:0.472, cz: 0      },
    'sleeve-right':  { type:'cap', r:0.058, l:0.60, cx: 0.213, cy:0.472, cz: 0      },
    'chest':         { type:'box', w:0.46,  h:0.32, d:0.14, cx: 0,      cy:0.700, cz: 0.060 },
    'back':          { type:'box', w:0.46,  h:0.46, d:0.14, cx: 0,      cy:0.710, cz:-0.060 },
    'abdomen':       { type:'box', w:0.40,  h:0.24, d:0.12, cx: 0,      cy:0.526, cz: 0.052 },
    'leg-left':      { type:'cap', r:0.094, l:0.52, cx:-0.062, cy:0.294, cz: 0      },
    'leg-right':     { type:'cap', r:0.094, l:0.52, cx: 0.062, cy:0.294, cz: 0      },
    'calf-left':     { type:'cap', r:0.068, l:0.25, cx:-0.064, cy:0.143, cz: 0      },
    'calf-right':    { type:'cap', r:0.068, l:0.25, cx: 0.064, cy:0.143, cz: 0      },
    'neck':          { type:'cap', r:0.034, l:0.06, cx: 0,      cy:0.851, cz: 0      },
};

// ── Wait for bodymap.js ────────────────────────────────────────────────────────
function init() {
    const bm = window.__bodymap;
    if (!bm) { console.error('[markers] __bodymap no disponible'); return; }

    const { scene, camera, controls, bodyMeshes, markersGroup, canvas } = bm;
    const raycaster = new THREE.Raycaster();
    const pointer   = new THREE.Vector2();

    // ── Zone overlay group (separate from markers) ────────────────────────────
    const zonaGroup = new THREE.Group();
    scene.add(zonaGroup);

    // ── Create scaled marker ──────────────────────────────────────────────────
    function makeMarker(t) {
        const g = new THREE.Group();
        g.userData.tatuajeId = t.id;
        g.userData.isMarker  = true;

        const coreR   = TAMANO_CORE[t.tamano ?? 'm']  ?? 0.040;
        const haloR   = TAMANO_HALO[t.tamano ?? 'm']  ?? 0.068;
        const mColor  = TINTA_MARKER[t.tinta ?? 'color'] ?? 0xff1a1a;

        g.userData.baseColor = mColor;

        const cMat = new THREE.MeshBasicMaterial({ color: mColor });
        const hMat = new THREE.MeshBasicMaterial({
            color: mColor, transparent: true, opacity: 0.28, depthWrite: false,
        });

        const core = new THREE.Mesh(new THREE.SphereGeometry(coreR, 16, 12), cMat);
        const halo = new THREE.Mesh(new THREE.SphereGeometry(haloR, 16, 12), hMat);
        halo.userData.isHalo = true;

        g.add(core, halo);

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

    // ── Zone overlay: semi-transparent mesh covering a body zone ─────────────
    function makeZoneOverlay(t) {
        if (!t.zona) return null;
        const def = ZONE_DEFS[t.zona];
        if (!def) return null;

        const H    = window.__bodymap.modelH    ?? 1.87;
        const botY = window.__bodymap.modelBotY ?? -0.02;
        const tinta = t.tinta ?? 'negro';

        const mat = new THREE.MeshPhysicalMaterial({
            color:       TINTA_OVERLAY[tinta] ?? TINTA_OVERLAY.negro,
            transparent: true,
            opacity:     TINTA_OPACITY[tinta] ?? 0.82,
            depthWrite:  false,
            side:        THREE.DoubleSide,
            roughness:   0.9,
            metalness:   0.0,
        });

        let geo;
        if (def.type === 'cap') {
            geo = new THREE.CapsuleGeometry(def.r * H, def.l * H, 8, 20);
        } else {
            geo = new THREE.BoxGeometry(def.w * H, def.h * H, def.d * H, 2, 2, 2);
        }

        const mesh = new THREE.Mesh(geo, mat);
        mesh.userData.zonaOverlayId = t.id;
        mesh.position.set(def.cx * H, botY + def.cy * H, def.cz * H);

        zonaGroup.add(mesh);
        return mesh;
    }

    function removeZoneOverlay(id) {
        const m = zonaGroup.children.find(c => c.userData.zonaOverlayId === id);
        if (m) {
            m.geometry.dispose();
            m.material.dispose();
            zonaGroup.remove(m);
        }
    }

    /** Re-create all zone overlays after a gender swap (new model dimensions). */
    function refreshAllZoneOverlays() {
        while (zonaGroup.children.length > 0) {
            const m = zonaGroup.children[0];
            m.geometry.dispose();
            m.material.dispose();
            zonaGroup.remove(m);
        }
        tatuajesMap.forEach(t => { if (t.zona) makeZoneOverlay(t); });
    }

    // Listen to gender swap
    window.addEventListener('bodymap:swapped', refreshAllZoneOverlays);

    // ── Load existing tattoos ─────────────────────────────────────────────────
    tatuajesMap.forEach(t => {
        if (t.pos_x !== null) makeMarker(t);
        if (t.zona)           makeZoneOverlay(t);
    });

    // ── Pulse animation ───────────────────────────────────────────────────────
    let tick = 0;
    bm.renderHooks.push(() => {
        tick += 0.022;
        markersGroup.children.forEach((m, i) => {
            const s = 1 + Math.sin(tick * 2.4 + i * 1.3) * 0.13;
            m.scale.setScalar(s);
        });
    });

    // ── Drag vs click detection ───────────────────────────────────────────────
    let mouseDown = null;
    let dragging  = false;

    canvas.addEventListener('mousedown',  e => { mouseDown = { x: e.clientX, y: e.clientY }; dragging = false; });
    canvas.addEventListener('mousemove',  e => {
        if (mouseDown && Math.hypot(e.clientX - mouseDown.x, e.clientY - mouseDown.y) > 4)
            dragging = true;
    });
    canvas.addEventListener('mouseup', () => mouseDown = null);

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

    // ── Cursor + hover highlight ──────────────────────────────────────────────
    let hoveredMarker = null;
    canvas.addEventListener('mousemove', e => {
        if (dragging) return;
        setPointer(e.clientX, e.clientY);
        raycaster.setFromCamera(pointer, camera);

        const mMeshes = collectMarkerMeshes();
        const mHits   = raycaster.intersectObjects(mMeshes);
        const bHits   = mHits.length === 0 ? raycaster.intersectObjects(bodyMeshes, true) : [];

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
    canvas.addEventListener('click', () => { if (!dragging) handleRaycast(); });

    function handleRaycast() {
        raycaster.setFromCamera(pointer, camera);

        const mMeshes = collectMarkerMeshes();
        const mHits   = raycaster.intersectObjects(mMeshes);
        if (mHits.length > 0) {
            const id = mHits[0].object.parent.userData.tatuajeId;
            openDetailModal(id);
            return;
        }

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
        pointer.x =  ((cx - r.left) / r.width)  * 2 - 1;
        pointer.y = -((cy - r.top)  / r.height)  * 2 + 1;
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
        g.children.forEach(m => {
            if (!m.userData.isHalo) m.material.color.set(g.userData.baseColor ?? 0xff1a1a);
        });
    }

    // ── MODAL: Create / Edit tattoo ───────────────────────────────────────────
    const modalForm     = document.getElementById('modal-form-tatuaje');
    const formOverlay   = document.getElementById('modal-form-overlay');
    const formTitle     = document.getElementById('form-modal-title');
    const formElem      = document.getElementById('tatuaje-form');
    const fotoInput     = document.getElementById('form-foto');
    const fotoPreview   = document.getElementById('form-foto-preview');
    const fotoDropzone  = document.getElementById('form-foto-dropzone');
    const btnCancelForm = document.getElementById('btn-cancel-form');

    let currentCoords = null;
    let editingId     = null;

    function openFormModal(tatuajeId, coords) {
        editingId     = tatuajeId;
        currentCoords = coords ?? null;

        formTitle.textContent = tatuajeId ? 'Editar tatuaje' : 'Nuevo tatuaje';
        formElem.reset();
        resetFotoPreview();

        // Reset visual selectors to defaults
        if (window._activateTamano) window._activateTamano('m');
        if (window._activateTinta)  window._activateTinta('negro');
        setVal('form-zona', '');

        if (tatuajeId) {
            const t = tatuajesMap.get(tatuajeId);
            if (t) prefillForm(t);
        }

        showModal(modalForm, formOverlay);
    }

    function prefillForm(t) {
        setVal('form-estilo',     t.estilo_id   ?? '');
        setVal('form-fecha',      t.fecha        ?? '');
        setVal('form-precio',     t.precio       ?? '');
        setVal('form-ses-total',  t.sesiones_totales ?? 1);
        setVal('form-ses-hechas', t.sesiones_hechas  ?? 0);
        setVal('form-notas',      t.notas        ?? '');
        if (t.foto_url) {
            fotoPreview.src = t.foto_url;
            fotoPreview.classList.remove('hidden');
            fotoDropzone.classList.add('hidden');
        }
        // New fields
        if (window._activateTamano) window._activateTamano(t.tamano ?? 'm');
        setVal('form-zona', t.zona ?? '');
        if (window._activateTinta) window._activateTinta(t.tinta ?? 'negro');
    }

    fotoInput?.addEventListener('change', () => {
        const file = fotoInput.files[0];
        if (!file) return;
        fotoPreview.src = URL.createObjectURL(file);
        fotoPreview.classList.remove('hidden');
        fotoDropzone.classList.add('hidden');
    });

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

    // Submit
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
                // Rebuild zone overlay (size/zone/tinta may have changed)
                removeZoneOverlay(editingId);
                if (t.zona) makeZoneOverlay(t);
                showToast('Tatuaje actualizado', 'success');
            } else {
                if (t.pos_x !== null) makeMarker(t);
                if (t.zona)           makeZoneOverlay(t);
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

    // ── MODAL: Detail ─────────────────────────────────────────────────────────
    const modalDetail    = document.getElementById('modal-detalle-tatuaje');
    const detailOverlay  = document.getElementById('modal-detalle-overlay');
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

        const img       = document.getElementById('detail-foto');
        const noPhoto   = document.getElementById('detail-no-foto');
        const uploadBtn = document.getElementById('detail-upload-btn');
        if (t.foto_url) {
            img.src = t.foto_url;
            img.classList.remove('hidden');
            noPhoto?.classList.add('hidden');
            if (uploadBtn) uploadBtn.style.display = 'flex';
        } else {
            img.classList.add('hidden');
            noPhoto?.classList.remove('hidden');
            if (uploadBtn) uploadBtn.style.display = 'none';
        }

        // ── Cobertura row: tamano + zona + tinta ─────────────────────────────
        const cobWrap    = document.getElementById('detail-cobertura-wrap');
        const tamanoChip = document.getElementById('detail-tamano-chip');
        const zonaChip   = document.getElementById('detail-zona-chip');
        const tintaDot   = document.getElementById('detail-tinta-dot');
        const tintaLbl   = document.getElementById('detail-tinta-label');

        if (cobWrap) {
            if (tamanoChip) tamanoChip.textContent = TAMANO_LABELS[t.tamano ?? 'm'] ?? 'M';
            if (zonaChip) {
                const zl = ZONA_LABELS[t.zona] ?? '';
                zonaChip.textContent = zl;
                zonaChip.classList.toggle('hidden', !zl);
            }
            if (tintaDot)  tintaDot.style.backgroundColor = TINTA_DOT_CSS[t.tinta ?? 'negro'] ?? '#2a2a5a';
            if (tintaLbl)  tintaLbl.textContent = TINTA_LABELS[t.tinta ?? 'negro'] ?? 'Blackout';
            cobWrap.classList.remove('hidden');
        }

        btnEditDetail.dataset.id = tatuajeId;
        btnDelDetail.dataset.id  = tatuajeId;

        showModal(modalDetail, detailOverlay);
    }

    btnCloseDetail?.addEventListener('click', () => closeModal(modalDetail, detailOverlay));
    detailOverlay?.addEventListener('click',  () => closeModal(modalDetail, detailOverlay));

    btnEditDetail?.addEventListener('click', () => {
        const id = parseInt(btnEditDetail.dataset.id);
        const t  = tatuajesMap.get(id);
        closeModal(modalDetail, detailOverlay);
        openFormModal(id, t ? {
            pos_x: t.pos_x, pos_y: t.pos_y, pos_z: t.pos_z,
            normal_x: t.normal_x, normal_y: t.normal_y, normal_z: t.normal_z,
        } : null);
    });

    btnDelDetail?.addEventListener('click', async () => {
        const id     = parseInt(btnDelDetail.dataset.id);
        const t      = tatuajesMap.get(id);
        const nombre = t?.estilo_nombre ?? 'este tatuaje';
        if (!confirm(`¿Eliminar ${nombre}? Esta acción no se puede deshacer.`)) return;

        setLoading(btnDelDetail, true);
        try {
            const fd = new FormData();
            fd.set('_csrf', CFG.csrf);
            const res  = await fetch(`${BASE}/api/tatuajes/${id}/borrar`, { method: 'POST', body: fd });
            const data = await res.json();
            if (!data.success) { showToast(data.error ?? 'Error', 'error'); return; }

            // Remove 3D marker
            const mg = markersGroup.children.find(g => g.userData.tatuajeId === id);
            if (mg) markersGroup.remove(mg);

            // Remove zone overlay if any
            removeZoneOverlay(id);

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

    // ── Sidebar helpers ───────────────────────────────────────────────────────
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

    /** Sync tatuajesMap + sidebar from outside (e.g. after photo upload). */
    function updateTatuajeData(t) {
        tatuajesMap.set(t.id, t);
        updateSidebarItem(t);
    }

    function updateTatuajesCount(delta) {
        const counter = document.getElementById('tatuajes-count');
        if (counter) counter.textContent = Math.max(0, (parseInt(counter.textContent) || 0) + delta);
        const mapCount = document.getElementById('bodymap-marked-count');
        if (mapCount && delta > 0)
            mapCount.textContent = Math.max(0, (parseInt(mapCount.textContent) || 0) + delta);
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

        // Size chip
        const sizeChip = `<span class="text-xs px-1.5 py-0.5 rounded bg-gray-800 text-gray-600 font-mono">${TAMANO_LABELS[t.tamano ?? 'm'] ?? 'M'}</span>`;

        return `<div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800/30 transition-colors cursor-pointer"
                     data-tatuaje-id="${t.id}">
            <div class="w-10 h-10 rounded-lg bg-gray-800 border border-gray-700 flex-shrink-0 overflow-hidden">
                ${thumb}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5">
                    <p class="text-white text-xs font-medium truncate">${escHtml(t.estilo_nombre ?? 'Sin estilo')}</p>
                    ${sizeChip}
                </div>
                <p class="text-gray-600 text-xs">${fecha}${escHtml(precio)}</p>
            </div>
            ${dot}
        </div>`;
    }

    // Sidebar click delegation
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

        requestAnimationFrame(() => toast.classList.remove('translate-y-2', 'opacity-0'));
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ── Public API ────────────────────────────────────────────────────────────
    window.__bodymap.markers = { openFormModal, openDetailModal, showToast, updateTatuajeData };
}

// ── Boot ──────────────────────────────────────────────────────────────────────
if (window.__bodymap?.ready) {
    init();
} else {
    window.addEventListener('bodymap:ready', init, { once: true });
}
