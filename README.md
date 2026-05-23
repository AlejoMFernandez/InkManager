<div align="center">

# 🖋️ InkManager

### Digital Studio System para estudios de tatuaje

**Un body map 3D rotable donde marcás cada tatuaje en su posición exacta.**
Construido en PHP vanilla + Three.js. Sin framework, sin Composer, sin build step.

[![Live demo](https://img.shields.io/badge/▶_Live_demo-inkmanager.up.railway.app-ef4444?style=for-the-badge)](https://inkmanager.up.railway.app)
[![GitHub](https://img.shields.io/badge/GitHub-AlejoMFernandez/InkManager-181717?style=for-the-badge&logo=github)](https://github.com/AlejoMFernandez/InkManager)

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Three.js](https://img.shields.io/badge/Three.js-r169-000000?logo=three.js&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-CDN-06B6D4?logo=tailwindcss&logoColor=white)
![Railway](https://img.shields.io/badge/Deployed_on-Railway-0B0D0E?logo=railway&logoColor=white)

</div>

<!--
⬇️ AGREGAR: hero shot del body map 3D acá
Capturá una pantalla del cliente show con el body map visible + 2-3 markers rojos.
Guardala en docs/screenshots/hero.png y descomentá la línea de abajo:
-->
<!-- ![Hero](docs/screenshots/hero.png) -->

---

## 🎯 Try it now

> **No setup needed.** Entrá, logueate y jugá con el body map 3D.

```
🌐 https://inkmanager.up.railway.app/login
📧 admin@inkmanager.com
🔑 admin123
```

Después de loguearte, **andá a Clientes → cualquier cliente → click en el cuerpo 3D** para agregar un tatuaje. Ver [`DEMO.md`](./DEMO.md) para el tour completo.

> ⚠️ Free tier de Railway → la primera carga puede tardar ~10s (cold start). Las fotos subidas se borran en cada redeploy.

---

## ✨ Features

| Feature | Detalle |
|---|---|
| 🧍 **Body Map 3D** | Modelo humano rotable construido con Three.js. Click sobre el cuerpo → marker pulsante rojo persistente. GLTF loader con fallback a humanoid procedural (`CapsuleGeometry` + `MeshPhysicalMaterial`). 4-point studio lighting. |
| 👤 **CRM de clientes** | Listado paginado con búsqueda fuzzy, ficha individual con galería de tatuajes, historial, Instagram, notas, próximos turnos. |
| 📅 **Calendario de turnos** | FullCalendar 6 con dark theme custom, drag & drop reagendado vía AJAX, 4 estados con color (agendado / confirmado / hecho / cancelado). |
| 📊 **Dashboard** | KPIs con counter animations (IntersectionObserver + easing), hero banner con saludo dinámico según hora, próximos turnos en vivo. |
| 🎨 **Identidad de marca** | Splash intro animado (1× por sesión), tipografía editorial Bebas Neue + Inter, monograma SVG con `stroke-dasharray` animation + ink drop, noise texture inline. |
| 🔒 **Auth + Security** | Sessions con cookies HttpOnly + Secure (HTTPS detection), CSRF tokens con `hash_equals` timing-safe, password hashing bcrypt cost 12. |
| 📸 **Upload de fotos** | `finfo` MIME validation real (no solo extensión), hashed filenames, 5MB limit, servidor-side. |

---

## 🧱 Stack

| Capa | Tech | Por qué |
|------|------|---------|
| Backend | **PHP 8.2 vanilla MVC** | Sin framework, sin Composer. Router regex custom, base Controller/Model propios. Demuestra fundamentos sin abstracciones. |
| Database | **MySQL 8** via PDO | Prepared statements en TODO el codebase. Cero string concatenation en queries. |
| Frontend 3D | **Three.js r169** | ES modules con importmap (sin bundler). |
| Calendar | **FullCalendar 6** | Drag & drop nativo, AJAX para persistir cambios. |
| Styling | **Tailwind CDN** + brand stylesheet custom | Sin build step. Tipografía Google Fonts. |
| Deploy | **Railway** (PHP built-in server + MySQL plugin) | Free tier. CI/CD desde GitHub. |

**Cero build step. Push to deploy. La carpeta `public/` es exactamente lo que sirve.**

---

## 🎨 Highlights técnicos

### Body Map 3D

- **Procedural humanoid** con `CapsuleGeometry` para extremidades suaves + `MeshPhysicalMaterial` (clearcoat 0.22) para look pulido.
- **4-point studio lighting**: key warm (0xfff0d0) + fill cool (0x6090ff) + rim red signature (0xff1a0a) + ground.
- **Inverted-hull outline** via `BackSide` material clone escalado 1.045 — efecto cel-shading sin shader custom.
- **Race condition resuelto** entre `bodymap.js` (sync scene init) y `markers.js` (async event-driven) con `window.__bodymap.ready` flag + `bodymap:ready` CustomEvent.
- **Raycaster** detecta intersección con el cuerpo en click, calcula normal de superficie, persiste posición 3D + normal en DB → marker renderizado en la misma posición en próxima visita.

### Brand identity

- **Splash intro** de 1.7s con `sessionStorage` (solo 1ª visita por sesión, no molesta en navegación interna).
- **Monograma SVG** con `stroke-dasharray` animation (líneas "se dibujan") + ink drop con `inkDrip` keyframe.
- **Tipografía editorial**: Bebas Neue (display, headings) + Inter (body) desde Google Fonts.
- **Noise texture** inline SVG en data-URI fijado al body — da grain de estudio sin pedir un asset extra.

### Routing & Auth

- **Regex router** con named capture groups (`{id}` → `(?P<id>[^/]+)`).
- **Middleware system** simple: `['auth']` en una ruta aplica `Auth::requireAuth()` antes del controller.
- **CSRF** con `hash_equals` (timing-safe).
- **BASE_URL auto-detection**: misma codebase funciona en XAMPP subdirectory (`/Tatoo`) y Railway root (`/`).

📖 **¿Te interesa el detalle del deploy?** → [`DEPLOYMENT.md`](./DEPLOYMENT.md) cuenta los 4 problemas que tuvimos que resolver para llevarlo de XAMPP a Railway.

---

## 💻 Setup local (XAMPP)

```bash
# 1. Cloná en C:\xampp\htdocs\Tatoo
git clone https://github.com/AlejoMFernandez/InkManager.git C:/xampp/htdocs/Tatoo

# 2. Copiá el .env de ejemplo
cp .env.example .env

# 3. Arrancá XAMPP (Apache + MySQL) y andá a:
http://localhost/Tatoo/setup.php

# 4. Login en:
http://localhost/Tatoo/login   # admin@inkmanager.com / admin123
```

## 🚀 Deploy en Railway

1. Fork del repo a tu GitHub.
2. [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo**.
3. Agregá un plugin **MySQL** al proyecto.
4. En el servicio web → **Variables** → agregá referencia a `MYSQL_URL` apuntando a `${{ MySQL.MYSQL_URL }}`, más:
   ```
   APP_ENV=production
   APP_DEBUG=false
   SETUP_TOKEN=<algo_random_largo>
   ```
5. Esperá el deploy y andá a `https://<tu-app>.up.railway.app/setup.php?token=<TU_SETUP_TOKEN>` (una sola vez).
6. Login en `/login`.

**Más detalle técnico del deploy en [`DEPLOYMENT.md`](./DEPLOYMENT.md)** — incluyendo Railpack vs Nixpacks, el wrapper de `setup.php` para doc-root mismatch, y por qué `$_ENV` quedaba vacío en PHP built-in server.

---

## 📂 Estructura

```
.
├── app/
│   ├── Controllers/    # ClientesController, TurnosController, TatuajesController, AuthController
│   ├── Core/           # Router, Database, Auth, Controller, Model base
│   ├── Models/         # Cliente, Turno, Tatuaje, Usuario
│   └── Views/          # layouts/, partials/, clientes/, turnos/, dashboard.php, login.php
├── config/
│   └── database.php    # ENV-aware (Railway MYSQL_URL / vars individuales / .env local)
├── database/
│   └── schema.sql      # CREATE TABLE para usuarios, clientes, tatuajes, turnos, estilos
├── public/
│   ├── index.php       # Front controller + static-file pass-through para php -S
│   ├── setup.php       # Wrapper para Railway (doc-root = public/)
│   └── assets/
│       ├── css/brand.css   # Brand identity stylesheet
│       ├── js/bodymap.js   # Three.js scene + procedural humanoid + lighting
│       ├── js/markers.js   # Raycaster, markers, modals, AJAX
│       └── models/         # body.glb opcional (fallback procedural si no existe)
├── index.php           # DirectoryIndex para XAMPP — reenvía a public/index.php
├── composer.json       # Marker para que Railpack detecte PHP (sin dependencias)
├── nixpacks.toml       # Build config alternativo
├── Procfile            # Start command para Railway
└── setup.php           # Instalador idempotente (token-gated en prod)
```

---

## 📸 Screenshots

<!-- Una vez que captures las imágenes en docs/screenshots/, descomentá esto -->
<!--
| Login | Dashboard | Body Map 3D |
|---|---|---|
| ![Login](docs/screenshots/login.png) | ![Dashboard](docs/screenshots/dashboard.png) | ![Body Map](docs/screenshots/bodymap.gif) |
-->

_Screenshots y GIF del body map 3D próximamente._

---

## 📝 License

MIT — libre uso, modificación y distribución.

---

<div align="center">

Hecho con 🖋️ y PHP por [**@AlejoMFernandez**](https://github.com/AlejoMFernandez)

</div>
