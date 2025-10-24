<?php namespace Aero\Clouds\Classes;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Aero\Clouds\Classes\ApiCache;
use Auth;

/**
 * ApiDispatcher for Aero\Clouds
 *
 * Sistema de API REST unificado con cache inteligente.
 * Adaptado de aero/manager con optimizaciones para aero/clouds.
 *
 * Endpoints disponibles:
 * - list:   Listar registros con filtros, scopes, y relaciones
 * - get:    Obtener un registro específico por ID o slug
 * - schema: Auto-documentación del modelo
 * - create: Crear nuevo registro
 * - update: Actualizar registro existente
 * - delete: Eliminar registro
 *
 * Uso desde frontend:
 * ```javascript
 * fetch('/api/clouds', {
 *   method: 'POST',
 *   body: JSON.stringify({
 *     model: 'order',
 *     action: 'list',
 *     scope: 'pending,recent',
 *     with: 'user:id,email|invoice:id,total',
 *     fields: 'id,order_date,status,total_amount',
 *     limit: 20
 *   })
 * })
 * ```
 */
class ApiDispatcher extends Controller
{
    /**
     * Manejar la petición de API
     */
    public function handle(Request $request)
    {
        $modelParam = $request->input('model');
        $action = $request->input('action', 'list');
        $id = $request->input('id');
        $slug = $request->input('slug');
        $task = $request->input('task');
        $fields = $request->input('fields');
        $withInput = $request->input('with');

        Log::info(
            "[Clouds API] Request: action='{$action}', model='{$modelParam}', id='{$id}', slug='{$slug}'"
        );

        // Validar modelo
        if (!$modelParam && !in_array(strtolower($action), ['stats', 'cache_stats'])) {
            Log::warning("[Clouds API] Missing model parameter");
            return $this->errorResponse('Missing model parameter', 400);
        }

        $modelClass = null;
        $finalClassName = '';

        if ($modelParam) {
            $modelClass = $this->resolveModelClass($modelParam);

            if (!$modelClass) {
                return $this->errorResponse("Model '{$modelParam}' not found", 404);
            }

            $finalClassName = class_basename($modelClass);
        }

        // Parsear parámetro 'with' para relaciones
        $parsedWithData = $this->parseWithParameter($withInput);

        try {
            switch (strtolower($action)) {
                case 'list':
                    return $this->actionList($request, $modelClass, $finalClassName, $fields, $parsedWithData);

                case 'get':
                    return $this->actionGet($request, $modelClass, $finalClassName, $id, $slug, $fields, $parsedWithData);

                case 'schema':
                    return $this->actionSchema($modelClass, $finalClassName);

                case 'create':
                case 'update':
                    return $this->actionCreateUpdate($request, $modelClass, $finalClassName, $action, $id, $slug, $fields, $parsedWithData);

                case 'delete':
                    return $this->actionDelete($modelClass, $finalClassName, $id, $slug);

                case 'cache_stats':
                    return $this->actionCacheStats();

                default:
                    return $this->errorResponse("Action '{$action}' not supported", 400);
            }
        } catch (\Exception $e) {
            Log::error("[Clouds API] Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            return $this->errorResponse('API Exception: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Action: List - Listar registros
     */
    protected function actionList(Request $request, $modelClass, $finalClassName, $fields, $parsedWithData)
    {
        // Generar clave de cache
        $cacheParams = [
            'scope' => $request->input('scope'),
            'with' => $request->input('with'),
            'fields' => $fields,
            'limit' => $request->input('limit', 50),
            'user_id' => Auth::check() ? Auth::id() : null
        ];

        $cacheKey = ApiCache::generateKey($finalClassName, 'list', $cacheParams);

        // Intentar obtener de cache
        $results = ApiCache::remember($cacheKey, function() use ($request, $modelClass, $finalClassName, $parsedWithData, $fields) {
            $query = $modelClass::query();

            // Aplicar scopes
            $this->applyScopes($query, $request->input('scope'), $finalClassName);

            // Eager load relaciones
            if (!empty($parsedWithData['relations'])) {
                $query->with(array_keys($parsedWithData['relations']));
            }

            // Filtrar por ID o slug si se proporciona
            if ($id = $request->input('id')) {
                $query->where((new $modelClass())->getKeyName(), $id);
            } elseif ($slug = $request->input('slug')) {
                $query->where('slug', $slug);
            }

            // Limitar resultados
            $limit = $request->input('limit', 50);
            $results = $query->limit($limit)->get();

            // Procesar registros
            $output = [];
            foreach ($results as $record) {
                $output[] = $this->processRecord($record, $fields, $parsedWithData);
            }

            return $output;

        }, ApiCache::getTTL('list', 0), [$finalClassName]);

        return $this->successResponse('Records retrieved successfully', $results);
    }

    /**
     * Action: Get - Obtener un registro específico
     */
    protected function actionGet(Request $request, $modelClass, $finalClassName, $id, $slug, $fields, $parsedWithData)
    {
        if (!$id && !$slug) {
            return $this->errorResponse('Missing id or slug parameter', 400);
        }

        // Generar clave de cache
        $cacheKey = ApiCache::generateKey($finalClassName, 'get', [
            'id' => $id,
            'slug' => $slug,
            'with' => $request->input('with'),
            'fields' => $fields
        ]);

        $record = ApiCache::remember($cacheKey, function() use ($request, $modelClass, $id, $slug, $parsedWithData) {
            $query = $modelClass::query();

            // Aplicar scopes
            $this->applyScopes($query, $request->input('scope'), class_basename($modelClass));

            // Eager load relaciones
            if (!empty($parsedWithData['relations'])) {
                $query->with(array_keys($parsedWithData['relations']));
            }

            return $id ? $query->find($id) : $query->where('slug', $slug)->first();

        }, ApiCache::getTTL('get'), [$finalClassName]);

        if (!$record) {
            return $this->errorResponse('Record not found', 404);
        }

        $data = $this->processRecord($record, $fields, $parsedWithData);

        return $this->successResponse('Record retrieved successfully', $data);
    }

    /**
     * Action: Schema - Auto-documentación del modelo
     */
    protected function actionSchema($modelClass, $finalClassName)
    {
        // Schemas se cachean por mucho tiempo (casi nunca cambian)
        $cacheKey = ApiCache::generateKey($finalClassName, 'schema', []);

        $schema = ApiCache::remember($cacheKey, function() use ($modelClass) {
            $tempInstance = new $modelClass();
            $schema = [];
            $fillable = $tempInstance->getFillable();
            $casts = $tempInstance->getCasts();
            $dates = $tempInstance->getDates();
            $primaryKey = $tempInstance->getKeyName();

            try {
                $table = $tempInstance->getTable();
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);

                foreach ($columns as $column) {
                    $type = \Illuminate\Support\Facades\Schema::getColumnType($table, $column);
                    if (in_array($column, $dates)) $type = 'datetime';

                    $schema[$column] = [
                        'type' => $casts[$column] ?? $type,
                        'fillable' => in_array($column, $fillable),
                        'primary' => $column === $primaryKey,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("[Clouds API Schema] Could not get DB schema: " . $e->getMessage());
            }

            // Agregar accessors/appends
            if (method_exists($tempInstance, 'getAppends')) {
                foreach ($tempInstance->getAppends() as $appended) {
                    if (!isset($schema[$appended])) {
                        $schema[$appended] = [
                            'type' => $casts[$appended] ?? 'accessor',
                            'fillable' => false,
                            'primary' => false,
                        ];
                    }
                }
            }

            // Detectar relaciones disponibles
            $availableRelations = $this->detectRelations($modelClass);

            return [
                'schema' => $schema,
                'available_relations' => $availableRelations
            ];

        }, ApiCache::SCHEMA_TTL, [$finalClassName]);

        return $this->successResponse('Schema retrieved successfully', $schema, [
            'model' => $finalClassName
        ]);
    }

    /**
     * Action: Create/Update - Crear o actualizar registro
     */
    protected function actionCreateUpdate(Request $request, $modelClass, $finalClassName, $action, $id, $slug, $fields, $parsedWithData)
    {
        $isUpdate = strtolower($action) === 'update';

        // Buscar registro si es update
        if ($isUpdate) {
            if (!$id && !$slug) {
                return $this->errorResponse('Missing id or slug for update', 400);
            }

            $modelInstance = $id ? $modelClass::find($id) : $modelClass::where('slug', $slug)->first();

            if (!$modelInstance) {
                return $this->errorResponse('Record not found for update', 404);
            }
        } else {
            $modelInstance = new $modelClass();
        }

        // Obtener datos del request
        $requestData = $request->except(['model', 'action', 'id', 'slug', 'scope', 'task', 'fields', 'with', '_token']);

        // Validar datos
        $validation = $this->validateModelData($modelInstance, $requestData, $isUpdate);

        if ($validation !== true) {
            return $validation; // Error response
        }

        // Guardar
        $modelInstance->fill($requestData);
        $modelInstance->save();

        // Invalidar cache del modelo
        ApiCache::invalidateModel($modelClass);

        // Cargar relaciones si se especificaron
        if (!empty($parsedWithData['relations'])) {
            $modelInstance->load(array_keys($parsedWithData['relations']));
        }

        $data = $this->processRecord($modelInstance, $fields, $parsedWithData);
        $message = $isUpdate ? 'Record updated successfully' : 'Record created successfully';
        $statusCode = $isUpdate ? 200 : 201;

        return $this->successResponse($message, $data, [], $statusCode);
    }

    /**
     * Action: Delete - Eliminar registro
     */
    protected function actionDelete($modelClass, $finalClassName, $id, $slug)
    {
        if (!$id && !$slug) {
            return $this->errorResponse('Missing id or slug for delete', 400);
        }

        $modelInstance = $id ? $modelClass::find($id) : $modelClass::where('slug', $slug)->first();

        if (!$modelInstance) {
            return $this->errorResponse('Record not found for delete', 404);
        }

        $modelInstance->delete();

        // Invalidar cache del modelo
        ApiCache::invalidateModel($modelClass);

        return $this->successResponse('Record deleted successfully');
    }

    /**
     * Action: Cache Stats - Estadísticas del cache
     */
    protected function actionCacheStats()
    {
        $stats = ApiCache::stats();
        return $this->successResponse('Cache statistics', $stats);
    }

    /**
     * Resolver clase del modelo
     */
    protected function resolveModelClass($modelParam)
    {
        $baseClassName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $modelParam)));

        $possibleNamespaces = [
            "Aero\\Clouds\\Models\\" . $baseClassName,
            "RainLab\\User\\Models\\" . $baseClassName,
            $baseClassName
        ];

        foreach ($possibleNamespaces as $potentialClass) {
            if (class_exists($potentialClass)) {
                Log::debug("[Clouds API] Model found: {$potentialClass}");
                return $potentialClass;
            }
        }

        // Fallback: buscar en archivos
        $modelsPath = plugins_path('aero/clouds/models');
        $files = @scandir($modelsPath);

        if ($files) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $fileName = pathinfo($file, PATHINFO_FILENAME);
                    if (strcasecmp($fileName, $baseClassName) === 0) {
                        $modelClass = "Aero\\Clouds\\Models\\" . $fileName;
                        if (class_exists($modelClass)) {
                            return $modelClass;
                        }
                    }
                }
            }
        }

        Log::warning("[Clouds API] Model not found: {$modelParam}");
        return null;
    }

    /**
     * Aplicar scopes dinámicos
     */
    protected function applyScopes(&$query, $scopesInput, $modelName)
    {
        if (!$scopesInput) return;

        $scopeNames = array_map('trim', explode(',', $scopesInput));

        foreach ($scopeNames as $scopeName) {
            if (empty($scopeName)) continue;

            $scopeMethod = 'scope' . Str::studly($scopeName);

            if (method_exists($query->getModel(), $scopeMethod)) {
                try {
                    $query = $query->{$scopeName}();
                    Log::debug("[Clouds API] Applied scope '{$scopeName}' to {$modelName}");
                } catch (\Exception $e) {
                    Log::error("[Clouds API] Error applying scope '{$scopeName}': " . $e->getMessage());
                }
            } else {
                Log::warning("[Clouds API] Scope '{$scopeName}' not found on {$modelName}");
            }
        }
    }

    /**
     * Parsear parámetro 'with' para relaciones
     */
    protected function parseWithParameter($withParam)
    {
        $parsed = ['relations' => []];

        if (empty($withParam)) {
            return $parsed;
        }

        $relationsParts = explode('|', $withParam);

        foreach ($relationsParts as $part) {
            if (empty($part)) continue;

            $details = explode(':', $part, 2);
            $relationName = trim($details[0]);

            if (empty($relationName)) continue;

            if (isset($details[1]) && !empty(trim($details[1]))) {
                $fields = array_map('trim', explode(',', trim($details[1])));
                $parsed['relations'][$relationName] = !empty($fields) ? $fields : ['*'];
            } else {
                $parsed['relations'][$relationName] = ['*'];
            }
        }

        return $parsed;
    }

    /**
     * Procesar registro para output
     */
    protected function processRecord(Model $record, $fieldsCsv, array $parsedWithData = [])
    {
        $data = [];
        $requestedRelations = $parsedWithData['relations'] ?? [];

        // Procesar campos específicos o todos
        if (empty($fieldsCsv)) {
            // Todos los atributos
            foreach ($record->getAttributes() as $key => $value) {
                $data[$key] = $this->processValue($record->getAttribute($key));
            }

            // Appends
            if (method_exists($record, 'getAppends')) {
                foreach ($record->getAppends() as $appendedField) {
                    if (!array_key_exists($appendedField, $data)) {
                        $data[$appendedField] = $this->processValue($record->{$appendedField});
                    }
                }
            }
        } else {
            // Solo campos específicos
            $fieldList = array_map('trim', explode(',', $fieldsCsv));

            foreach ($fieldList as $fieldKey) {
                if (!isset($requestedRelations[$fieldKey])) {
                    if (array_key_exists($fieldKey, $record->getAttributes())) {
                        $data[$fieldKey] = $this->processValue($record->getAttribute($fieldKey));
                    } elseif (in_array($fieldKey, $record->getAppends())) {
                        $data[$fieldKey] = $this->processValue($record->{$fieldKey});
                    }
                }
            }
        }

        // Procesar relaciones
        foreach ($requestedRelations as $relationName => $relationFields) {
            if ($record->relationLoaded($relationName)) {
                $relationData = $record->getRelation($relationName);
                $data[$relationName] = $this->extractRelationData($relationData, $relationFields);
            }
        }

        return $data;
    }

    /**
     * Extraer datos de relación
     */
    protected function extractRelationData($modelOrCollection, array $fieldsToSelect)
    {
        if (is_null($modelOrCollection)) {
            return null;
        }

        $isAllFields = ($fieldsToSelect === ['*'] || empty($fieldsToSelect));

        if ($modelOrCollection instanceof EloquentCollection) {
            return $modelOrCollection->map(function (Model $item) use ($isAllFields, $fieldsToSelect) {
                return $this->extractFieldsFromItem($item, $isAllFields, $fieldsToSelect);
            })->toArray();
        } elseif ($modelOrCollection instanceof Model) {
            return $this->extractFieldsFromItem($modelOrCollection, $isAllFields, $fieldsToSelect);
        }

        return $this->processValue($modelOrCollection);
    }

    /**
     * Extraer campos específicos de un item
     */
    protected function extractFieldsFromItem(Model $item, bool $isAllFields, array $fieldsToSelect)
    {
        $itemData = [];

        if ($isAllFields) {
            foreach ($item->getAttributes() as $key => $value) {
                $itemData[$key] = $this->processValue($item->getAttribute($key));
            }

            if (method_exists($item, 'getAppends')) {
                foreach ($item->getAppends() as $appendedField) {
                    if (!array_key_exists($appendedField, $itemData)) {
                        $itemData[$appendedField] = $this->processValue($item->{$appendedField});
                    }
                }
            }
        } else {
            foreach ($fieldsToSelect as $fieldKey) {
                if (array_key_exists($fieldKey, $item->getAttributes())) {
                    $itemData[$fieldKey] = $this->processValue($item->getAttribute($fieldKey));
                } elseif (in_array($fieldKey, $item->getAppends())) {
                    $itemData[$fieldKey] = $this->processValue($item->{$fieldKey});
                } else {
                    $itemData[$fieldKey] = null;
                }
            }
        }

        return $itemData;
    }

    /**
     * Procesar valor (manejar archivos, etc.)
     */
    protected function processValue($value)
    {
        if ($value instanceof \System\Models\File) {
            return $value->getPath();
        } elseif ($value instanceof EloquentCollection && $value->count() > 0 && $value->first() instanceof \System\Models\File) {
            return $value->map(fn($file) => $file->getPath());
        }

        return $value;
    }

    /**
     * Detectar relaciones disponibles en el modelo
     */
    protected function detectRelations($modelClass)
    {
        $availableRelations = [];
        $tempInstance = new $modelClass();
        $reflection = new \ReflectionClass($modelClass);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0) continue;
            if (Str::startsWith($method->name, '__')) continue;

            try {
                $relationCheck = $tempInstance->{$method->name}();

                if ($relationCheck instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $relationType = class_basename(get_class($relationCheck));
                    $relatedModel = class_basename($relationCheck->getRelated());

                    $availableRelations[$method->name] = [
                        'type' => $relationType,
                        'related_model' => $relatedModel
                    ];
                }
            } catch (\Throwable $e) {
                // Ignorar métodos que no son relaciones
            }
        }

        return $availableRelations;
    }

    /**
     * Validar datos del modelo
     */
    protected function validateModelData($modelInstance, $requestData, $isUpdate = false)
    {
        $rules = [];

        if (method_exists($modelInstance, 'getValidationRules')) {
            $rules = $modelInstance->getValidationRules($isUpdate ? $modelInstance->getKey() : null, $requestData);
        } elseif (property_exists($modelInstance, 'rules')) {
            $rules = is_array($modelInstance->rules) ? $modelInstance->rules : [];
        }

        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make($requestData, $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, [
                'errors' => $validator->errors()
            ]);
        }

        return true;
    }

    /**
     * Respuesta de éxito
     */
    protected function successResponse($message, $data = null, $meta = [], $statusCode = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Respuesta de error
     */
    protected function errorResponse($message, $statusCode = 400, $extra = [])
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        return response()->json($response, $statusCode, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
