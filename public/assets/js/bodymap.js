/**
 * InkManager — Body Map 3D
 * Three.js scene con iluminación de estudio profesional.
 * Modelo procedurar con CapsuleGeometry + MeshPhysicalMaterial.
 */

import * as THREE        from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader }    from 'three/addons/loaders/GLTFLoader.js';

const CFG    = window.BODYMAP_CONFIG ?? {};
const canvas = document.getElementById('bodymap-canvas');
if (!canvas) throw new Error('[BodyMap] #bodymap-canvas not found');

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

// ── Lighting profesional 4 puntos ─────────────────────────────────────────────
// Ambiente suave con color azul-noche
scene.add(new THREE.AmbientLight(0x1a1830, 2.0));

// Key light: calida, frontal-superior-izquierda
const keyLight = new THREE.DirectionalLight(0xfff0d0, 3.2);
keyLight.position.set(-1.6, 3.8, 2.8);
keyLight.castShadow = true;
keyLight.shadow.mapSize.setScalar(2048);
Object.assign(keyLight.shadow.camera, { left:-2, right:2, top:2.5, bottom:-2, near:0.1, far:14 });
keyLight.shadow.bias = -0.0008;
scene.add(keyLight);

// Fill light: fría, derecha
const fillLight = new THREE.DirectionalLight(0x6090ff, 1.1);
fillLight.position.set(2.8, 1.2, -0.5);
scene.add(fillLight);

// Rim light: rojo profundo, desde atrás — firma visual del estudio
const rimLight = new THREE.DirectionalLight(0xff1a0a, 0.55);
rimLight.position.set(0, 2.2, -3.8);
scene.add(rimLight);

// Luz de suelo: muy tenue para recuperar detalle en partes bajas
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

// Ring glow rojo en el suelo
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
controls.enableDamping = true; controls.dampingFactor = 0.065;
controls.enablePan = false;
controls.minDistance = 1.0; controls.maxDistance = 5.0;
controls.maxPolarAngle = Math.PI * 0.86;
controls.rotateSpeed = 0.55; controls.zoomSpeed = 0.75;
controls.update();

// ── Material principal: MeshPhysicalMaterial ──────────────────────────────────
const bodyMat = new THREE.MeshPhysicalMaterial({
    color:               0x19172a,
    roughness:           0.62,
    metalness:           0.0,
    clearcoat:           0.22,
    clearcoatRoughness:  0.75,
    reflectivity:        0.3,
});

// ── Material secundario: edge subtle highlight ─────────────────────────────────
const edgeMat = new THREE.MeshPhysicalMaterial({
    color:     0x2a2050,
    roughness: 0.9,
    metalness: 0.0,
    side:      THREE.BackSide,   // inverted-hull outline
});

// ── Colección de meshes para Raycaster ────────────────────────────────────────
const bodyMeshes  = [];
const markersGroup = new THREE.Group();
scene.add(markersGroup);
const raycaster = new THREE.Raycaster();
const pointer   = new THREE.Vector2();

// ── API pública ANTES de mountBody ────────────────────────────────────────────
window.__bodymap = {
    scene, camera, renderer, controls,
    bodyMeshes, markersGroup, raycaster, pointer, canvas,
    renderHooks: [],
    ready: false,
};

// ── Helpers para construir partes ─────────────────────────────────────────────
function addMesh(geo, x, y, z, rx = 0, ry = 0, rz = 0) {
    const m = new THREE.Mesh(geo, bodyMat);
    m.position.set(x, y, z);
    m.rotation.set(rx, ry, rz);
    m.castShadow = m.receiveShadow = true;
    return m;
}

function cap(r, len, x, y, z, rx = 0, ry = 0, rz = 0) {
    // CapsuleGeometry(radius, length, capSegments, radialSegments)
    return addMesh(new THREE.CapsuleGeometry(r, len, 6, 24), x, y, z, rx, ry, rz);
}

function box(w, h, d, x, y, z, rx = 0, ry = 0, rz = 0) {
    return addMesh(new THREE.BoxGeometry(w, h, d, 2, 3, 2), x, y, z, rx, ry, rz);
}

// Agrega un mesh y su silueta de contorno al grupo
function part(mesh, root) {
    root.add(mesh);
    // Outline sutil: mismo geo, BackSide, ligeramente más grande
    const outline = new THREE.Mesh(mesh.geometry, edgeMat);
    outline.position.copy(mesh.position);
    outline.rotation.copy(mesh.rotation);
    outline.scale.setScalar(1.045);
    root.add(outline);
}

// ── Humanoid procedurar mejorado ──────────────────────────────────────────────
// Proporciones basadas en canon artístico (8 cabezas de altura ≈ 1.84m)
function buildHumanoid() {
    const root = new THREE.Group();
    const P = (m, r) => part(m, r); // shorthand

    // ── CABEZA (ligeramente ovalada) ──────────────────────────────────────────
    const headGeo = new THREE.SphereGeometry(0.118, 32, 24);
    // Aplana levemente el eje Z (cráneo) y alarga el Y (cara)
    headGeo.applyMatrix4(new THREE.Matrix4().makeScale(1.0, 1.12, 0.92));
    P(addMesh(headGeo, 0, 1.785, -0.01), root);

    // Mandíbula / mentón (elipsoide más pequeño)
    const jawGeo = new THREE.SphereGeometry(0.09, 24, 16);
    jawGeo.applyMatrix4(new THREE.Matrix4().makeScale(0.88, 0.6, 0.8));
    P(addMesh(jawGeo, 0, 1.64, 0.02), root);

    // ── CUELLO ────────────────────────────────────────────────────────────────
    P(cap(0.052, 0.05, 0, 1.575, 0), root);

    // ── TORSO (3 secciones con perfil trapezoidal) ────────────────────────────
    P(box(0.47, 0.30, 0.22, 0, 1.465,  0), root); // pecho ancho
    P(box(0.41, 0.18, 0.20, 0, 1.255,  0), root); // cintura
    P(box(0.38, 0.22, 0.20, 0, 1.055,  0), root); // abdomen bajo
    P(box(0.40, 0.22, 0.21, 0, 0.855,  0), root); // caderas

    // ── BRAZO IZQUIERDO ───────────────────────────────────────────────────────
    // hombro → codo (capsule): total = len + 2r
    P(cap(0.052, 0.21, -0.315, 1.375, 0,  0, 0,  0.30), root); // upper arm
    P(cap(0.040, 0.18, -0.390, 1.075, 0,  0, 0,  0.08), root); // forearm
    P(cap(0.038, 0.04, -0.422, 0.860, 0), root);                // mano

    // ── BRAZO DERECHO ─────────────────────────────────────────────────────────
    P(cap(0.052, 0.21,  0.315, 1.375, 0,  0, 0, -0.30), root);
    P(cap(0.040, 0.18,  0.390, 1.075, 0,  0, 0, -0.08), root);
    P(cap(0.038, 0.04,  0.422, 0.860, 0), root);

    // ── PIERNA IZQUIERDA ──────────────────────────────────────────────────────
    // muslo ancho → se estrecha en pantorrilla
    P(cap(0.088, 0.27, -0.108, 0.655, 0), root); // muslo
    P(cap(0.065, 0.28, -0.113, 0.240, 0,  0.04, 0, 0), root); // pantorrilla
    // pie como box aplanado
    const footL = box(0.098, 0.068, 0.215, -0.108, 0.0, 0.046);
    P(footL, root);

    // ── PIERNA DERECHA ────────────────────────────────────────────────────────
    P(cap(0.088, 0.27,  0.108, 0.655, 0), root);
    P(cap(0.065, 0.28,  0.113, 0.240, 0, -0.04, 0, 0), root);
    P(box(0.098, 0.068, 0.215, 0.108, 0.0, 0.046), root);

    return root;
}

// ── Mount: centrar, apoyar en suelo, poblar bodyMeshes ───────────────────────
function mountBody(group) {
    bodyMeshes.length = 0;
    group.traverse(child => {
        if (child.isMesh && child.material === bodyMat) bodyMeshes.push(child);
    });

    const box3  = new THREE.Box3().setFromObject(group);
    const size  = box3.getSize(new THREE.Vector3());
    const ctr   = box3.getCenter(new THREE.Vector3());

    group.position.x -= ctr.x;
    group.position.z -= ctr.z;
    group.position.y  = floor.position.y - box3.min.y + group.position.y;

    const midY = floor.position.y + size.y * 0.5;
    controls.target.set(0, midY, 0);
    camera.position.set(0, midY + size.y * 0.05, size.y * 1.55 + 0.8);
    controls.update();

    scene.add(group);
    setLoader(false);

    window.__bodymap.ready = true;
    window.dispatchEvent(new CustomEvent('bodymap:ready', { detail: { bodyMeshes } }));
}

function setLoader(v) {
    const el = document.getElementById('bodymap-loader');
    if (el) el.style.display = v ? 'flex' : 'none';
}

// ── Cargar modelo ─────────────────────────────────────────────────────────────
if (CFG.modelUrl) {
    setLoader(true);
    new GLTFLoader().load(
        CFG.modelUrl,
        (gltf) => {
            gltf.scene.traverse(c => {
                if (!c.isMesh) return;
                c.material = bodyMat;
                c.castShadow = c.receiveShadow = true;
            });
            mountBody(gltf.scene);
        },
        undefined,
        () => mountBody(buildHumanoid())
    );
} else {
    mountBody(buildHumanoid());
}

// ── Resize ────────────────────────────────────────────────────────────────────
new ResizeObserver(() => {
    const w = canvas.clientWidth, h = canvas.clientHeight;
    if (!w || !h) return;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h, false);
}).observe(canvas);

// ── Render loop ───────────────────────────────────────────────────────────────
(function animate() {
    requestAnimationFrame(animate);
    controls.update();
    window.__bodymap.renderHooks.forEach(fn => fn());
    renderer.render(scene, camera);
})();
