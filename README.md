# Clouds.com.bo

Sistema de gestión de servicios cloud con OctoberCMS 4.0

## Stack Tecnológico

- **Backend:** OctoberCMS 4.0 (Laravel 12.23.1)
- **PHP:** 8.2.29
- **Base de datos:** MySQL 8
- **Cache/Sessions/Queues:** Redis
- **Frontend:** Vite 5.4 + Tailwind CSS 3.4 + Alpine.js 3.14
- **PWA:** Service Worker con Workbox

## Plugins Personalizados

### Aero.Clouds
Plugin principal de gestión de servicios cloud con las siguientes entidades:

- **Services:** Servicios principales con relaciones múltiples
- **Plans:** Planes de hosting con pricing flexible
- **Addons:** Complementos adicionales
- **Features:** Características de servicios
- **FAQs:** Preguntas frecuentes con links relacionados
- **Docs:** Documentación de servicios

### Relaciones del Sistema

Solo **Services** maneja las relaciones many-to-many con:
- Plans (via `aero_clouds_plan_service`)
- Addons (via `aero_clouds_addon_service`)
- Features (via `aero_clouds_feature_service`)
- FAQs (via `aero_clouds_faq_service`)
- Docs (via `aero_clouds_doc_service`)

## Estructura del Proyecto

```
/home/clouds/htdocs/clouds.com.bo/
├── plugins/aero/clouds/     # Plugin principal
├── themes/master/           # Tema activo
│   ├── assets/src/         # Fuentes (CSS/JS/PWA)
│   └── assets/dist/        # Compilados
└── storage/                # Archivos y logs
```

## Comandos Importantes

```bash
# Build de producción (requerido después de cambios JS/CSS)
cd themes/master
npm run build

# Desarrollo con HMR
npm run dev

# Limpiar cache
php artisan cache:clear
php artisan config:clear

# Migraciones
php artisan october:migrate
```

## Ambiente de Producción

- **Servidor:** CloudPanel
- **Dominio:** https://clouds.com.bo
- **Directorio:** `/home/clouds/htdocs/clouds.com.bo/`
- **Permisos:** 775

⚠️ **Importante:** Este es un ambiente de producción en vivo. Todos los cambios son inmediatos.

## Desarrollo

Repositorio: https://github.com/aeromatico/clouds.git
