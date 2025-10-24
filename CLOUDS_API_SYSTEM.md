# Clouds API System - Documentación Completa

**Fecha de creación:** 2025-01-16
**Sistema:** API REST Híbrido para aero/clouds plugin
**Ubicación:** `/plugins/aero/clouds/`

---

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Componentes Principales](#componentes-principales)
4. [Uso del API](#uso-del-api)
5. [Sistema de Cache con Redis](#sistema-de-cache-con-redis)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Integración con Microfrontends](#integración-con-microfrontends)
8. [Troubleshooting](#troubleshooting)

---

## Descripción General

El **Clouds API System** es un sistema híbrido que combina dos enfoques para maximizar versatilidad:

### 🎯 Dos Capas de API

**1. ApiDispatcher** (Para consultas complejas y lectura)
- Endpoint unificado `/api`
- Optimizado con Redis cache
- Ideal para: Microfrontends, SPAs, Mobile apps, integraciones externas
- Soporta: Scopes, relaciones, filtros complejos

**2. FormHandler + CloudsForm** (Para formularios simples del frontend web)
- Componentes de October CMS
- Ideal para: Formularios HTML tradicionales en el sitio
- Uso: `data-request="formComponent::onCreate"`

### ✅ Ventajas del Sistema Híbrido

- **Flexibilidad**: Cada caso usa la herramienta correcta
- **Performance**: Cache inteligente con Redis
- **Versatilidad**: Soporta microfrontends aislados
- **Mantenibilidad**: Código reutilizable y documentado

---

## Arquitectura del Sistema

```
plugins/aero/clouds/
├── classes/
│   ├── ApiDispatcher.php    # Dispatcher principal con cache
│   ├── ApiCache.php          # Sistema de cache con Redis
│   └── ...
├── components/
│   ├── CloudsForm.php        # Componente genérico para formularios
│   └── ...
├── traits/
│   └── FormHandler.php       # Trait reutilizable para CRUD
├── routes.php                # Rutas unificadas de la API
└── Plugin.php                # Registra componentes y rutas
```

### Flujo de Datos

```
Request → routes.php → ApiDispatcher → Model → Redis Cache → Response
                                    ↓
                            FormHandler (formularios simples)
```

---

## Componentes Principales

### 1. **ApiDispatcher.php**

**Ubicación:** `/plugins/aero/clouds/classes/ApiDispatcher.php`

**Propósito:** Endpoint unificado para operaciones CRUD complejas con cache inteligente.

**Actions soportadas:**
- `list` - Listar registros con filtros, scopes y relaciones
- `get` - Obtener un registro específico por ID o slug
- `schema` - Auto-documentación del modelo (fields, relations, types)
- `create` - Crear nuevo registro
- `update` - Actualizar registro existente
- `delete` - Eliminar registro

**Características:**
- ✅ Auto-descubrimiento de modelos
- ✅ Scopes dinámicos del modelo
- ✅ Relaciones eager loading (with:)
- ✅ Selección de campos específicos (fields:)
- ✅ Cache inteligente con Redis
- ✅ Validación automática desde el modelo
- ✅ Logging completo

---

### 2. **ApiCache.php**

**Ubicación:** `/plugins/aero/clouds/classes/ApiCache.php`

**Propósito:** Sistema de cache inteligente con Redis para optimizar consultas grandes.

**Funciones principales:**

```php
// Recordar datos con callback
ApiCache::remember($key, function() {
    return Model::with('relation')->get();
}, $ttl, ['tag1', 'tag2']);

// Invalidar por tag
ApiCache::forgetTag('order');

// Invalidar por modelo
ApiCache::invalidateModel(Order::class);

// Estadísticas
ApiCache::stats();
```

**TTL (Time To Live):**
- Schemas: 24 horas (casi nunca cambian)
- Consultas grandes (>50 items): 6 horas
- Consultas normales: 1 hora

**Tags para invalidación:**
Cada modelo se cachea con un tag (ej: `order`, `invoice`). Al crear/actualizar/eliminar, se invalida todo el cache de ese modelo.

---

### 3. **FormHandler.php** (Trait)

**Ubicación:** `/plugins/aero/clouds/traits/FormHandler.php`

**Propósito:** Trait reutilizable para operaciones CRUD en componentes.

**Métodos principales:**

```php
// En cualquier componente
use \Aero\Clouds\Traits\FormHandler;

public function onCreateOrder()
{
    return $this->handleCreate(
        'Aero\Clouds\Models\Order',
        post(),
        '/dashboard/orders',
        [
            'requireAuth' => true,
            'checkOwnership' => true,
            'successMessage' => 'Orden creada exitosamente'
        ]
    );
}
```

**Opciones disponibles:**
- `requireAuth` - Requiere autenticación (default: true)
- `checkOwnership` - Verificar que el registro pertenece al usuario
- `successMessage` / `errorMessage` - Mensajes personalizados
- `transform` - Callback para transformar datos antes de guardar
- `afterCreate` / `afterUpdate` / `beforeDelete` - Callbacks
- `relations` - Manejo automático de relaciones (attach, sync, detach)

---

### 4. **CloudsForm.php** (Component)

**Ubicación:** `/plugins/aero/clouds/components/CloudsForm.php`

**Propósito:** Componente genérico configurable para cualquier modelo.

**Uso en páginas:**

```ini
[cloudsForm orderForm]
model = "Order"
redirectTo = "/dashboard/orders"
requireAuth = 1
checkOwnership = 1
successMessage = "Orden creada exitosamente"
==
```

**En el template:**

```html
<form data-request="orderForm::onCreate">
    <input name="user_id" value="{{ user.id }}" />
    <input name="total_amount" type="number" />
    <button type="submit">Crear Orden</button>
</form>
```

---

## Uso del API

### Endpoint Principal

```
POST /api
```

> **Nota:** El endpoint `/api` es manejado por aero/clouds. El antiguo `/api` de aero/manager fue movido a `/api/manager`.

### Parámetros Comunes

| Parámetro | Descripción | Ejemplo |
|-----------|-------------|---------|
| `model` | Nombre del modelo (required) | `order`, `invoice`, `cloud` |
| `action` | Acción a realizar (default: list) | `list`, `get`, `create`, `update`, `delete`, `schema` |
| `id` | ID del registro (para get/update/delete) | `123` |
| `slug` | Slug del registro (alternativa a id) | `my-order` |
| `scope` | Scopes del modelo (separados por coma) | `pending,recent` |
| `with` | Relaciones eager loading | `user:id,email\|invoice:id,total` |
| `fields` | Campos a retornar | `id,order_date,status,total_amount` |
| `limit` | Límite de resultados (default: 50) | `20` |

### Formato del parámetro `with`

El parámetro `with` permite cargar relaciones de manera eficiente:

```
with=relation                    # Todos los campos
with=relation:field1,field2      # Solo field1 y field2
with=rel1:id,name|rel2:id        # Múltiples relaciones
```

**Ejemplos:**
```
with=user                        # Todos los campos del usuario
with=user:id,email,name          # Solo id, email y name del usuario
with=user:id,email|invoice:id,total  # Usuario e invoice
```

---

## Sistema de Cache con Redis

### ¿Cuándo se cachea?

1. **Actions de lectura:**
   - `list` - Sí, con TTL variable según cantidad de resultados
   - `get` - Sí, con TTL default (1 hora)
   - `schema` - Sí, con TTL largo (24 horas)

2. **Actions de escritura:**
   - `create` / `update` / `delete` - No se cachean, **invalidan** el cache del modelo

### Generación de Claves

Las claves de cache son únicas por combinación de parámetros:

```
clouds_api:order.list.{md5(params)}
clouds_api:order.get.{md5(id+with+fields)}
clouds_api:order.schema.{md5([])}
```

### Invalidación Automática

Cuando se crea/actualiza/elimina un registro:

```php
// En ApiDispatcher
ApiCache::invalidateModel(Order::class);
// Esto elimina TODAS las claves del tag 'order'
```

### Endpoints de Gestión de Cache

```bash
# Ver estadísticas
GET /api/cache/stats

# Limpiar todo el cache
POST /api/cache/flush

# Invalidar cache de un modelo específico
POST /api/cache/invalidate/order
```

---

## Ejemplos Prácticos

### 1. Listar Órdenes Pendientes con Usuario e Invoice

```javascript
fetch('/api', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    model: 'order',
    action: 'list',
    scope: 'pending,recent',
    with: 'user:id,email,name|invoice:id,total,status',
    fields: 'id,order_date,status,total_amount',
    limit: 20
  })
})
.then(res => res.json())
.then(data => {
  console.log(data);
  // {
  //   status: 'success',
  //   message: 'Records retrieved successfully',
  //   data: [
  //     {
  //       id: 123,
  //       order_date: '2025-01-15',
  //       status: 'pending',
  //       total_amount: 150.00,
  //       user: { id: 1, email: 'user@example.com', name: 'John Doe' },
  //       invoice: { id: 456, total: 150.00, status: 'draft' }
  //     }
  //   ]
  // }
});
```

### 2. Obtener Schema de un Modelo

```javascript
fetch('/api', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    model: 'order',
    action: 'schema'
  })
})
.then(res => res.json())
.then(data => {
  console.log(data.data.schema);
  // {
  //   id: { type: 'integer', fillable: false, primary: true },
  //   user_id: { type: 'integer', fillable: true, primary: false },
  //   order_date: { type: 'datetime', fillable: true, primary: false },
  //   ...
  // }

  console.log(data.data.available_relations);
  // {
  //   user: { type: 'BelongsTo', related_model: 'User' },
  //   invoice: { type: 'BelongsTo', related_model: 'Invoice' }
  // }
});
```

### 3. Crear Nueva Orden

```javascript
fetch('/api', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    model: 'order',
    action: 'create',
    user_id: 1,
    order_date: '2025-01-16',
    status: 'pending',
    items: [
      { plan_id: 1, quantity: 2, price: 50 }
    ],
    total_amount: 100
  })
})
.then(res => res.json())
.then(data => {
  console.log(data);
  // {
  //   status: 'success',
  //   message: 'Record created successfully',
  //   data: { id: 789, user_id: 1, ... }
  // }
});
```

### 4. Actualizar Orden

```javascript
fetch('/api', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    model: 'order',
    action: 'update',
    id: 789,
    status: 'completed'
  })
})
.then(res => res.json())
.then(data => {
  console.log(data);
  // {
  //   status: 'success',
  //   message: 'Record updated successfully',
  //   data: { id: 789, status: 'completed', ... }
  // }
});
```

### 5. Usar CloudsForm en una Página

**Archivo:** `pages/dashboard-new-order.htm`

```ini
title = "Nueva Orden"
url = "/dashboard/orders/new"
layout = "dashboard"

[cloudsForm orderForm]
model = "Order"
redirectTo = "/dashboard/orders/{id}"
requireAuth = 1
successMessage = "Orden creada exitosamente"
==
<div class="container">
    <h1>Crear Nueva Orden</h1>

    <form data-request="orderForm::onCreate" data-request-flash>
        <div class="form-group">
            <label>Plan</label>
            <select name="plan_id" required>
                <!-- opciones -->
            </select>
        </div>

        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="quantity" value="1" required />
        </div>

        <button type="submit" class="btn btn-primary">
            Crear Orden
        </button>
    </form>
</div>
```

---

## Integración con Microfrontends

### Ventajas para Microfrontends Aislados

1. **Endpoint único y predecible:** `/api`
2. **Auto-documentación:** `action=schema` para descubrir estructura
3. **Relaciones flexibles:** Cada microfrontend pide solo lo que necesita
4. **Cache inteligente:** Reduce carga en consultas frecuentes
5. **Respuestas consistentes:** Siempre JSON con estructura `{status, message, data}`

### Ejemplo: Microfrontend de Órdenes (React)

```javascript
// hooks/useOrders.js
import { useState, useEffect } from 'react';

export function useOrders(filters = {}) {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('/api', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        model: 'order',
        action: 'list',
        scope: filters.scope || 'recent',
        with: 'user:id,email|invoice:id,total,status',
        fields: 'id,order_date,status,total_amount',
        limit: filters.limit || 20
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        setOrders(data.data);
      }
      setLoading(false);
    });
  }, [filters]);

  return { orders, loading };
}

// components/OrdersList.jsx
function OrdersList() {
  const { orders, loading } = useOrders({ scope: 'pending,recent' });

  if (loading) return <div>Cargando...</div>;

  return (
    <ul>
      {orders.map(order => (
        <li key={order.id}>
          Orden #{order.id} - {order.user.email} - ${order.total_amount}
          <span className={`status-${order.status}`}>{order.status}</span>
        </li>
      ))}
    </ul>
  );
}
```

---

## Troubleshooting

### Problema: "Model not found"

**Causa:** El modelo no existe o el nombre está mal escrito.

**Solución:**
1. Verificar que el modelo existe en `/plugins/aero/clouds/models/`
2. Usar el nombre correcto (case-sensitive): `Order` no `order`
3. Revisar logs: `tail -f storage/logs/system.log | grep "Clouds API"`

### Problema: Cache no se invalida

**Causa:** Redis no está disponible o hay un error en la invalidación.

**Solución:**
1. Verificar Redis: `redis-cli ping` (debe retornar PONG)
2. Ver estadísticas: `GET /api/cache/stats`
3. Limpiar manualmente: `POST /api/cache/flush`
4. Invalidar modelo específico: `POST /api/cache/invalidate/order`

### Problema: Scope no funciona

**Causa:** El scope no existe en el modelo o tiene parámetros requeridos.

**Solución:**
1. Verificar que el modelo tiene el método `scopeNombre()`
2. Los scopes con parámetros no son soportados (solo scopes sin parámetros)
3. Revisar logs para ver el error específico

### Problema: Relación no carga

**Causa:** La relación no existe o no está definida correctamente en el modelo.

**Solución:**
1. Verificar que el modelo tiene el método de relación (ej: `user()`)
2. Usar `action=schema` para ver relaciones disponibles
3. Verificar sintaxis del parámetro `with`: `user:id,email` (no spaces)

---

## Resumen de Archivos Creados

```
✅ /plugins/aero/clouds/classes/ApiDispatcher.php
✅ /plugins/aero/clouds/classes/ApiCache.php
✅ /plugins/aero/clouds/traits/FormHandler.php
✅ /plugins/aero/clouds/components/CloudsForm.php
✅ /plugins/aero/clouds/routes.php
✅ /plugins/aero/clouds/Plugin.php (modificado)
✅ /plugins/aero/clouds/CLOUDS_API_SYSTEM.md (este archivo)
```

---

## Próximos Pasos Recomendados

1. **Testing:** Probar todos los endpoints con Postman o Thunder Client
2. **Seguridad:** Agregar middleware de autenticación/autorización si es necesario
3. **Rate Limiting:** Implementar límite de peticiones por IP/usuario
4. **Logging:** Configurar logging de peticiones de API para analytics
5. **Documentación Frontend:** Documentar uso específico para cada microfrontend

---

**Última actualización:** 2025-01-16
**Mantenedor:** Aero
**Contacto:** clouds.com.bo
