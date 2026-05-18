# 🖋️ InkManager — Digital Studio System

> Sistema de gestión para estudios de tatuaje con **body map 3D interactivo**.
> Marcá cada tatuaje en su posición exacta sobre un modelo humano rotable construido con Three.js.

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Three.js](https://img.shields.io/badge/Three.js-r169-000000?logo=three.js&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-CDN-06B6D4?logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Features

- **Body Map 3D** — Modelo humano rotable (procedural + GLTF fallback) con raycaster click-to-mark, markers pulsantes persistentes y modal de detalle por tatuaje.
- **CRM de clientes** — Listado con búsqueda + paginación, ficha con galería de tatuajes, primera visita, Instagram, notas.
- **Calendario de turnos** — FullCalendar 6 con dark theme custom, drag & drop reagendado, estados con color (agendado / confirmado / hecho / cancelado).
- **Dashboard** — KPIs con counter animations, hero banner con saludo dinámico, próximos turnos en vivo.
- **Identidad de marca** — Splash intro animado, tipografía editorial (Bebas Neue + Inter), monograma SVG con stroke-draw animation.
- **Auth segura** — Sessions con cookies HttpOnly + Secure (HTTPS detection), CSRF tokens, password hashing con bcrypt cost 12.
- **Upload de fotos** — `finfo` MIME validation, hashed filenames, 5MB limit.

---

## 🧱 Stack

| Capa | Tech |
|------|------|
| Backend | PHP 8.2 vanilla MVC (sin Composer) |
| Database | MySQL 8 + PDO prepared statements |
| Frontend | Tailwind CDN + Three.js r169 (ES modules + importmap) |
| Calendar | FullCalendar 6 |
| Routing | Custom regex router en `app/Core/Router.php` |
| Sin build step. Sin framework. Cero magia. |

---

## 🚀 Deploy en Railway

1. Forkeá / cloneá el repo a tu GitHub.
2. Entrá a [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo**.
3. Una vez creado el servicio, agregá un plugin **MySQL** desde el dashboard de Railway.
4. En **Variables** del servicio web, agregá:
   ```
   APP_ENV=production
   APP_DEBUG=false
   SETUP_TOKEN=algun_string_random_largo_acá
   ```
   (Las vars `MYSQLHOST`, `MYSQLUSER`, etc. las inyecta Railway automáticamente al linkear el plugin de MySQL.)
5. Esperá el primer deploy → andá a `https://tu-app.up.railway.app/setup.php?token=TU_SETUP_TOKEN` UNA vez para crear las tablas + admin user.
6. Login con `admin@inkmanager.com` / `admin123` — **cambiá la contraseña**.

⚠️ **Filesystem ephemeral**: los uploads de fotos se pierden en cada redeploy en Railway free tier. Para persistencia real, agregá Railway Volumes o un bucket S3/R2.

---

## 💻 Setup local (XAMPP)

```bash
# 1. Cloná en htdocs/Tatoo
git clone https://github.com/tu-user/inkmanager.git C:/xampp/htdocs/Tatoo

# 2. Copiá el .env y editá si hace falta
cp .env.example .env

# 3. Arrancá XAMPP (Apache + MySQL) y andá a:
http://localhost/Tatoo/setup.php

# 4. Borrá setup.php (o dejalo — en local no expone nada crítico) y entrá:
http://localhost/Tatoo/login
```

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
│   ├── assets/
│   │   ├── css/brand.css   # Brand identity stylesheet
│   │   ├── js/bodymap.js   # Three.js scene + procedural humanoid + lighting
│   │   ├── js/markers.js   # Raycaster, markers, modals, AJAX
│   │   └── models/         # body.glb opcional (fallback procedural si no existe)
├── nixpacks.toml       # Railway build config
├── Procfile            # Start command
└── setup.php           # Instalador idempotente
```

---

## 🎨 Highlights técnicos

### Body Map 3D
- Procedural humanoid con `CapsuleGeometry` + `MeshPhysicalMaterial` (clearcoat 0.22).
- 4-point studio lighting (key warm + fill cool + rim red signature + ground).
- Inverted-hull outline effect via `BackSide` material clone (scale 1.045).
- Race condition resuelto entre `bodymap.js` (sync scene init) y `markers.js` (async event-driven) con `window.__bodymap.ready` + `bodymap:ready` CustomEvent.

### Brand identity
- Splash intro de 1.7s con `sessionStorage` (solo 1ª visita por sesión).
- Monograma SVG con `stroke-dasharray` animation + ink drop.
- Tipografía: Bebas Neue (display) + Inter (body) desde Google Fonts.
- Background con noise texture inline SVG + radial gradients duales.

### Routing
- Regex-based router con named capture groups para params (`{id}`).
- Middleware system simple (`['auth']` aplica `Auth::requireAuth()`).
- CSRF token comparison con `hash_equals` (timing-safe).

---

## 📸 Screenshots

_(Agregá acá GIFs del body map rotando + screenshots del dashboard y login)_

---

## 📝 License

MIT — usá esto para tu portfolio, modificalo, vendelo si querés.

Hecho con 🖋️ por [@tu-handle](https://github.com/tu-handle).
