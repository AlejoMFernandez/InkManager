# Instagram + Threads — Launch Content

> Instagram = visual primero, texto después. Threads = más conversacional, texto primero. Mismo proyecto, dos formatos distintos.

---

## 📱 Instagram — Carrusel de 7 slides

**Formato:** Cuadrado 1080×1080 px o vertical 1080×1350 px (recomendado: vertical, más real estate).

**Herramienta para diseñar:** [Canva](https://canva.com) (template gratis "Tech carousel") o Figma si querés más control.

**Paleta del carrusel:**
- Fondo: `#0a0a0a` (negro tattoo studio)
- Acento: `#ef4444` (rojo signature)
- Texto: `#ffffff` + `#9ca3af` (gris medio)
- Tipografía display: **Bebas Neue** (gratis en Google Fonts)
- Tipografía body: **Inter**

### 🎨 Slide 1 — Cover (el hook)

```
┌─────────────────────────────────┐
│                                 │
│   [GIF/Screenshot del          │
│    body map 3D con un          │
│    marker rojo, ocupando       │
│    60% del slide]              │
│                                 │
│   ─────────                    │
│                                 │
│   INK                          │
│   MANAGER.                     │
│                                 │
│   Marcá cada tatuaje en su     │
│   posición exacta sobre un     │
│   modelo 3D rotable.           │
│                                 │
│   → desliz á                   │
└─────────────────────────────────┘
```

**Texto:** "INK MANAGER." gigante en Bebas Neue. Subtítulo en Inter regular.

### 🎨 Slide 2 — El problema

```
EL PROBLEMA

Los gestores para estudios de
tatuaje hoy son básicamente
Excel con luz.

❌ Sin historial visual del cliente
❌ Sin forma de saber QUÉ tatuaje
   tiene Y DÓNDE
❌ Diseñados para escritorio,
   ignorando mobile
```

### 🎨 Slide 3 — La solución (hero feature)

```
LA SOLUCIÓN

Un body map 3D rotable.

[GIF del cuerpo rotando con markers]

→ Click sobre el cuerpo →
  agregás un tatuaje en esa
  posición exacta.

→ Click sobre un marker →
  ves el detalle.
```

### 🎨 Slide 4 — Features más

```
TODO LO DEMÁS

📋 CRM con búsqueda + paginación
📅 Calendario drag & drop
📊 Dashboard con KPIs animados
📸 Upload de fotos con validación
🎨 Identidad de marca completa
```

### 🎨 Slide 5 — Stack visual

```
EL STACK

[Logos centrados en grid 2x3]

PHP 8.2  ·  MySQL 8
Three.js  ·  Tailwind
FullCalendar  ·  Railway

Sin framework.
Sin Composer.
Sin build step.
```

### 🎨 Slide 6 — Behind the scenes

```
LO QUE MÁS ME COSTÓ

NO fue construir el body map 3D.
NO fue el calendario drag & drop.
NO fue el CRM.

FUE EL DEPLOY.

4 problemas en cadena de
XAMPP → Railway.

Escribí todo en el repo →
swipe para el link.
```

### 🎨 Slide 7 — CTA

```
PROBALO YA

🌐 inkmanager.up.railway.app

Login:
admin@inkmanager.com
admin123

────────

¿Sos tatuador?
¿Sos dev?

Romper el demo, fork del repo,
todo bienvenido.

→ Link en bio
```

---

## ✍️ Caption del post (Instagram)

```
🖋️ Lancé InkManager — un sistema de gestión para estudios de tatuaje con body map 3D rotable.

Hace 6 semanas no había tocado Three.js. Hoy lo deployé funcionando 24/7.

→ Marcá cada tatuaje en su posición exacta sobre un modelo humano 3D
→ CRM de clientes + calendario drag & drop
→ Identidad visual completa (splash intro, tipografía editorial, micro-animaciones)

Construido en PHP vanilla + Three.js. Sin framework, sin build step.

Probalo (login en el slide 7):
inkmanager.up.railway.app

Código abierto en GitHub → link en bio.

—

PD: Lo que más me costó NO fue el código. Fue el deploy de XAMPP a Railway. Si te pasó algo parecido, escribí todo el journey en el repo (DEPLOYMENT.md).

#desarrolloweb #php #threejs #webdev #fullstackdeveloper #programacion #tatuajes #tattoostudio #buildinpublic #softwaredeveloper #devlife #portfolioproject #codenewbie #aprenderaprogramar #buenosaires
```

> 💡 **Hashtag mix:** 8 hashtags grandes (>500k posts) + 4 medianos (50-500k) + 3 chicos/nicho (<50k). Eso te da máximo reach sin que el algoritmo te marque como spammer.

---

## 🧵 Threads — 5 posts encadenados

> Threads es más conversacional. Cada post es independiente pero linkea al siguiente. Tono: como contarle a un amigo dev en un asado.

### Thread 1 — El hook

```
🖋️ Acabo de lanzar InkManager.

Es un gestor para estudios de tatuaje con un body map 3D rotable donde marcás cada tatuaje en su posición exacta sobre el cuerpo.

[GIF del body map]

Demo → inkmanager.up.railway.app
```

### Thread 2 — El "por qué"

```
La idea salió de algo simple:

Los gestores que usan los estudios de tatuaje hoy son Excel con CSS.

Pensé: "si un cliente vuelve a hacerse su quinto tatuaje, ¿no estaría bueno ver en un modelo 3D dónde están los otros cuatro?"

Y eso fue todo. Empecé a tirar líneas.
```

### Thread 3 — Stack

```
Stack:
• PHP 8.2 vanilla MVC (sí, sin framework. Router custom + Controller base)
• Three.js para el body map (raycaster + procedural humanoid + 4-point lighting)
• MySQL 8 con PDO
• Tailwind CDN
• FullCalendar 6 para los turnos
• Railway free tier

Cero build step. Push to deploy.
```

### Thread 4 — La parte que duele

```
Lo más interesante fue el deploy.

Construir el body map 3D me tomó 2 días.
Llevarlo de XAMPP a Railway me tomó 4 horas y 4 problemas en cadena:

1. Railway detectó el proyecto como Staticfile en vez de PHP
2. $_ENV vacío en PHP built-in server (variables_order...)
3. setup.php invisible por doc-root mismatch
4. 403 Forbidden volviendo al local

Cada uno me hizo entender un nivel más profundo del stack.
```

### Thread 5 — Cierre + CTA

```
Escribí todo el deployment journey en el repo, por si a alguien le sirve:

github.com/AlejoMFernandez/InkManager/blob/main/DEPLOYMENT.md

Demo:
🌐 inkmanager.up.railway.app
📧 admin@inkmanager.com
🔑 admin123

Si sos tatuador o dev, dale para adelante.
Feedback o PRs bienvenidos.
```

---

## 🎬 Si querés hacer un Reel / TikTok en vez del carrusel

**Hook (3s):** Cámara del cliente clickeando el cuerpo 3D, marker rojo aparece. Audio: trending sound minimalista.

**Body (15-20s):** Voz en off corta:
> "Construí un gestor para estudios de tatuaje con body map 3D. Hace 6 semanas no sabía Three.js. Hoy lo deployé en Railway. Link en bio."

**Mostrar mientras hablás:**
- Body map rotando (5s)
- Dashboard con KPIs animados (3s)
- Calendario drag & drop (3s)
- Pantalla de Visual Studio Code mostrando el repo (3s)

**Caption + hashtags iguales al carrusel.**

---

## 📊 Checklist de timing

- **Lunes/Martes 7-9pm** → mejor horario IG para tech audience en LATAM
- **Threads:** funciona cualquier momento, pero **postear los 5 con 30-60s entre cada uno** para que se vean encadenados
- **Reposteá las stories** con polls "¿qué feature te gustaría que agregue?" para generar conversación
- **DM a 5-10 amigos** con el link 5min antes de postear → primeros likes en burst aumentan el reach orgánico
