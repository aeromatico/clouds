# Api Hub - Guía Rápida de Inicio

## ✅ Estado de la Instalación

El plugin está completamente instalado con:
- ✓ 3 APIs de ejemplo (Weather, Finance, News)
- ✓ 10 endpoints de ejemplo
- ✓ Tablas de base de datos creadas
- ✓ Todas las clases y controllers cargados

## 📋 Pasos para Usar el Scanner

### 1. Configurar RapidAPI Key (REQUERIDO para buscar APIs)

Para poder buscar e importar APIs desde RapidAPI:

1. Obtén tu API key gratis en: https://rapidapi.com
   - Regístrate/inicia sesión
   - Ve a tu perfil → Applications
   - Copia tu API key

2. En el backend de OctoberCMS:
   - Ve a: **Settings → Api Hub Settings**
   - Pega tu RapidAPI key en el campo "RapidAPI Key"
   - Configura Cache TTL: `3600` (1 hora)
   - Guarda

### 2. Ver APIs Existentes

1. Ve a: **Api Hub → APIs**
2. Verás las 3 APIs de ejemplo ya creadas
3. Haz clic en cualquiera para ver sus endpoints

### 3. Buscar e Importar APIs desde RapidAPI

1. Ve a: **Api Hub → Import**

2. **Si NO has configurado el RapidAPI key:**
   - Verás un mensaje de advertencia
   - Haz clic en "Go to Settings" para configurarla

3. **Si YA configuraste el RapidAPI key:**
   - Escribe un término de búsqueda (ejemplo: "weather", "crypto", "news")
   - Haz clic en "Search"
   - Verás los resultados de RapidAPI
   - Haz clic en "Import" para importar cualquier API

### 4. Importación Rápida por Categoría

En la página Import, puedes importar APIs populares con un clic:
- Weather
- Finance
- Social
- Sports

## 🔍 Solución de Problemas

### "No Results" al buscar

**Causa:** No has configurado el RapidAPI key

**Solución:**
1. Ve a Settings → Api Hub Settings
2. Ingresa tu RapidAPI key
3. Guarda
4. Vuelve a Import e intenta de nuevo

### Error "RapidAPI key not configured"

**Solución:**
1. Obtén tu key de https://rapidapi.com
2. Ve a Settings → Api Hub Settings
3. Pega la key
4. Guarda

### La búsqueda no responde

**Verifica:**
1. Redis está corriendo: `redis-cli ping` (debe responder PONG)
2. Verifica logs: `storage/logs/system.log`
3. Limpia cache: `php artisan cache:clear`

## 🚀 Usar desde el Frontend

### Mostrar Catálogo de APIs

Crea una página en el CMS con:

```twig
title = "API Catalog"
url = "/api-catalog"

[apiCatalog]
perPage = 12
showSearch = 1
showFilters = 1
==
{% component 'apiCatalog' %}
```

### Mostrar Endpoints de una API

Crea una página:

```twig
title = "API Details"
url = "/api-catalog/:slug"

[endpointList]
apiSlug = "{{ :slug }}"
groupByMethod = 1
showParameters = 1
==
{% component 'endpointList' %}
```

## 📊 Ver Analytics

1. Ve a: **Api Hub → Analytics**
2. Verás:
   - Total de APIs y endpoints
   - Gráfico de APIs por categoría
   - Gráfico de endpoints por método HTTP
   - Patrones comunes de endpoints

## 🔧 Comandos de Consola

```bash
# Sincronizar APIs desde RapidAPI
php artisan apihub:sync

# Sincronizar todas las APIs
php artisan apihub:sync --all

# Sincronizar API específica
php artisan apihub:sync --id=1

# Usar cola para sync
php artisan apihub:sync --all --queue

# Limpiar cache
php artisan cache:clear
```

## 🎯 Próximos Pasos

1. ✅ Configura tu RapidAPI key
2. ✅ Busca e importa APIs que te interesen
3. ✅ Crea páginas frontend para mostrar tu catálogo
4. ✅ Configura auto-sync en Settings para mantener datos actualizados

## 💡 Tips

- **Cache:** El plugin usa Redis para cachear todo. Los datos se actualizan cada hora.
- **Queue:** Las importaciones usan colas. Asegúrate de tener `queue:work` corriendo.
- **Permisos:** Asigna permisos específicos a usuarios en Settings → Administrators → Manage Roles

## 📝 Notas Importantes

- Los datos de ejemplo YA están instalados (no necesitas seed)
- La búsqueda REQUIERE RapidAPI key configurada
- Los datos se cachean por 1 hora por defecto
- Las importaciones se ejecutan en background (queue)

## 🆘 Soporte

Si tienes problemas:
1. Revisa logs: `storage/logs/system.log`
2. Limpia cache: `php artisan cache:clear`
3. Verifica Redis: `redis-cli ping`
4. Revisa este archivo: `plugins/aero/apihub/README.md`
