# Aero.Clouds Plugin

Plugin de gestión de servicios cloud para OctoberCMS 4.0

## Descripción

Plugin completo para gestión de servicios cloud, planes de hosting, addons, características, FAQs y documentación.

## Entidades

### Services
Servicios principales del sistema. Maneja todas las relaciones many-to-many con:
- Plans
- Addons
- Features
- FAQs
- Docs

**Campos:**
- `name`, `slug`, `description`
- `short_description`, `menu_description`, `html_description`
- `icon`, `is_active`, `sort_order`
- Attachments: `img` (single), `gallery` (multiple)

### Plans
Planes de hosting con pricing flexible y características.

**Campos:**
- `name`, `slug`, `description`
- `is_active`, `is_featured`, `promo`
- `free_domain`, `ssh`, `ssl`, `dedicated_ip`
- `pricing` (JSON): array de opciones con price, currency, billing_cycle, setup_fee
- `features` (JSON): array de características
- `limits` (JSON): array de límites de recursos

### Addons
Complementos adicionales para los servicios.

**Campos:**
- `name`, `slug`, `description`
- `pricing` (decimal)
- `is_active`, `sort_order`

### Features
Características destacadas de los servicios.

**Campos:**
- `name`, `slug`, `description`
- `icon`, `is_active`, `is_highlighted`
- `sort_order`

### FAQs
Preguntas frecuentes con respuestas detalladas.

**Campos:**
- `question`, `answer` (richeditor)
- `links` (JSON): array de {name, url}
- `is_active`, `sort_order`

### Docs
Documentación detallada de servicios.

**Campos:**
- `title`, `slug`, `content` (richeditor)
- `is_active`, `sort_order`

## Relaciones

Solo **Services** mantiene relaciones bidireccionales many-to-many:

```php
Service->plans     // via aero_clouds_plan_service
Service->addons    // via aero_clouds_addon_service
Service->features  // via aero_clouds_feature_service
Service->faqs      // via aero_clouds_faq_service
Service->docs      // via aero_clouds_doc_service
```

Los modelos individuales (Plan, Addon, Feature, FAQ, Doc) **no tienen** relaciones inversas.

## Tablas de Base de Datos

**Principales:**
- `aero_clouds_services`
- `aero_clouds_plans`
- `aero_clouds_addons`
- `aero_clouds_features`
- `aero_clouds_faqs`
- `aero_clouds_docs`

**Pivot Tables:**
- `aero_clouds_plan_service`
- `aero_clouds_addon_service`
- `aero_clouds_feature_service`
- `aero_clouds_faq_service`
- `aero_clouds_doc_service`

## Instalación

1. Copiar el plugin a `plugins/aero/clouds/`
2. Ejecutar migraciones:
```bash
php artisan october:migrate
```

## Desarrollo

Repositorio: https://github.com/aeromatico/clouds.git

## Autor

Aero - https://clouds.com.bo
