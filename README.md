# Sistema de Gestión

Este repositorio sirve como base para el sistema de gestión construido con **Laravel 12**.

## Estructura

- `app/` : lógica de la aplicación (modelos, controladores, comandos)
- `resources/` : vistas Blade, componentes Livewire y assets
- `resources/views/components/README.md` : organización y convención de componentes Blade
- `routes/` : definición de rutas (web, api, ai)
- `database/` : migraciones, seeders y factories
- `config/` : archivos de configuración
- `public/` : punto de entrada del servidor web
- `README.md` : documentación principal

---

## Requisitos previos

- **PHP** ^8.4 (con extensiones: `pdo_sqlite`, `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Composer** (gestor de dependencias PHP)
- **Node.js** + **npm** (para compilar assets con Vite)
- Base de datos: **SQLite** (no requiere servidor ni Docker) o **MySQL/PostgreSQL** si preferís un motor externo

---

## Iniciar servidor para desarrollo

### Primera vez (instalación)

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Instalar dependencias Node
npm install

# 3. Crear archivo de entorno
copy .env.example .env        # Windows
# cp .env.example .env       # Linux/Mac

# 4. Generar APP_KEY
php artisan key:generate
```

Después elegí **una** de las dos formas de base de datos:

#### Opción A — SQLite (local, sin Docker ni servidor de BD)

`.env.example` ya viene preparado para SQLite. En `.env` verificá que exista:

```env
DB_CONNECTION=sqlite
```

No definas `DB_DATABASE`: Laravel usa por defecto `database/database.sqlite`. Luego creá el archivo y cargá el esquema con datos de demostración:

```bash
# Windows PowerShell (crear el archivo antes de migrar)
New-Item -ItemType File -Path database/database.sqlite

# Linux/Mac
touch database/database.sqlite

php artisan migrate --seed
```

El seed carga roles, permisos, estados, categorías, servicios, usuarios y datos de demostración (clientes, procesos con estados variados, turnos, documentos, reportes y comprobantes).

#### Opción B — MySQL o PostgreSQL (base de datos externa)

Configurá la conexión en `.env`, por ejemplo para MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_bd
DB_USERNAME=root
DB_PASSWORD=
```

O para PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nombre_bd
DB_USERNAME=postgres
DB_PASSWORD=
```

Creá la base y corré las migraciones:

```bash
php artisan migrate
php artisan db:seed
```

### Levantar el proyecto con Docker

```bash
docker compose up -d
```

La primera vez, o cuando cambie la imagen de Docker:

```bash
docker compose up -d --build
```

Laravel queda disponible en `http://localhost:8000` y el servicio Vite se inicia automáticamente en el puerto `5173`. No es necesario ejecutar `npm run dev` por separado.

### Levantar el proyecto sin Docker

Con SQLite no hace falta levantar ningún servicio adicional. Un solo comando inicia Laravel y Vite en la misma consola:

```bash
php artisan dev
```

- Laravel: `http://localhost:8000`
- Vite: `http://localhost:5175`

Al presionar `Ctrl+C` se detienen ambos procesos; no quedan servidores ni consolas abiertas. El comando usa `--no-reload`, así que si modificás `.env` tenés que reiniciarlo.

También podés usar el atajo de Composer, que ejecuta el mismo comando:

```bash
composer run dev
```

> **Nota Windows:** `composer.bat` puede mostrar `Terminate batch job (Y/N)?` al cancelar; respondé `Y`. Para evitar ese paso, usá directamente `php artisan dev`.

Para incluir también el worker de colas y los logs en vivo:

```bash
php artisan dev --full
# equivalente:
composer run dev:full
```

> **Nota logs:** Pail requiere la extensión `pcntl`, que no está disponible en Windows. En ese caso `--full` levanta Laravel, Vite y el worker de colas, y omite el visor de logs; podés seguir el log con `Get-Content storage/logs/laravel.log -Wait`.

Si preferís ejecutar los procesos por separado, usá dos terminales:

```bash
# Terminal 1 — Servidor Laravel
php artisan serve --no-reload

# Terminal 2 — Vite (frontend)
npm run dev
```

> **Nota Windows:** `php artisan dev` ya utiliza `php artisan serve --no-reload`, que evita el bug conocido de Laravel en Windows al filtrar variables de entorno.

Si no vas a tocar el frontend, alcanza con compilar los assets una vez (`npm run build`) y ejecutar sólo `php artisan serve`.

### Usuarios de demostración

Después de `php artisan migrate --seed`, podés ingresar en `http://localhost:8000` con:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| Administrador | `admin@example.com` | `1234` |
| Secretario | `carlos@example.com` | `1234` |
| Profesional | `juanperez@example.com` | `1234` |
| Coordinador | `coordinador@example.com` | `1234` |
| Directivo | `directivo@example.com` | `1234` |

Los procesos de demostración se generan con estados variados (pendiente, admitido, iniciado, en proceso, en espera, finalizado y rechazado) para probar los filtros y el tablero.


---

## Cómo funcionan las vistas interactivas

El estándar para las pantallas que necesitan interacción es **Laravel MVC + Blade + Livewire + Alpine.js + Tailwind CSS**:

- **Laravel MVC:** contiene las rutas, permisos, validaciones, modelos y reglas del sistema.
- **Blade:** construye el HTML inicial de la página.
- **Tailwind CSS:** define colores, tamaños, espacios y diseño responsive.
- **Alpine.js:** maneja interacciones que sólo necesitan el navegador, como abrir o cerrar un modal. Por eso estas acciones son inmediatas.
- **Livewire:** se utiliza cuando hay que consultar o guardar datos. Envía una petición al servidor sin recargar toda la página.

En `/servicios`, por ejemplo, al presionar **Nuevo Servicio** ocurre lo siguiente:

1. Alpine muestra el modal inmediatamente.
2. Livewire prepara el formulario y verifica el permiso `gestionar_servicios`.
3. Al guardar, Livewire valida los datos y los persiste en la base de datos.
4. Livewire informa el resultado y Alpine cierra el modal.

No todas las vistas necesitan Livewire. Una vista estática puede usar sólo Blade + Tailwind. Tampoco es obligatorio crear una API: la aplicación continúa utilizando MVC.

---

## Calidad y formato durante el desarrollo

Laravel Pint es una herramienta de desarrollo que ordena automáticamente el formato del código PHP. No es parte de la aplicación en producción, no cambia la lógica y no requiere Docker.

Antes de confirmar cambios PHP, ejecutar Pint sobre los archivos modificados:

```bash
vendor/bin/pint --dirty --format agent
```

Si el proyecto está levantado con Docker Sail, usar:

```bash
docker compose exec -T laravel.test vendor/bin/pint --dirty --format agent
```

Usar una sola de las dos opciones:

- Si PHP y Composer están instalados en la computadora, usar el primer comando.
- Si se trabaja con Docker y PHP está dentro del contenedor, usar el segundo.

Si no se usa Docker, no hay que instalar Docker para ejecutar Pint; alcanza con ejecutar primero `composer install` y luego el comando local. Se recomienda ejecutarlo antes de cada commit junto con las pruebas afectadas:

```bash
php artisan test --compact tests/Feature/NombreDelTest.php
```

---

## Desarrollo con IA (Laravel Boost + OpenCode)

Este proyecto incluye **Laravel Boost**, el servidor MCP oficial de Laravel para agentes de IA.

### ¿Qué es?

Laravel Boost expone herramientas (tools) que permiten a OpenCode (u otros agentes MCP) interactuar directamente con la aplicación: consultar la base de datos, leer logs, listar rutas, ejecutar comandos Artisan, buscar en la documentación oficial de Laravel, y más.

### Configuración para OpenCode

El archivo `opencode.json` ya está incluido en la raíz del proyecto. OpenCode lo detecta automáticamente al abrir la carpeta.

```json
{
  "mcp": {
    "laravel-boost": {
      "type": "local",
      "command": ["php", "artisan", "boost:mcp"],
      "enabled": true
    }
  }
}
```

### Tools disponibles

| Tool | Función |
| :--- | :--- |
| Application Info | Versiones de PHP/Laravel, paquetes instalados, modelos Eloquent |
| Database Query | Ejecutar queries SQL contra la base de datos |
| Database Schema | Inspeccionar tablas y columnas |
| List Routes | Ver rutas registradas de la aplicación |
| Read Log Entries | Leer logs recientes de Laravel |
| Last Error | Ver el último error del log |
| Search Docs | Buscar en la documentación oficial del ecosistema Laravel |
| Tinker | Ejecutar código PHP en contexto de la app |

### Comandos útiles de Boost

```bash
# Actualizar guidelines y skills
php artisan boost:update

# Listar skills instalados
php artisan boost:list-skills

# Iniciar servidor MCP manualmente (no es necesario, OpenCode lo hace solo)
php artisan boost:mcp
```

---

## Archivos ignorados por el repositorio

| Archivo / Carpeta | Razón |
| :--- | :--- |
| `.env` | Contiene credenciales y configuración sensible local. |
| `.env.*` | Variantes de entorno (producción, backup). |
| `vendor/` | Dependencias de Composer (se instalan con `composer install`). |
| `node_modules/` | Dependencias de Node (se instalan con `npm install`). |
| `storage/*.key`, `storage/pail` | Archivos generados por la aplicación en runtime. |
| `.agents/` | Skills de IA generados automáticamente por `boost:install`. |
| `AGENTS.md` | Guidelines del agente, regenerado por `boost:update`. |
| `boost.json` | Configuración local de Laravel Boost por desarrollador. |
| `.mcp.json` | Configuración específica del agente MCP (Cursor, Claude, etc.). |
| `CLAUDE.md`, `junie/` | Archivos específicos de agentes de IA individuales. |

> **Nota:** Los archivos de Laravel Boost (`.agents/`, `AGENTS.md`, `boost.json`) se regeneran automáticamente al correr `php artisan boost:install` o `boost:update`. No es necesario ni recomendable versionarlos.

---

## Notas

- Ajustar permisos de `storage/` y `bootstrap/cache/` si es necesario (en Linux/Mac: `chmod -R 775 storage bootstrap/cache`).
- Actualizar la documentación a medida que avance el desarrollo.

### Pendiente: visualización de comprobantes

El módulo permite registrar y listar comprobantes de pago en PDF, almacenándolos de forma privada. Falta implementar la visualización o descarga autorizada del archivo desde el listado de comprobantes.
