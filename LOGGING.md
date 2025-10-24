# Sistema de Logging de Actividad - Plugin Aero.Clouds

## Descripción General

El plugin Aero.Clouds ahora incluye un sistema completo de logging de actividad que registra automáticamente todos los eventos importantes relacionados con:

- **Modelos**: Creación, actualización y eliminación
- **Autenticación**: Login y logout de usuarios
- **Operaciones de archivos**: Uploads
- **Eventos personalizados**: Cualquier evento que desees registrar

## Componentes del Sistema

### 1. Tabla de Base de Datos

**Tabla:** `aero_clouds_activity_logs`

**Campos:**
- `id` - ID único del log
- `log_name` - Nombre/categoría del log (ej: "authentication", "service", "invoice")
- `description` - Descripción legible del evento
- `subject_type` - Tipo del modelo afectado (clase completa)
- `subject_id` - ID del modelo afectado
- `causer_type` - Tipo del usuario que causó el evento
- `causer_id` - ID del usuario que causó el evento
- `properties` - Datos JSON con detalles adicionales
- `event` - Tipo de evento (created, updated, deleted, login, etc.)
- `ip_address` - Dirección IP del usuario
- `user_agent` - User Agent del navegador
- `created_at` - Fecha y hora del evento
- `updated_at` - Fecha de actualización

### 2. Trait LogsActivity

**Ubicación:** `plugins/aero/clouds/traits/LogsActivity.php`

Este trait se aplica automáticamente a todos los modelos y registra:

#### Eventos Automáticos:
- `created` - Cuando se crea un registro
- `updated` - Cuando se actualiza un registro (incluye valores antiguos y nuevos)
- `deleted` - Cuando se elimina un registro

#### Propiedades Configurables en el Modelo:

```php
class MiModelo extends Model
{
    use \Aero\Clouds\Traits\LogsActivity;

    // Opcional: Especificar el nombre del log
    protected $logName = 'mi_modulo';

    // Opcional: Especificar qué campo usar como identificador en los logs
    protected $logIdentifier = 'name'; // Por defecto busca: name, title, email, number, id
}
```

### 3. Modelos con Logging Activo

Los siguientes modelos ya tienen logging habilitado:

- `Service`
- `Plan`
- `Feature`
- `Addon`
- `Order`
- `Invoice`
- `Faq`
- `Doc`

### 4. Backend Controller

**Ubicación:** `Clouds Manager > Activity Logs`

Características:
- Visualización de todos los logs en tabla
- Búsqueda por descripción, IP, ID
- Filtros por:
  - Tipo de log
  - Tipo de evento
  - Tipo de sujeto
  - Rango de fechas
- Botones para eliminar logs antiguos (30 días, 90 días)
- Botón para limpiar todos los logs
- Modal con detalles JSON de cada entrada

## Uso

### Logging Automático en Modelos

Solo necesitas agregar el trait al modelo:

```php
<?php namespace Aero\Clouds\Models;

use Model;

class MiNuevoModelo extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity; // ← Agregar esta línea

    // ... resto del modelo
}
```

Ahora, cada vez que se cree, actualice o elimine un registro, se registrará automáticamente.

### Logging Manual de Eventos Personalizados

Puedes registrar eventos personalizados desde cualquier parte del código:

```php
// Desde un modelo
$invoice = Invoice::find(1);
$invoice->log('payment_received', 'Payment received', [
    'amount' => 100.50,
    'method' => 'credit_card',
    'transaction_id' => 'TRX123456'
]);

// Desde un controlador
use Aero\Clouds\Models\ActivityLog;

ActivityLog::create([
    'log_name' => 'custom_action',
    'description' => 'Custom action performed',
    'event' => 'custom_event',
    'properties' => [
        'key1' => 'value1',
        'key2' => 'value2'
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent()
]);
```

### Consultar Logs

```php
use Aero\Clouds\Models\ActivityLog;

// Todos los logs
$allLogs = ActivityLog::all();

// Logs de un tipo específico
$authLogs = ActivityLog::inLog('authentication')->get();

// Logs de un evento específico
$createdLogs = ActivityLog::forEvent('created')->get();

// Logs de un modelo específico
$invoice = Invoice::find(1);
$invoiceLogs = ActivityLog::forSubject($invoice)->get();
// O usar la relación:
$invoiceLogs = $invoice->activityLogs;

// Logs causados por un usuario
$user = BackendAuth::getUser();
$userLogs = ActivityLog::causedBy($user)->get();

// Combinaciones
$recentInvoiceUpdates = ActivityLog::inLog('invoice')
    ->forEvent('updated')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();
```

### Eventos del Sistema Registrados Automáticamente

#### Autenticación
```php
// Ya configurado en Plugin.php
Event::listen('backend.user.login', ...);
Event::listen('backend.user.logout', ...);
```

#### Uploads de Archivos
```php
// Ya configurado en Plugin.php
Event::listen('system.file.upload', ...);
```

### Agregar Nuevos Eventos del Sistema

Edita `plugins/aero/clouds/Plugin.php` en el método `boot()`:

```php
public function boot()
{
    // ... eventos existentes ...

    // Nuevo evento personalizado
    Event::listen('mi.evento.personalizado', function ($data) {
        ActivityLog::create([
            'log_name' => 'mi_categoria',
            'description' => 'Mi evento ocurrió',
            'event' => 'mi_evento',
            'properties' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    });
}
```

## Mantenimiento

### Limpieza de Logs Antiguos

**Desde el Backend:**
1. Ir a `Clouds Manager > Activity Logs`
2. Usar botones del toolbar:
   - "Delete Logs Older Than 30 Days"
   - "Delete Logs Older Than 90 Days"
   - "Clear All Logs" (elimina TODO)

**Desde Código:**
```php
// Eliminar logs de más de 30 días
ActivityLog::where('created_at', '<', now()->subDays(30))->delete();

// Eliminar todos los logs
ActivityLog::truncate();
```

**Automatización con Scheduled Tasks:**

Puedes agregar un comando programado en `Plugin.php`:

```php
public function registerSchedule($schedule)
{
    // Limpiar logs cada mes
    $schedule->call(function () {
        ActivityLog::where('created_at', '<', now()->subDays(90))->delete();
    })->monthly();
}
```

## Seguridad y Privacidad

- Los logs incluyen direcciones IP y user agents
- Las propiedades pueden contener datos sensibles
- Considera implementar permisos para acceder a los logs
- Implementa políticas de retención de datos según tus necesidades legales
- Los passwords y datos sensibles NO deberían ser registrados en `properties`

## Ejemplo Completo: Nuevo Formulario con Logging

```php
<?php namespace Aero\Clouds\Models;

use Model;

class Customer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\LogsActivity;

    protected $table = 'aero_clouds_customers';

    // Personalizar el nombre del log
    protected $logName = 'customer';

    // Usar el campo 'email' como identificador en logs
    protected $logIdentifier = 'email';

    protected $fillable = ['name', 'email', 'phone'];

    public $rules = [
        'name' => 'required',
        'email' => 'required|email|unique:aero_clouds_customers'
    ];
}
```

Ahora, automáticamente:
- Cuando se cree un cliente: `Created Customer (john@example.com)`
- Cuando se actualice: `Updated Customer (john@example.com)` + diff de cambios
- Cuando se elimine: `Deleted Customer (john@example.com)`

## Troubleshooting

### Los logs no se están creando

1. Verifica que el trait esté agregado al modelo
2. Verifica que la migración se haya ejecutado: `php artisan october:migrate`
3. Revisa los logs de errores en `storage/logs/system.log`

### Problemas de rendimiento

Si hay demasiados logs:
- Implementa limpieza automática programada
- Considera indexar campos adicionales si haces consultas frecuentes
- Archiva logs antiguos en tablas separadas

### Error al guardar logs

Verifica permisos de la base de datos y que la tabla exista:
```sql
SHOW TABLES LIKE 'aero_clouds_activity_logs';
```

## Roadmap Futuro

Posibles mejoras:
- [ ] Dashboard de analytics de actividad
- [ ] Exportación de logs a CSV/JSON
- [ ] Alertas por eventos específicos
- [ ] Visualización de timeline por usuario
- [ ] Restauración de registros eliminados (soft deletes)
- [ ] Auditoría de cambios con diff visual
