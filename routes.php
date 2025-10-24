<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Aero\Clouds\Classes\ApiDispatcher;
use Aero\Clouds\Classes\ApiCache;

/*
|--------------------------------------------------------------------------
| Clouds API Routes
|--------------------------------------------------------------------------
|
| Sistema híbrido de API REST:
| - ApiDispatcher: Para consultas complejas y lectura (microfrontends, SPAs)
| - FormHandler/CloudsForm: Para formularios simples del frontend web (ya manejados por componentes)
|
| El endpoint principal /api maneja todas las operaciones de lectura/escritura.
| El antiguo /api de aero/manager fue movido a /api/manager
|
*/

/**
 * API Principal - Dispatcher con cache inteligente
 *
 * Endpoint unificado para todas las operaciones CRUD.
 * Optimizado con Redis cache para consultas grandes.
 *
 * Uso:
 * POST /api
 * {
 *   "model": "order",
 *   "action": "list",
 *   "scope": "pending,recent",
 *   "with": "user:id,email|invoice:id,total",
 *   "fields": "id,order_date,status,total_amount",
 *   "limit": 20
 * }
 *
 * Actions disponibles:
 * - list:   Listar registros (con filtros, scopes, relaciones)
 * - get:    Obtener un registro (por ID o slug)
 * - schema: Auto-documentación del modelo
 * - create: Crear nuevo registro
 * - update: Actualizar registro
 * - delete: Eliminar registro
 */
Route::any('/api', function (Request $request) {
    $dispatcher = new ApiDispatcher();
    return $dispatcher->handle($request);
});

/**
 * Utilities - Gestión de cache
 */
Route::group(['prefix' => 'api/cache'], function() {

    // Estadísticas del cache
    Route::get('/stats', function() {
        $stats = ApiCache::stats();
        return response()->json([
            'status' => 'success',
            'data' => $stats
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    });

    // Limpiar todo el cache
    Route::post('/flush', function() {
        $count = ApiCache::flush();
        return response()->json([
            'status' => 'success',
            'message' => "Cache flushed: {$count} keys deleted"
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    });

    // Invalidar cache de un modelo específico
    Route::post('/invalidate/{model}', function($model) {
        $modelClass = "Aero\\Clouds\\Models\\" . ucfirst($model);

        if (!class_exists($modelClass)) {
            return response()->json([
                'status' => 'error',
                'message' => "Model {$model} not found"
            ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $count = ApiCache::invalidateModel($modelClass);

        return response()->json([
            'status' => 'success',
            'message' => "Cache invalidated for model {$model}: {$count} keys deleted"
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    });
});

/**
 * Shortcuts RESTful (opcional - para compatibilidad)
 *
 * Endpoints alternativos estilo REST tradicional.
 * Internamente usan el mismo ApiDispatcher.
 */
Route::group(['prefix' => 'api'], function() {

    // GET /api/orders - Listar órdenes
    Route::get('/{model}', function(Request $request, $model) {
        $request->merge(['model' => $model, 'action' => 'list']);
        $dispatcher = new ApiDispatcher();
        return $dispatcher->handle($request);
    });

    // GET /api/orders/123 - Obtener orden específica
    Route::get('/{model}/{id}', function(Request $request, $model, $id) {
        $request->merge(['model' => $model, 'action' => 'get', 'id' => $id]);
        $dispatcher = new ApiDispatcher();
        return $dispatcher->handle($request);
    });

    // POST /api/orders - Crear orden
    Route::post('/{model}', function(Request $request, $model) {
        $request->merge(['model' => $model, 'action' => 'create']);
        $dispatcher = new ApiDispatcher();
        return $dispatcher->handle($request);
    });

    // PUT/PATCH /api/orders/123 - Actualizar orden
    Route::match(['put', 'patch'], '/{model}/{id}', function(Request $request, $model, $id) {
        $request->merge(['model' => $model, 'action' => 'update', 'id' => $id]);
        $dispatcher = new ApiDispatcher();
        return $dispatcher->handle($request);
    });

    // DELETE /api/orders/123 - Eliminar orden
    Route::delete('/{model}/{id}', function(Request $request, $model, $id) {
        $request->merge(['model' => $model, 'action' => 'delete', 'id' => $id]);
        $dispatcher = new ApiDispatcher();
        return $dispatcher->handle($request);
    });
});
