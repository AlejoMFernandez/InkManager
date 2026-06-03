/**
 * InkManager — Body Map 3D
 * Three.js scene con iluminación de estudio profesional.
 * Soporta modelo masculino y femenino (GLTF externo o procedurar de fallback).
 */

import * as THREE        from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader }    from 'three/addons/loaders/GLTFLoader.js';

const CFG    = window.BODYMAP_CONFIG ?? {};
const canvas = document.getElementById('bodymap-canvas');
if (!canvas) throw new Error('[BodyMap] #bodymap-canvas not found');

// ── Resolución de género ───────────────────────────────────────────────────────
// Para clientes con genero="otro", el último modelo elegido se guarda en localStorage.
const rawGender    = CFG.genero ?? 'masculino';
let   activeGender = rawGender;
if (rawGender === 'otro') {
    const saved = localStorage.getItem('ink_g_' + (CFG.clienteId ?? '0'));
    activeGender = saved === 'femenino' ? 'femenino' : 'masculino';
}

// ── Scene ─────────────────────────────────────────────────────────────────────
const scene      = new THREE.Scene();
scene.background = new THREE.Color(0x07070e);
scene.fog        = new THREE.FogExp2(0x07070e, 0.055);

// ── Camera ────────────────────────────────────────────────────────────────────
const camera = new THREE.PerspectiveCamera(40, canvas.clientWidth / canvas.clientHeight, 0.01, 50);
camera.position.set(0, 0.92, 3.1);

// ── Renderer ──────────────────────────────────────────────────────────────────
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.setSize(canvas.clientWidth, canvas.clientHeight, false);
renderer.shadowMap.enabled   = true;
renderer.shadowMap.type      = THREE.PCFSoftShadowMap;
renderer.toneMapping         = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.15;
renderer.outputColorSpace    = THREE.SRGBColorSpace;

// ── Iluminación 4 puntos ──────────────────────────────────────────────────────
scene.add(new THREE.AmbientLight(0x1a1830, 2.5));

const keyLight = new THREE.DirectionalLight(0xfff0d0, 4.2);
keyLight.position.set(-1.6, 3.8, 2.8);
keyLight.castShadow = true;
keyLight.shadow.mapSize.setScalar(2048);
Object.assign(keyLight.shadow.camera, { left: -2, right: 2, top: 2.5, bottom: -2, near: 0.1, far: 14 });
keyLight.shadow.bias = -0.0008;
scene.add(keyLight);

const fillLight = new THREE.DirectionalLight(0x6090ff, 1.4);
fillLight.position.set(2.8, 1.2, -0.5);
scene.add(fillLight);

const rimLight = new THREE.DirectionalLight(0xff2208, 0.7);
rimLight.position.set(0, 2.2, -3.8);
scene.add(rimLight);

// Luz de relleno trasera — ilumina espalda y partes traseras del modelo
const backFill = new THREE.DirectionalLight(0x7090c8, 2.2);
backFill.position.set(0, 1.2, -3.5);
scene.add(backFill);

const groundLight = new THREE.DirectionalLight(0x221128, 0.6);
groundLight.position.set(0, -2, 0);
scene.add(groundLight);

// ── Floor ─────────────────────────────────────────────────────────────────────
const floor = new THREE.Mesh(
    new THREE.CircleGeometry(1.6, 64),
    new THREE.MeshStandardMaterial({ color: 0x0d0d16, roughness: 1.0, metalness: 0.0 })
);
floor.rotation.x = -Math.PI / 2;
floor.position.y = -0.02;
floor.receiveShadow = true;
scene.add(floor);

const ring = new THREE.Mesh(
    new THREE.RingGeometry(0.18, 1.05, 64),
    new THREE.MeshBasicMaterial({ color: 0x3a0000, transparent: true, opacity: 0.5, side: THREE.FrontSide })
);
ring.rotation.x = -Math.PI / 2;
ring.position.y = -0.018;
scene.add(ring);

// ── OrbitControls ─────────────────────────────────────────────────────────────
const controls = new OrbitControls(camera, canvas);
controls.target.set(0, 0.9, 0);
controls.enableDamping  = true;
controls.dampingFactor  = 0.065;
controls.enablePan      = false;
controls.minDistance    = 1.0;
controls.maxDistance    = 5.0;
controls.maxPolarAngle  = Math.PI * 0.86;
controls.rotateSpeed    = 0.55;
controls.zoomSpeed      = 0.75;
controls.update();

// ── Materiales ────────────────────────────────────────────────────────────────
const bodyMat = new THREE.MeshPhysicalMaterial({
    color:              0x28224a,   // punto medio: más claro que el original sin quemarse
    roughness:          0.62,
    metalness:          0.0,
    clearcoat:          0.22,
    clearcoatRoughness: 0.75,
    reflectivity:       0.3,
});

const edgeMat = new THREE.MeshPhysicalMaterial({
    color:     0x2a2050,
    roughness: 0.9,
    metalness: 0.0,
    side:      THREE.BackSide,
});

// ── Estado compartido ─────────────────────────────────────────────────────────
const bodyMeshes   = [];
const markersGroup = new THREE.Group();
scene.add(markersGroup);

let currentBodyGroup = null;

// ── API pública ───────────────────────────────────────────────────────────────
window.__bodymap = {
    scene, camera, renderer, controls,
    bodyMeshes, markersGroup, canvas,
    renderHooks: [],
    ready: false,
};

// ── Navegación por zonas ──────────────────────────────────────────────────────
// Posiciones calibradas para el modelo procedurar (~1.87m).
// Todas las distancias cámara→target son ≥ minDistance (1.0).
// "full" se rellena dinámicamente en mountBody con la posición real del modelo cargado.
const ZONES = {
    full:         null,
    head:         { pos: [0,      1.80,  1.08], look: [0,      1.76,  0]     },
    neck:         { pos: [0,      1.62,  1.05], look: [0,      1.60,  0]     },
    chest:        { pos: [0,      1.30,  1.20], look: [0,      1.25,  0]     },
    back:         { pos: [0,      1.30, -1.25], look: [0,      1.25,  0]     },
    'arm-left':   { pos: [-1.30,  1.12,  0.55], look: [-0.36,  1.10,  0]    },
    'arm-right':  { pos: [ 1.30,  1.12,  0.55], look: [ 0.36,  1.10,  0]    },
    'hand-left':  { pos: [-1.20,  0.95,  0.75], look: [-0.42,  0.87,  0]    },
    'hand-right': { pos: [ 1.20,  0.95,  0.75], look: [ 0.42,  0.87,  0]    },
    'leg-left':   { pos: [-0.55,  0.46,  1.55], look: [-0.11,  0.44,  0]    },
    'leg-right':  { pos: [ 0.55,  0.46,  1.55], look: [ 0.11,  0.44,  0]    },
    'foot-left':  { pos: [-0.42,  0.14,  1.02], look: [-0.11,  0.02,  0.04] },
    'foot-right': { pos: [ 0.42,  0.14,  1.02], look: [ 0.11,  0.02,  0.04] },
};

// ── Tween de cámara ───────────────────────────────────────────────────────────
let activeTween = null;

function tweenCamera(endPos, endLook, duration = 620) {
    const startPos  = camera.position.clone();
    const startLook = controls.target.clone();
    const ep = new THREE.Vector3(...endPos);
    const el = new THREE.Vector3(...endLook);
    const t0 = performance.now();

    // easeInOutCubic
    const ease = t => t < 0.5 ? 4*t*t*t : 1 - Math.pow(-2*t + 2, 3) / 2;

    activeTween = (now) => {
        const raw = Math.min((now - t0) / duration, 1);
        const k   = ease(raw);
        camera.position.lerpVectors(startPos, ep, k);
        controls.target.lerpVectors(startLook, el, k);
        if (raw >= 1) activeTween = null;
    };
}

// ── Ir a una zona ─────────────────────────────────────────────────────────────
function goToZone(zone) {
    const z = ZONES[zone];
    if (!z) return;
    tweenCamera(z.pos, z.look);
    setActiveZone(zone);
}

// ── Resaltar botón de zona activa ─────────────────────────────────────────────
function setActiveZone(zone) {
    document.querySelectorAll('[data-zone]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.zone === zone);
    });
}

// Exponer en API pública (útil para Feature 1C y extensiones futuras)
window.__bodymap.goToZone = goToZone;

// ── Helpers de geometría ──────────────────────────────────────────────────────
function addMesh(geo, x, y, z, rx = 0, ry = 0, rz = 0) {
    const m = new THREE.Mesh(geo, bodyMat);
    m.position.set(x, y, z);
    m.rotation.set(rx, ry, rz);
    m.castShadow = m.receiveShadow = true;
    return m;
}

function cap(r, len, x, y, z, rx = 0, ry = 0, rz = 0) {
    return addMesh(new THREE.CapsuleGeometry(r, len, 6, 24), x, y, z, rx, ry, rz);
}

function box(w, h, d, x, y, z, rx = 0, ry = 0, rz = 0) {
    return addMesh(new THREE.BoxGeometry(w, h, d, 2, 3, 2), x, y, z, rx, ry, rz);
}

function part(mesh, root) {
    root.add(mesh);
    const outline = new THREE.Mesh(mesh.geometry, edgeMat);
    outline.position.copy(mesh.position);
    outline.rotation.copy(mesh.rotation);
    outline.scale.setScalar(1.045);
    root.add(outline);
}

// ── Modelo procedurar: Masculino ──────────────────────────────────────────────
// Canon artístico 8 cabezas (~1.84m). Hombros anchos, caderas angostas.
function buildHumanoidMale() {
    const root = new THREE.Group();
    const P = (m) => part(m, root);

    // Cabeza
    const headGeo = new THREE.SphereGeometry(0.118, 32, 24);
    headGeo.applyMatrix4(new THREE.Matrix4().makeScale(1.0, 1.12, 0.92));
    P(addMesh(headGeo, 0, 1.785, -0.01));

    const jawGeo = new THREE.SphereGeometry(0.09, 24, 16);
    jawGeo.applyMatrix4(new THREE.Matrix4().makeScale(0.88, 0.6, 0.8));
    P(addMesh(jawGeo, 0, 1.64, 0.02));

    // Cuello
    P(cap(0.052, 0.05, 0, 1.575, 0));

    // Torso (trapecio: pecho ancho → cintura → caderas angostas)
    P(box(0.47, 0.30, 0.22, 0, 1.465, 0));
    P(box(0.41, 0.18, 0.20, 0, 1.255, 0));
    P(box(0.38, 0.22, 0.20, 0, 1.055, 0));
    P(box(0.40, 0.22, 0.21, 0, 0.855, 0));

    // Brazo izquierdo
    P(cap(0.052, 0.21, -0.315, 1.375, 0,  0, 0,  0.30));
    P(cap(0.040, 0.18, -0.390, 1.075, 0,  0, 0,  0.08));
    P(cap(0.038, 0.04, -0.422, 0.860, 0));

    // Brazo derecho
    P(cap(0.052, 0.21,  0.315, 1.375, 0,  0, 0, -0.30));
    P(cap(0.040, 0.18,  0.390, 1.075, 0,  0, 0, -0.08));
    P(cap(0.038, 0.04,  0.422, 0.860, 0));

    // Pierna izquierda
    P(cap(0.088, 0.27, -0.108, 0.655, 0));
    P(cap(0.065, 0.28, -0.113, 0.240, 0,  0.04, 0, 0));
    P(box(0.098, 0.068, 0.215, -0.108, 0.0, 0.046));

    // Pierna derecha
    P(cap(0.088, 0.27,  0.108, 0.655, 0));
    P(cap(0.065, 0.28,  0.113, 0.240, 0, -0.04, 0, 0));
    P(box(0.098, 0.068, 0.215,  0.108, 0.0, 0.046));

    return root;
}

// ── Modelo procedurar: Femenino ───────────────────────────────────────────────
// Hombros angostos (−15%), cintura estrecha (−22%), caderas anchas (+17%).
function buildHumanoidFemale() {
    const root = new THREE.Group();
    const P = (m) => part(m, root);

    // Cabeza (ligeramente más pequeña)
    const headGeo = new THREE.SphereGeometry(0.112, 32, 24);
    headGeo.applyMatrix4(new THREE.Matrix4().makeScale(0.97, 1.13, 0.90));
    P(addMesh(headGeo, 0, 1.785, -0.01));

    const jawGeo = new THREE.SphereGeometry(0.086, 24, 16);
    jawGeo.applyMatrix4(new THREE.Matrix4().makeScale(0.84, 0.58, 0.78));
    P(addMesh(jawGeo, 0, 1.64, 0.02));

    // Cuello más delgado
    P(cap(0.044, 0.055, 0, 1.575, 0));

    // Torso: forma ampolla (hombros angostos → cintura estrecha → caderas anchas)
    P(box(0.40, 0.28, 0.21, 0, 1.465, 0));
    P(box(0.32, 0.16, 0.18, 0, 1.255, 0));
    P(box(0.36, 0.22, 0.20, 0, 1.055, 0));
    P(box(0.47, 0.24, 0.22, 0, 0.855, 0));

    // Pecho (sutil, útil para referencia de tatuajes en la zona del torso)
    [[-0.09, 1.47], [0.09, 1.47]].forEach(([bx, by]) => {
        const bGeo = new THREE.SphereGeometry(0.065, 20, 16);
        bGeo.applyMatrix4(new THREE.Matrix4().makeScale(0.85, 0.78, 0.68));
        P(addMesh(bGeo, bx, by, 0.082));
    });

    // Brazo izquierdo (más delgado, posición ajustada a hombros angostos)
    P(cap(0.044, 0.21, -0.268, 1.375, 0,  0, 0,  0.24));
    P(cap(0.034, 0.18, -0.335, 1.075, 0,  0, 0,  0.07));
    P(cap(0.028, 0.04, -0.362, 0.860, 0));

    // Brazo derecho
    P(cap(0.044, 0.21,  0.268, 1.375, 0,  0, 0, -0.24));
    P(cap(0.034, 0.18,  0.335, 1.075, 0,  0, 0, -0.07));
    P(cap(0.028, 0.04,  0.362, 0.860, 0));

    // Pierna izquierda (muslos levemente más anchos por caderas amplias)
    P(cap(0.090, 0.27, -0.116, 0.655, 0));
    P(cap(0.060, 0.28, -0.118, 0.240, 0,  0.032, 0, 0));
    P(box(0.088, 0.060, 0.195, -0.112, 0.0, 0.042));

    // Pierna derecha
    P(cap(0.090, 0.27,  0.116, 0.655, 0));
    P(cap(0.060, 0.28,  0.118, 0.240, 0, -0.032, 0, 0));
    P(box(0.088, 0.060, 0.195,  0.112, 0.0, 0.042));

    return root;
}

function buildForGender(g) {
    return g === 'femenino' ? buildHumanoidFemale() : buildHumanoidMale();
}

// ── Loader overlay ────────────────────────────────────────────────────────────
function setLoader(v) {
    const el = document.getElementById('bodymap-loader');
    if (el) el.style.display = v ? 'flex' : 'none';
}

// ── Zonas dinámicas: calibradas para cualquier modelo ─────────────────────────
// botY = world Y del suelo (≈ -0.02). h = altura del modelo en unidades Three.js.
// Proporciones derivadas del modelo procedural canónico (~1.87 u).
// Distancias cámara→target verificadas ≥ minDistance (1.0) para h ≥ 1.4.
function updateZonesForModel(botY, h) {
    ZONES.head         = { pos: [0,        botY+h*.973,  h*.578], look: [0,        botY+h*.952, 0]         };
    ZONES.neck         = { pos: [0,        botY+h*.877,  h*.562], look: [0,        botY+h*.867, 0]         };
    ZONES.chest        = { pos: [0,        botY+h*.706,  h*.642], look: [0,        botY+h*.679, 0]         };
    ZONES.back         = { pos: [0,        botY+h*.706, -h*.669], look: [0,        botY+h*.679, 0]         };
    ZONES['arm-left']  = { pos: [-h*.695,  botY+h*.610,  h*.294], look: [-h*.193,  botY+h*.599, 0]         };
    ZONES['arm-right'] = { pos: [ h*.695,  botY+h*.610,  h*.294], look: [ h*.193,  botY+h*.599, 0]         };
    ZONES['hand-left'] = { pos: [-h*.642,  botY+h*.519,  h*.401], look: [-h*.225,  botY+h*.476, 0]         };
    ZONES['hand-right']= { pos: [ h*.642,  botY+h*.519,  h*.401], look: [ h*.225,  botY+h*.476, 0]         };
    ZONES['leg-left']  = { pos: [-h*.294,  botY+h*.257,  h*.829], look: [-h*.059,  botY+h*.246, 0]         };
    ZONES['leg-right'] = { pos: [ h*.294,  botY+h*.257,  h*.829], look: [ h*.059,  botY+h*.246, 0]         };
    ZONES['foot-left'] = { pos: [-h*.225,  botY+h*.086,  h*.546], look: [-h*.059,  botY+h*.021, h*.021]    };
    ZONES['foot-right']= { pos: [ h*.225,  botY+h*.086,  h*.546], look: [ h*.059,  botY+h*.021, h*.021]    };
}

// ── Mount: coloca el grupo en escena, actualiza bodyMeshes ────────────────────
// isSwap=true preserva la posición de cámara (para el toggle masculino/femenino).
function mountBody(group, isSwap = false) {
    if (currentBodyGroup) scene.remove(currentBodyGroup);
    currentBodyGroup = group;

    // bodyMeshes se limpia IN PLACE para que markers.js (que tiene la misma referencia)
    // apunte automáticamente a los nuevos meshes tras el swap.
    // Se aceptan tanto meshes del modelo procedural (bodyMat) como de GLB (userData.isBody).
    bodyMeshes.length = 0;
    group.traverse(child => {
        if (child.isMesh && (child.material === bodyMat || child.userData.isBody))
            bodyMeshes.push(child);
    });

    const box3 = new THREE.Box3().setFromObject(group);
    const size = box3.getSize(new THREE.Vector3());
    const ctr  = box3.getCenter(new THREE.Vector3());

    group.position.x -= ctr.x;
    group.position.z -= ctr.z;
    group.position.y  = floor.position.y - box3.min.y + group.position.y;

    // Recalcular zonas de cámara según la geometría real del modelo cargado
    updateZonesForModel(floor.position.y, size.y);

    if (!isSwap) {
        const midY = floor.position.y + size.y * 0.5;
        controls.target.set(0, midY, 0);
        camera.position.set(0, midY + size.y * 0.05, size.y * 1.55 + 0.8);
        controls.update();
        // Guardar posición "Vista general" para esta geometría concreta
        ZONES.full = {
            pos:  [camera.position.x, camera.position.y, camera.position.z],
            look: [controls.target.x, controls.target.y, controls.target.z],
        };
        setActiveZone('full');
    }

    scene.add(group);
    setLoader(false);

    // Exponer dimensiones para que markers.js pueda posicionar overlays de zona
    window.__bodymap.modelH    = size.y;
    window.__bodymap.modelBotY = floor.position.y;

    const badge = document.getElementById('bodymap-status');
    if (badge) badge.textContent = activeGender === 'femenino' ? 'Modelo F · 3D' : 'Modelo M · 3D';

    if (!isSwap) {
        window.__bodymap.ready = true;
        window.dispatchEvent(new CustomEvent('bodymap:ready', { detail: { bodyMeshes } }));
    } else {
        // Avisa a markers.js que reconstruya los overlays con las nuevas dimensiones
        window.dispatchEvent(new CustomEvent('bodymap:swapped', {
            detail: { modelH: size.y, modelBotY: floor.position.y },
        }));
    }
}

// ── Carga de modelo para un género ───────────────────────────────────────────
const gltfLoader = new GLTFLoader();

function loadBodyForGender(gender, isSwap = false) {
    // Prioridad: GLB específico de género → body.glb legacy (solo masculino) → procedurar
    const url = gender === 'femenino'
        ? (CFG.modelUrlFem  ?? null)
        : (CFG.modelUrlMasc ?? CFG.modelUrl ?? null);

    if (url) {
        setLoader(true);
        gltfLoader.load(
            url,
            gltf => {
                gltf.scene.traverse(c => {
                    if (!c.isMesh) return;
                    // Aplicar bodyMat para paleta visual consistente en todos los modelos.
                    // El color 0x28224a es suficientemente claro para apreciar la iluminación.
                    c.material    = bodyMat;
                    c.castShadow  = c.receiveShadow = true;
                });

                // ── Auto-normalizar escala ─────────────────────────────────────
                // Mixamo FBX convertidos online vienen en cm (~170 u de alto).
                // Blender bien exportado: ~1.7-1.9 u. Ambos quedan en ~1.82 u.
                const tmpBox = new THREE.Box3().setFromObject(gltf.scene);
                const modelH = tmpBox.max.y - tmpBox.min.y;
                if (modelH > 2.5 || modelH < 0.5) {
                    gltf.scene.scale.setScalar(1.82 / modelH);
                }

                mountBody(gltf.scene, isSwap);
            },
            undefined,
            () => mountBody(buildForGender(gender), isSwap)
        );
    } else {
        mountBody(buildForGender(gender), isSwap);
    }
}

// ── Toggle de género (solo para clientes con genero="otro") ───────────────────
function switchGender(gender) {
    if (gender === activeGender) return;
    activeGender = gender;
    localStorage.setItem('ink_g_' + CFG.clienteId, gender);
    loadBodyForGender(gender, true);
    updateGenderToggleUI(gender);
}

function updateGenderToggleUI(gender) {
    document.querySelectorAll('[data-gender-btn]').forEach(btn => {
        const active = btn.dataset.genderBtn === gender;
        btn.classList.toggle('bg-red-700/70', active);
        btn.classList.toggle('text-white',    active);
        btn.classList.toggle('text-gray-400', !active);
    });
}

document.querySelectorAll('[data-gender-btn]').forEach(btn => {
    btn.addEventListener('click', () => switchGender(btn.dataset.genderBtn));
});
if (rawGender === 'otro') updateGenderToggleUI(activeGender);

// ── Zona: wiring botones ──────────────────────────────────────────────────────
document.querySelectorAll('[data-zone]').forEach(btn => {
    btn.addEventListener('click', () => goToZone(btn.dataset.zone));
});

// ── Carga inicial ─────────────────────────────────────────────────────────────
loadBodyForGender(activeGender, false);

// ── Resize ────────────────────────────────────────────────────────────────────
new ResizeObserver(() => {
    const w = canvas.clientWidth, h = canvas.clientHeight;
    if (!w || !h) return;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h, false);
}).observe(canvas);

// ── Render loop ───────────────────────────────────────────────────────────────
(function animate(ts = 0) {
    requestAnimationFrame(animate);
    if (activeTween) activeTween(ts);   // actualiza camera.position y controls.target
    controls.update();                   // aplica damping + recalcula matrices
    window.__bodymap.renderHooks.forEach(fn => fn());
    renderer.render(scene, camera);
})();
