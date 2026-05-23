# LinkedIn — Launch Post

> Lanzamiento de **InkManager**. Tono: profesional pero personal. Foco en problem-solving + lecciones aprendidas (lo que recruiters quieren leer).
>
> **Cuándo postear:** martes o miércoles, 9-11am hora local. Es cuando LinkedIn tiene más engagement orgánico.
>
> **Imagen a adjuntar:** el GIF del body map 3D (es el hook visual). LinkedIn soporta GIFs hasta 5MB.
>
> **Largo:** ~450 palabras. LinkedIn corta a las ~210 caracteres con "ver más" — los primeros 2 párrafos tienen que enganchar.

---

## 📝 Copy

🖋️ Acabo de lanzar **InkManager** — un sistema de gestión para estudios de tatuaje con un **body map 3D rotable** donde marcás cada tatuaje en su posición exacta sobre el cuerpo.

Lo construí desde cero en **PHP 8.2 vanilla + Three.js**. Sin frameworks, sin Composer, sin build step. Solo router custom, MVC propio y un modelo humano 3D que responde a clicks.

🌐 Live demo → https://inkmanager.up.railway.app
💾 Código → https://github.com/AlejoMFernandez/InkManager

---

**¿Por qué este proyecto?**

Quería un caso de uso real con **diferencial visual fuerte**. Los gestores para estudios de tatuaje que existen hoy son básicamente Excel con luz. Pensé: si un cliente vuelve a hacerse el quinto tatuaje, ¿no estaría bueno ver en un modelo 3D dónde están los otros cuatro? Eso fue el punto de partida.

---

**Lo que aprendí (la parte interesante)**

Migrarlo de **XAMPP local a Railway** me tomó más tiempo que construir el dashboard entero. Tuve que resolver **4 problemas en cadena**:

1️⃣ El builder de Railway lo detectó como sitio estático e instaló Caddy en vez de PHP. Fix: `composer.json` mínimo como marker, aunque no use Composer.

2️⃣ Login fallaba con "Error de conexión a la base de datos" en producción. ¿La causa? **`$_ENV` no se popula por defecto en PHP built-in server** (variables_order = "GPCS"). Las variables de Railway existían pero solo accesibles vía `getenv()`. Fix: merge de ambos al boot.

3️⃣ `/setup.php` daba 404. El doc-root en Railway es `public/`, así que el archivo en la raíz era invisible. Fix: wrapper que reenvía al original.

4️⃣ De vuelta en XAMPP local: 403 Forbidden en la raíz. El `.htaccess` no rewriteaba directorios → Apache no encontraba DirectoryIndex. Fix: `index.php` raíz que delega al front controller.

Cada uno parecía tonto en aislado, pero juntos me hicieron entender **un nivel más profundo del stack**. Esa es la diferencia entre "uso PHP" y "sé PHP".

📖 Escribí todo el deployment journey en el repo si te interesa el detalle técnico: https://github.com/AlejoMFernandez/InkManager/blob/main/DEPLOYMENT.md

---

**Stack:**
🔹 Backend: PHP 8.2 vanilla MVC + MySQL 8 (PDO prepared statements)
🔹 Frontend 3D: Three.js r169 (raycaster + procedural humanoid + 4-point lighting)
🔹 UI: Tailwind CDN + tipografía editorial (Bebas Neue + Inter)
🔹 Calendar: FullCalendar 6 con drag & drop
🔹 Deploy: Railway free tier

---

Si conocés a algún tatuador que quiera probarlo, o sos dev y querés mirar el código, estás invitado/a a romperlo 🤝.

Feedback bienvenido.

#PHP #ThreeJS #WebDevelopment #FullStack #JavaScript #MySQL #OpenSource #BuildInPublic #PortfolioProject #TattooStudio

---

## 🎯 Variantes / tweaks según audiencia

### Versión más corta (si querés más reach)

> 🖋️ Lancé InkManager: un gestor para estudios de tatuaje con body map 3D rotable donde marcás cada tatuaje en su posición exacta.
>
> Construido en PHP 8.2 vanilla + Three.js. Sin framework, sin build step.
>
> Lo que más me llevó tiempo no fue el código — fue el deploy. 4 problemas en cadena de XAMPP → Railway. Lo escribí todo acá:
> [link]
>
> Demo → [link]
> Repo → [link]
>
> #PHP #ThreeJS #WebDev

### Versión más "personal brand"

Cambiar el primer párrafo por algo más vulnerable:

> Hace 6 semanas no había escrito una línea de Three.js. Hace 2 días nunca había deployado PHP a Railway. Hoy lanzo InkManager, mi proyecto más completo hasta ahora.

(Funciona mejor si sos junior/mid — vulnerabilidad + crecimiento gana engagement)

---

## 📊 Checklist antes de postear

- [ ] GIF del body map subido (el hook visual es clave)
- [ ] Link directo a `inkmanager.up.railway.app` testeado en mobile
- [ ] Credenciales demo (`admin@inkmanager.com` / `admin123`) verificadas
- [ ] Avisar a 3-5 conexiones cercanas antes de postear para que comenten en los primeros 30min (esto multiplica el reach orgánico)
- [ ] Respondé a TODOS los comments las primeras 2hs — algoritmo de LinkedIn premia eso
