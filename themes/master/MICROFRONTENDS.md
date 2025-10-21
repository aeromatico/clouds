# Sistema de Microfrontends con Cache de APIs

Este documento describe el sistema de microfrontends implementado en Master Theme, que incluye un API Manager centralizado para reutilización inteligente de endpoints.

## 🎯 Problema que Resuelve

En aplicaciones con múltiples microfrontends, es común que diferentes componentes necesiten los mismos datos del backend:

- **User Dashboard** necesita `GET /api/user/profile`
- **User Profile** también necesita `GET /api/user/profile`  
- **User Menu** también necesita `GET /api/user/profile`

Sin un sistema de cache, cada componente hace su propia petición HTTP, causando:
- ❌ Requests duplicados innecesarios
- ❌ Inconsistencia de datos entre componentes
- ❌ Mayor latencia y uso de bandwidth
- ❌ Complejidad para sincronizar actualizaciones

## ✅ Solución Implementada

### Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────┐
│                 API Manager                         │
│  ┌─────────────┬─────────────┬─────────────────────┐ │
│  │    Cache    │  Subscribers │   Request Queue     │ │
│  │   (5 min)   │   System    │   (Deduplication)   │ │
│  └─────────────┴─────────────┴─────────────────────┘ │
└─────────────────────────────────────────────────────┘
                          │
              ┌───────────┼───────────┐
              │           │           │
    ┌─────────▼──┐ ┌──────▼─────┐ ┌───▼────────┐
    │ Dashboard  │ │  Profile   │ │    Menu    │
    │Microfrontend│ │Microfrontend│ │Microfrontend│
    └────────────┘ └────────────┘ └────────────┘
```

## 🚀 Uso Básico

### 1. Crear un Microfrontend

```javascript
// assets/src/js/microfrontends/my-component.js
import { createApiComponent } from '../core/alpine-api-mixin.js';

document.addEventListener('alpine:init', () => {
    Alpine.data('myComponent', createApiComponent({
        // Estado del componente
        data: null,
        
        async init() {
            // Cargar datos con cache automático
            await this.loadData();
            
            // Suscribirse a cambios desde otros componentes
            this.apiSubscribe('/api/my-data', (newData) => {
                this.data = newData;
            });
        },

        async loadData() {
            try {
                this.data = await this.apiCall('/api/my-data', {
                    cache: true,           // Habilitar cache
                    ttl: 5 * 60 * 1000    // 5 minutos de duración
                });
            } catch (error) {
                console.error('Error loading data:', error);
            }
        },

        async updateData(newData) {
            try {
                const result = await this.apiCall('/api/my-data', {
                    method: 'PUT',
                    body: JSON.stringify(newData),
                    cache: false  // No cache para operaciones de escritura
                });
                
                this.data = result;
                // Automáticamente notifica a otros componentes
                
            } catch (error) {
                console.error('Error updating data:', error);
            }
        }
    }));
});
```

### 2. Crear el Partial Twig

```html
<!-- partials/microfrontends/my-component.htm -->
<div 
    x-data="myComponent" 
    x-init="init()" 
    data-component="my-component"
    class="bg-card rounded-lg shadow-lg p-6"
>
    <!-- Loading State -->
    <div x-show="loading" class="flex items-center space-x-2">
        <div class="spinner"></div>
        <span>Cargando...</span>
    </div>

    <!-- Error State -->
    <div x-show="error" class="text-red-500">
        Error: <span x-text="error"></span>
    </div>

    <!-- Content -->
    <div x-show="!loading && !error && data">
        <h3 x-text="data?.title"></h3>
        <p x-text="data?.description"></p>
        
        <button @click="updateData({...data, updated: true})">
            Actualizar
        </button>
    </div>
</div>
```

### 3. Incluir en una Página

```html
<!-- pages/my-page.htm -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 space-y-8">
        
        <!-- Múltiples microfrontends usando los mismos datos -->
        {% partial 'microfrontends/my-component' %}
        {% partial 'microfrontends/another-component' %}
        
    </div>
</section>
```

## 🔧 API Manager - Referencia Completa

### Métodos Principales

#### `apiManager.fetch(endpoint, options)`

```javascript
const data = await apiManager.fetch('/api/users', {
    method: 'GET',           // HTTP method
    cache: true,             // Habilitar cache (default: true para GET)
    ttl: 5 * 60 * 1000,     // Time to live en ms (default: 5 min)
    forceRefresh: false,     // Ignorar cache y hacer request fresco
    headers: {               // Headers adicionales
        'Authorization': 'Bearer token'
    }
});
```

#### `apiManager.subscribe(endpoint, callback)`

```javascript
const unsubscribe = apiManager.subscribe('/api/user/profile', (data) => {
    console.log('Perfil actualizado:', data);
});

// Desuscribirse más tarde
unsubscribe();
```

#### `apiManager.clearCache(pattern)`

```javascript
// Limpiar todo el cache
apiManager.clearCache();

// Limpiar cache específico
apiManager.clearCache('/api/user/');
```

### Utilidades del Alpine Mixin

#### Propiedades Automáticas

Todos los componentes que usan `createApiComponent` tienen:

```javascript
{
    loading: false,        // true durante requests
    error: null,          // mensaje de error si falla
    lastUpdate: null,     // timestamp de última actualización
    
    // Métodos helper
    apiCall(),           // Wrapper con loading/error automático
    apiSubscribe(),      // Suscribirse a cambios
    apiInvalidate(),     // Invalidar cache
    apiCleanup()         // Limpiar suscripciones (automático)
}
```

## 🛠️ Casos de Uso Avanzados

### 1. Batch Loading

```javascript
// Cargar múltiples endpoints en paralelo
import { apiUtils } from '../core/alpine-api-mixin.js';

const results = await apiUtils.batchFetch([
    '/api/user/profile',
    '/api/user/settings', 
    '/api/user/notifications'
], { cache: true });
```

### 2. Polling con Cache

```javascript
// Polling cada 30 segundos con cache inteligente
const stopPolling = apiUtils.startPolling('/api/notifications', 30000, {
    cache: true,
    ttl: 10000  // Cache por 10 segundos
});

// Detener polling más tarde
stopPolling();
```

### 3. Prefetch de Datos

```javascript
// Precargar datos que probablemente se necesiten
await apiManager.prefetch([
    '/api/user/dashboard',
    '/api/user/recent-activity',
    '/api/system/status'
]);
```

### 4. Endpoints Dinámicos

```javascript
import { apiUtils } from '../core/alpine-api-mixin.js';

// Construir URLs con parámetros
const endpoint = apiUtils.buildEndpoint('/api/users', {
    page: 2,
    limit: 20,
    search: 'john'
});
// Resultado: '/api/users?page=2&limit=20&search=john'
```

## 📊 Monitoreo y Debug

### Ver Estadísticas del Cache

```javascript
const stats = apiManager.getCacheStats();
console.log('Cache stats:', stats);
/*
{
    total: 5,           // Entradas en cache
    pending: 1,         // Requests en progreso
    subscribers: 3,     // Suscriptores activos
    entries: [...]      // Detalles de cada entrada
}
*/
```

### Debug Mode

Activa logs detallados en la consola del navegador para ver:
- Cache hits/misses
- Request deduplication
- Invalidaciones automáticas
- Notificaciones a suscriptores

## 🎯 Beneficios Medibles

### Antes (sin cache)
```
Request 1: GET /api/user/profile (200ms)
Request 2: GET /api/user/profile (180ms) // Dashboard
Request 3: GET /api/user/profile (220ms) // Profile
Request 4: GET /api/user/profile (190ms) // Menu
Total: 790ms + 4 requests
```

### Después (con cache)
```
Request 1: GET /api/user/profile (200ms)
Request 2: Cache hit (0ms)               // Dashboard  
Request 3: Cache hit (0ms)               // Profile
Request 4: Cache hit (0ms)               // Menu
Total: 200ms + 1 request
```

**Mejora: 75% menos tiempo, 75% menos requests**

## 🔒 Invalidación Inteligente

El sistema invalida automáticamente el cache cuando:

1. **Operaciones de escritura**: POST, PUT, PATCH, DELETE invalidan cache relacionado
2. **TTL expirado**: Cache automáticamente expirado
3. **Manual**: `apiInvalidate()` o `clearCache()`

```javascript
// Esto automáticamente invalida todo el cache de /api/user/*
await apiCall('/api/user/profile', {
    method: 'PUT',
    body: JSON.stringify(updatedProfile)
});
```

## 📝 Mejores Prácticas

### ✅ DO
- Usar cache para operaciones GET/HEAD
- Configurar TTL apropiado según frecuencia de cambio de datos
- Suscribirse a cambios en datos compartidos
- Manejar estados de loading y error

### ❌ DON'T  
- No usar cache para operaciones que modifican datos
- No usar TTL muy largo para datos que cambian frecuentemente
- No olvidar manejar errores de red
- No hacer requests síncronos

## 🚀 Extensiones Futuras

El sistema está diseñado para extensión fácil:

1. **Persistencia**: Guardar cache en localStorage/IndexedDB
2. **Offline Support**: Queue de requests para cuando vuelva conexión  
3. **Analytics**: Métricas de performance del cache
4. **Background Sync**: Actualización en background
5. **GraphQL Support**: Cache inteligente para queries GraphQL

---

## 🎉 Conclusión

Este sistema de microfrontends con cache de APIs te permite:

- ✅ **Reducir drasticamente** el número de requests HTTP
- ✅ **Mantener sincronizados** múltiples componentes automáticamente  
- ✅ **Desarrollar independientemente** cada microfrontend
- ✅ **Mejorar performance** y experiencia de usuario
- ✅ **Escalar fácilmente** añadiendo nuevos microfrontends

¡Todo sin sacrificar la independencia y reutilización de los componentes!