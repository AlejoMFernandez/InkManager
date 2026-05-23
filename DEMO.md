# 🎬 InkManager — Demo Tour

> Tour guiado para ver el sistema en 5 minutos. Hacelo en este orden para apreciar todo.

🌐 **Demo en vivo:** [inkmanager.up.railway.app](https://inkmanager.up.railway.app)

## 🔑 Credenciales

```
Email:        admin@inkmanager.com
Password:     admin123
```

> ⚠️ **Demo público**: cualquier visitante usa el mismo login. Los datos que crees son visibles para todos.
> 🔁 **Free tier**: Railway duerme el servicio tras ~5min de inactividad → primer hit puede tardar ~10-15s.

---

## 1️⃣ Splash intro (3s)

Al entrar al login por primera vez en la sesión, vas a ver el splash animado:
- Monograma SVG con líneas que "se dibujan"
- Gota de tinta cae
- Nombre "INK MANAGER" con letter-spacing animado
- Tagline + barra decorativa

> 💡 El splash usa `sessionStorage` para mostrarse solo una vez por sesión y no molestar en navegación interna.

---

## 2️⃣ Login (10s)

Entrá con las credenciales. Mientras escribís, fijate:
- Layout split-screen (panel izquierdo brand hero + derecho form)
- Scan-line horizontal animada cruzando la pantalla
- Corner brackets decorativos en el form
- Press del botón con `active:scale-95` micro-feedback

---

## 3️⃣ Dashboard (30s)

Después del login caés en el dashboard. Mirá:

- **Hero banner** con saludo dinámico según hora ("Buenas tardes, Admin.")
- **KPI cards** con números en Bebas Neue que se animan de 0 → valor real al cargar (counter animation con `requestAnimationFrame` + easing).
- **Gradient hover** en cada card (border que se ilumina).
- **Mini bar chart** que crece con stagger delay de izquierda a derecha.
- **Top bar** con status pill verde animado ("Studio · Online") y la fecha en formato monospace.

---

## 4️⃣ Clientes (60s) — _Lo importante_

**Andá a Clientes** (sidebar izquierdo).

1. Vas a ver el listado con búsqueda en vivo.
2. Hover sobre una fila → aparece accent rojo en el borde izquierdo + slide del avatar.
3. **Click en cualquier cliente** para entrar a su ficha.

### Dentro de la ficha del cliente

Esto es **lo que hace único al proyecto**:

1. Vas a ver un **modelo humano 3D rotable** en el centro de la pantalla.
2. **Arrastrá con el mouse** para rotarlo. **Scroll** para zoom.
3. **Click sobre cualquier parte del cuerpo** → se abre un modal "Nuevo tatuaje":
   - Estilo (dropdown)
   - Fecha
   - Precio
   - Sesiones
   - Notas
   - **Foto** (drag & drop o click)
4. Guardalo → vas a ver un **marker rojo pulsante** en la posición exacta donde clickeaste.
5. **Click sobre el marker** → modal de detalle con foto + datos.
6. Rotá el modelo: el marker queda anclado a esa posición del cuerpo.

> 🧠 Bajo el capó: cuando clickeás, un raycaster de Three.js calcula la intersección con el mesh + la normal de la superficie. Esos 6 valores (`pos_x/y/z`, `normal_x/y/z`) se guardan en MySQL. Al cargar el cliente, se renderiza el marker en esa posición exacta.

---

## 5️⃣ Calendario de turnos (30s)

**Andá a Turnos** (sidebar).

- FullCalendar 6 con dark theme custom (las CSS variables `--fc-*` overriden).
- **Click en cualquier slot** → modal nuevo turno con la fecha pre-cargada.
- **Drag & drop un turno** a otro día/hora → AJAX al backend, persiste el cambio sin recargar.
- **Resize** un turno (arrastrar el borde) para cambiar duración.
- 4 colores según estado: azul (agendado), verde (confirmado), gris (hecho), rojo (cancelado).

---

## 6️⃣ Mobile (15s)

Reducí la ventana del navegador a <640px. Fijate:
- Sidebar colapsa y se convierte en hamburger
- Top bar muestra el logo en vez del título
- KPI grid pasa de 4 columnas a 2
- Body map 3D mantiene aspect ratio y se hace touch-friendly

---

## 🐛 ¿Encontraste un bug?

Abrime un [issue en GitHub](https://github.com/AlejoMFernandez/InkManager/issues) — pull requests bienvenidos.

---

← Volver al [README](./README.md)
