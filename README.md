# Sistema de Gestión

Este repositorio sirve como base para el sistema de gestión construido con **Laravel 12**.

## Estructura

- `app/` : lógica de la aplicación (modelos, controladores, comandos)
- `resources/` : vistas Blade, componentes Livewire, assets
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
- **MySQL** (todos los desarrolladores usan MySQL en local)

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

# 5. Configurar base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nombre_bd
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Crear base de datos y correr migraciones
php artisan migrate

# 7. (Opcional) Cargar datos de prueba
php artisan db:seed
```

### Levantar el proyecto (cada vez)

```bash
# Terminal 1 — Servidor Laravel
php artisan serve
# En caso de error en Windows, usar:
# php artisan serve --no-reload

# Terminal 2 — Vite (frontend)
npm run dev
```

> **Nota Windows:** Si `php artisan serve` falla con `Failed to listen on 127.0.0.1:8000`, usá `php artisan serve --no-reload`. Esto soluciona un bug conocido de Laravel en Windows al filtrar variables de entorno.

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
