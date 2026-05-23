# 🚂 Deployment Journey — XAMPP → Railway

> 4 problemas reales que tuvimos que resolver para llevar **InkManager** de funcionar en XAMPP local a un servidor PHP corriendo 24/7 en Railway.
>
> Si estás migrando una app PHP "tradicional" (front controller + Apache + .htaccess) a un host moderno con Docker + builder automático, probablemente te vas a tropezar con la mayoría de estos.

---

## 🎯 TL;DR

| # | Problema | Causa raíz | Fix |
|---|----------|-----------|-----|
| 1 | Build deployaba como `Staticfile` (Caddy), no PHP → `php: command not found` | Railpack busca `composer.json` o `index.php` en la raíz; el nuestro estaba en `public/` | Crear `composer.json` mínimo con `require php: ^8.2` |
| 2 | `DB Error: Error de conexión` aunque `MYSQLHOST` estaba seteado en Railway | PHP built-in server no popula `$_ENV` con vars del proceso (variables_order = `GPCS`) | Mergear `getenv()` en `$_ENV` en el bootstrap |
| 3 | `/setup.php?token=...` daba 404 | Doc-root en Railway es `public/`; `setup.php` vive en la raíz → invisible | Wrapper `public/setup.php` que hace `require dirname(__DIR__) . '/setup.php'` |
| 4 | `/Tatoo/` (root) daba 403 Forbidden en XAMPP local | `.htaccess` excluía directorios del rewrite + no había DirectoryIndex en la raíz | Crear `index.php` raíz que reenvía a `public/index.php` |

Si tenés tiempo solo para uno, leé el #2 — es el más insidioso porque silencioso.

---

## 1️⃣ Railpack detecta el proyecto como Staticfile

### Síntoma

Push a `main`, build pasa, deploy intenta arrancar:

```
↳ Detected Staticfile
↳ Using staticfile root dir: public

Starting Container
/bin/bash: line 1: php: command not found
/bin/bash: line 1: php: command not found
...
```

El container reinicia infinitamente porque el `Procfile` ejecuta `php -S 0.0.0.0:$PORT -t public public/index.php`, pero PHP nunca fue provisionado.

### Causa

Railway empezó a usar **Railpack** (su builder propio) en lugar de Nixpacks. Railpack detecta el tipo de proyecto buscando archivos marker en la **raíz del repo**:

- `composer.json` → PHP project
- `index.php` en root → PHP project
- `package.json` → Node project
- `Procfile` solo → Staticfile (Heroku-style buildpack)

Nuestro `index.php` vive en `public/` (convención de frameworks modernos tipo Laravel/Symfony), así que Railpack no encontró ningún marker PHP → defaulteó a `Staticfile` → instaló Caddy.

### Fix

`composer.json` mínimo en la raíz. No necesitamos Composer realmente — solo el archivo como señal:

```json
{
    "name": "inkmanager/studio",
    "type": "project",
    "require": {
        "php": "^8.2",
        "ext-pdo": "*",
        "ext-pdo_mysql": "*",
        "ext-fileinfo": "*",
        "ext-session": "*",
        "ext-json": "*"
    },
    "config": {
        "platform": {
            "php": "8.2"
        }
    }
}
```

Después del siguiente push, el build log mostró:

```
↳ Detected PHP
  Packages
  ──────────
  php   │  8.2.x
```

### Lección

> Los builders automáticos hacen heurísticas. Si tu proyecto no sigue convenciones mainstream (Laravel, Symfony, WordPress), agregale un marker explícito para evitar dolor de cabeza.

---

## 2️⃣ `$_ENV` está vacío en PHP built-in server

### Síntoma

Login en producción tira `DB Error: Error de conexión a la base de datos`. Las variables están seteadas en Railway (`MYSQLHOST`, `MYSQLUSER`, etc., referenciadas desde el plugin MySQL). Setear `APP_DEBUG=true` para ver el error real... **tampoco hace nada**. Sigue viéndose el mensaje genérico de producción.

### Causa

Esta me la sé ahora pero no la había vivido. Dato técnico:

> PHP tiene un setting `variables_order` que controla qué arrays superglobales se populan. El default es **`GPCS`**:
> - **G** → `$_GET`
> - **P** → `$_POST`
> - **C** → `$_COOKIE`
> - **S** → `$_SERVER`
> - **E** → `$_ENV` ⚠️ **NO incluida por default**

Significa que `$_ENV` está vacío salvo lo que vos le inyectes a mano (por ejemplo, leyendo un archivo `.env`).

En XAMPP local funcionaba porque cargábamos `.env` al boot:
```php
foreach (file(ROOT_PATH . '/.env', ...) as $line) {
    [$k, $v] = explode('=', $line, 2);
    $_ENV[trim($k)] = trim($v);
}
```

Pero en Railway:
- No hay `.env` (está en `.gitignore`)
- Las vars del entorno del proceso (`MYSQLHOST`, `APP_DEBUG`, etc.) existen — pero solo accesibles via `getenv()`
- `$_ENV` quedaba vacío
- Todos mis `$_ENV['APP_DEBUG'] === 'true'` checks fallaban → mensaje genérico
- Todos mis `$_ENV['MYSQLHOST']` eran `null` → PDO intentaba `localhost` → fallaba

### Fix

Después de cargar el `.env` (si existe), mergear las vars del proceso:

```php
// .env (local) primero — gana en conflictos
if (file_exists(ROOT_PATH . '/.env')) { /* ... */ }

// getenv() (Railway/Docker/CI) llena lo que falta
foreach (getenv() as $k => $v) {
    if (!isset($_ENV[$k])) $_ENV[$k] = $v;
}
```

Aplicado en los 3 entry points: `public/index.php`, `setup.php`, `config/database.php`.

### Alternativas que descarté

- **Cambiar `variables_order` a `EGPCS` via `php.ini`** — funciona pero requiere modificar el config del runtime de Railway (no lo controlo directamente).
- **Usar `getenv($key)` en vez de `$_ENV[$key]` en toda la codebase** — sería más correcto pero implica tocar muchos archivos. El merge es un cambio de 1 línea con el mismo efecto.

### Lección

> `$_ENV` no es lo mismo que `getenv()`. En PHP built-in server (y en algunos PHP-FPM configs estrictos), `$_ENV` puede estar vacío incluso con vars del entorno seteadas. Si vas a usar `$_ENV` en tu código, populalo explícitamente al boot.

---

## 3️⃣ `setup.php` invisible por doc-root mismatch

### Síntoma

Después del fix #2, conexión a DB OK pero las tablas no existen. Voy a correr el setup:

```
GET https://inkmanager.up.railway.app/setup.php?token=...
→ 404 Página no encontrada
```

Logs muestran que la request llega pero el router responde 404.

### Causa

Mi start command:
```
php -S 0.0.0.0:$PORT -t public public/index.php
```

`-t public` define el **document root como `public/`**. Para el servidor, todo lo que está fuera de `public/` simplemente **no existe**.

- `setup.php` (en la raíz) → invisible
- El built-in server pasa la request al router (`public/index.php`)
- El router solo conoce las rutas `/login`, `/clientes`, etc. — no `/setup.php`
- Cae en el handler 404 default

En XAMPP local funcionaba porque Apache usa toda la carpeta `htdocs/Tatoo/` como doc-root y el `.htaccess` sirve `setup.php` directamente (porque es un archivo real, no rewritea).

### Fix

Wrapper en `public/setup.php` que reenvía al original:

```php
<?php
require dirname(__DIR__) . '/setup.php';
```

`__DIR__` adentro del archivo wrapper es `/app/public`, `dirname(__DIR__)` es `/app`, donde vive el `setup.php` real. PHP `require` ejecuta el archivo en su propio contexto — todos los `__DIR__` adentro del original siguen apuntando a la raíz (que es lo que necesita para acceder a `database/schema.sql`).

Beneficio del wrapper sobre mover el archivo: **un solo source of truth**. XAMPP sigue funcionando con `/Tatoo/setup.php` (sirve el real), Railway funciona con `/setup.php` (sirve el wrapper que requirea el real).

### Lección

> Cuando deployás una app con front controller pattern a un host con doc-root configurable, **revisá qué archivos están "encima" del doc-root**. Si necesitan ser web-accessibles (como un installer one-shot), o los movés adentro, o creás un wrapper.

---

## 4️⃣ `/Tatoo/` da 403 en Apache local

### Síntoma

Después de toda la odisea Railway, vuelvo a XAMPP para chequear que no rompí nada local:

```
GET http://localhost/Tatoo/
→ 403 Forbidden
You don't have permission to access this resource.
```

### Causa

El `.htaccess` original:

```apache
Options -MultiViews -Indexes
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

RewriteRule ^(.*)$ public/index.php [QSA,L]
```

La primera regla dice: **"si es archivo real (-f) O directorio (-d), no rewritees"** (`-` significa "no rewrite", `[L]` corta la cadena).

Cuando pido `/Tatoo/`:
1. `REQUEST_FILENAME` = `C:/xampp/htdocs/Tatoo/` (es directorio)
2. La condición `-d` matchea → no rewrite
3. Apache intenta servir el directorio
4. Busca un `DirectoryIndex` (por defecto `index.php`) → no existe en la raíz (está en `public/`)
5. `-Indexes` impide el listado → **403**

Esto siempre estuvo así. Nunca lo noté porque siempre entraba con URLs específicas (`/Tatoo/login`, `/Tatoo/dashboard`), que sí matchean la segunda regla del rewrite.

### Fix

`index.php` en la raíz que reenvía a `public/`:

```php
<?php
require __DIR__ . '/public/index.php';
```

Ahora Apache encuentra un DirectoryIndex válido y PHP se hace cargo del routing.

### Por qué NO rompe Railway

- Railway usa `php -S -t public` → doc-root es `public/` → el `index.php` raíz **no existe** desde la perspectiva del server.
- XAMPP usa `htdocs/` como doc-root → ve `Tatoo/index.php` como DirectoryIndex válido.

Otra vez: **un cambio que arregla XAMPP sin tocar Railway**, porque cada uno ve un subset distinto del filesystem.

### Lección

> El comportamiento por default de Apache + `.htaccess` para directorios es contraintuitivo. Si tu pattern es "front controller en subcarpeta", asegurate de tener un DirectoryIndex en la raíz, aunque sea un one-liner.

---

## 🧰 Stack final del deploy

```yaml
Builder:        Railpack v0.23
Detected:       PHP
Runtime:        PHP 8.2.31
Server:         php -S (built-in)
Doc-root:       public/
DB:             MySQL plugin (Railway)
Cost:           Free tier (~$0 + posible sleep tras 5min inactividad)
Build time:     ~45s
Deploy time:    ~10s
Cold start:     ~10s
```

## 📝 Archivos clave que terminaron en el repo por estos fixes

```
.
├── composer.json           # ← Fix 1: marker para Railpack
├── index.php               # ← Fix 4: DirectoryIndex para XAMPP
├── nixpacks.toml           # ← Fallback si Railpack falla
├── Procfile                # ← Start command
├── public/
│   ├── index.php           # ← Fix 2 vive acá (getenv merge)
│   └── setup.php           # ← Fix 3: wrapper
└── setup.php               # ← El original
```

---

## 🤔 ¿Lo haría distinto si empezara de nuevo?

**Probablemente sí.** Si supiera todo esto antes:

1. **`composer.json` desde el commit #1** — aunque no use Composer, el archivo ahorra horas.
2. **Helper `env($key, $default)`** que use `getenv()` directamente, en lugar de `$_ENV` por toda la codebase.
3. **DirectoryIndex en raíz desde el principio** — patrón estándar de framework.
4. **CI con un smoke test** que haga `curl /login` y chequee `200 OK` después de cada deploy.

Pero también: cada uno de estos problemas me hizo entender un nivel más profundo del stack. **Esa es la diferencia entre "uso PHP" y "sé PHP"**.

---

← Volver al [README](./README.md)
