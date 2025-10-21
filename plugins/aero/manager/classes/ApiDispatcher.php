<?php

namespace Aero\Manager\Classes;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ApiDispatcher extends Controller
{
    // ... (código anterior del constructor, parseWithParameter, processRecord, extractRelationData, extractFieldsFromItem) ...

    public function handle(Request $request)
    {
        $modelParam = $request->input('model');
        $action = $request->input('action', 'list');
        $id = $request->input('id');
        $slug = $request->input('slug');
        // $scope ya no se lee aquí directamente, se leerá $scopesInput más abajo
        $task = $request->input('task');
        $fields = $request->input('fields'); 
        $withInput = $request->input('with');

        Log::info(
            "[ApiDispatcher] Received Request. Action='{$action}', ModelParam='{$modelParam}', ScopesParam='" . $request->input('scope') . "', ID='{$id}', Slug='{$slug}', Fields='{$fields}', With='{$withInput}', HTTP_HOST='" . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "'"
        );

        // ... (resto de la lógica de validación del modelo y carga de clase, igual que antes) ...
        if (!$modelParam && !in_array(strtolower($action), ['send_email', 'email'])) {
            Log::warning("[ApiDispatcher] Missing model parameter for action '{$action}'.");
            return response()->json([
                'status' => 'error',
                'message' => 'Missing model parameter for the requested action.'
            ], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $modelClass = null;
        $finalClassNameForMsg = '';

        if ($modelParam) {
            $baseClassNameAttempt = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $modelParam)));
            $possibleNamespaces = [
                "Aero\\Manager\\Models\\" . $baseClassNameAttempt,
                "App\\Models\\" . $baseClassNameAttempt, 
                $baseClassNameAttempt 
            ];
            
            foreach ($possibleNamespaces as $potentialModelClass) {
                if (class_exists($potentialModelClass)) {
                    $modelClass = $potentialModelClass;
                    $finalClassNameForMsg = class_basename($modelClass);
                    Log::debug("[ApiDispatcher] Model class found: '{$modelClass}' for param '{$modelParam}'. Final class name: '{$finalClassNameForMsg}'");
                    break;
                }
            }

            if (!$modelClass) {
                 Log::debug("[ApiDispatcher] Primary attempts to find class for '{$modelParam}' (tried: " . implode(', ', $possibleNamespaces) . ") failed. Attempting file-based fallback.");
                $modelsPath = plugins_path('aero/manager/models'); // O app_path('Models')

                $files = @scandir($modelsPath);
                $correctedBaseClassName = null;

                if ($files) {
                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                            $fileNameWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
                            if (strcasecmp($fileNameWithoutExtension, $baseClassNameAttempt) === 0) {
                                $correctedBaseClassName = $fileNameWithoutExtension;
                                $modelClass = "Aero\\Manager\\Models\\" . $correctedBaseClassName;
                                if (!class_exists($modelClass)) {
                                     $modelClass = "App\\Models\\" . $correctedBaseClassName; 
                                }
                                $finalClassNameForMsg = $correctedBaseClassName;
                                Log::debug("[ApiDispatcher] Fallback found '{$correctedBaseClassName}.php'. Attempting class '{$modelClass}'.");
                                break;
                            }
                        }
                    }
                }
                 if (!$modelClass || !class_exists($modelClass)) {
                    Log::warning("[ApiDispatcher] Could not find model class for '{$modelParam}'. Attempted base '{$baseClassNameAttempt}', fallback '{$finalClassNameForMsg}'.");
                    return response()->json([
                        'status' => 'error',
                        'message' => "Model class derived from '{$modelParam}' (tried names like '{$finalClassNameForMsg}') not found."
                    ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                }
            }
        }


        $parsedWithData = $this->parseWithParameter($withInput);

        try {
            switch (strtolower($action)) {
                case 'list':
                    if (!$modelClass) {
                        return response()->json(['status' => 'error', 'message' => 'Model not specified for list action.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    $query = $modelClass::query();

                    // --- INICIO SECCIÓN DE SCOPES MODIFICADA ---
                    $scopesInput = $request->input('scope'); // Ej: "domain,active" o "domain"
                    if ($scopesInput) {
                        $scopeNames = array_map('trim', explode(',', $scopesInput));
                        Log::debug("[ApiDispatcher - Action List] Attempting to apply scopes: " . implode(', ', $scopeNames) . " to model '{$finalClassNameForMsg}'.");
                        foreach ($scopeNames as $individualScopeName) {
                            if (empty($individualScopeName)) {
                                continue;
                            }

                            // El método real en el modelo es 'scope' + NombreDelScopeEnPascalCase
                            // Ej: si $individualScopeName es 'domain', el método es 'scopeDomain'
                            // Ej: si $individualScopeName es 'postsRecientes', el método es 'scopePostsRecientes'
                            $scopeMethodOnModel = 'scope' . Str::studly($individualScopeName); // Str::studly convierte 'mi_scope' o 'mi-scope' a 'MiScope'

                            if (method_exists($modelClass, $scopeMethodOnModel)) {
                                Log::info("[ApiDispatcher - Action List] Applying scope '{$individualScopeName}' (model method '{$scopeMethodOnModel}') to model '{$finalClassNameForMsg}'.");
                                try {
                                    // Se llama al scope en el query builder por su nombre simple (sin 'scope')
                                    // Ej: $query->domain() o $query->postsRecientes()
                                    $query = $query->{$individualScopeName}(); 
                                } catch (\BadMethodCallException $e) {
                                    Log::error("[ApiDispatcher - Action List] BadMethodCallException while applying scope '{$individualScopeName}' to model '{$finalClassNameForMsg}'. Scope might require parameters or is misnamed for dynamic call. Exception: " . $e->getMessage());
                                } catch (\ArgumentCountError $e) {
                                    Log::error("[ApiDispatcher - Action List] ArgumentCountError while applying scope '{$individualScopeName}' to model '{$finalClassNameForMsg}'. Scope likely requires parameters not provided. Exception: " . $e->getMessage());
                                } catch (\Exception $e) {
                                    Log::error("[ApiDispatcher - Action List] Generic error applying scope '{$individualScopeName}' to model '{$finalClassNameForMsg}'. Exception: " . $e->getMessage());
                                }
                            } else {
                                Log::warning("[ApiDispatcher - Action List] Scope '{$individualScopeName}' (expected model method '{$scopeMethodOnModel}') requested but NOT FOUND on model '{$finalClassNameForMsg}'.");
                            }
                        }
                    }
                    // --- FIN SECCIÓN DE SCOPES MODIFICADA ---

                    if (!empty($parsedWithData['relations'])) {
                        $relationsToLoad = array_keys($parsedWithData['relations']);
                        Log::debug("[ApiDispatcher - Action List] Eager loading relations: " . implode(', ', $relationsToLoad));
                        $query = $query->with($relationsToLoad);
                    }

                    if ($id) {
                        $query->where((new $modelClass())->getKeyName(), $id);
                    } elseif ($slug) {
                        $query->where('slug', $slug);
                    }
                    $results = $query->limit($request->input('limit', 50))->get();
                    $output = [];
                    foreach ($results as $record) {
                        $output[] = $this->processRecord($record, $fields, $parsedWithData);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Records retrieved successfully', 'data' => $output], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // ... (resto de los cases: get, schema, create, update, delete, send_email, etc. como estaban en la versión anterior que funcionaba para relaciones)
                // Asegúrate de replicar la lógica de scopes si la necesitas en otros 'cases' como 'get'.
                case 'get':
                    if (!$modelClass) { return response()->json(['status' => 'error', 'message' => 'Model not specified for get action.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                    if (!$id && !$slug) {
                        return response()->json(['status' => 'error', 'message' => 'Missing id or slug parameter for get action'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    $query = $modelClass::query();

                    // --- INICIO SECCIÓN DE SCOPES MODIFICADA (replicada para 'get') ---
                    $scopesInput = $request->input('scope'); 
                    if ($scopesInput) {
                        $scopeNames = array_map('trim', explode(',', $scopesInput));
                        Log::debug("[ApiDispatcher - Action Get] Attempting to apply scopes: " . implode(', ', $scopeNames) . " to model '{$finalClassNameForMsg}'.");
                        foreach ($scopeNames as $individualScopeName) {
                            if (empty($individualScopeName)) continue;
                            $scopeMethodOnModel = 'scope' . Str::studly($individualScopeName);
                            if (method_exists($modelClass, $scopeMethodOnModel)) {
                                Log::info("[ApiDispatcher - Action Get] Applying scope '{$individualScopeName}' (model method '{$scopeMethodOnModel}') to model '{$finalClassNameForMsg}'.");
                                try {
                                    $query = $query->{$individualScopeName}(); 
                                } catch (\BadMethodCallException $e) {
                                    Log::error("[ApiDispatcher - Action Get] BadMethodCallException while applying scope '{$individualScopeName}'. Exception: " . $e->getMessage());
                                } catch (\ArgumentCountError $e) {
                                    Log::error("[ApiDispatcher - Action Get] ArgumentCountError while applying scope '{$individualScopeName}'. Exception: " . $e->getMessage());
                                } catch (\Exception $e) {
                                    Log::error("[ApiDispatcher - Action Get] Generic error applying scope '{$individualScopeName}'. Exception: " . $e->getMessage());
                                }
                            } else {
                                Log::warning("[ApiDispatcher - Action Get] Scope '{$individualScopeName}' (expected '{$scopeMethodOnModel}') NOT FOUND on model '{$finalClassNameForMsg}'.");
                            }
                        }
                    }
                    // --- FIN SECCIÓN DE SCOPES MODIFICADA (replicada para 'get') ---


                    if (!empty($parsedWithData['relations'])) {
                        $relationsToLoad = array_keys($parsedWithData['relations']);
                        Log::debug("[ApiDispatcher - Action Get] Eager loading relations: " . implode(', ', $relationsToLoad));
                        $query = $query->with($relationsToLoad);
                    }
                    
                    $record = $id ? $query->find($id) : $query->where('slug', $slug)->first();

                    if (!$record) {
                        return response()->json(['status' => 'error', 'message' => 'Record not found'], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    return response()->json(['status' => 'success', 'message' => 'Record retrieved successfully', 'data' => $this->processRecord($record, $fields, $parsedWithData)], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                case 'schema':
                    if (!$modelClass) { return response()->json(['status' => 'error', 'message' => 'Model not specified for schema action.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                    
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
                    } catch (\Exception $dbException) {
                        Log::warning("[ApiDispatcher - Schema] Could not get DB schema for table '{$tempInstance->getTable()}'. Falling back. Error: " . $dbException->getMessage());
                        $attributesFromModel = $tempInstance->getAttributes(); 
                         if (empty(array_keys($attributesFromModel)) && method_exists($tempInstance, 'getFillable')) {
                            $attributesFromModel = array_fill_keys($tempInstance->getFillable(), null);
                        }
                        foreach (array_keys($attributesFromModel) as $attr) {
                             $schema[$attr] = [
                                'type' => $casts[$attr] ?? (isset($tempInstance->$attr) ? gettype($tempInstance->$attr) : 'unknown'),
                                'fillable' => in_array($attr, $fillable),
                                'primary' => $attr === $primaryKey,
                            ];
                        }
                    }

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
                    
                    $availableRelations = [];
                    $reflection = new \ReflectionClass($modelClass);
                    foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                        if (Str::startsWith($method->name, '__') || $method->getNumberOfParameters() > 0 || in_array($method->name, ['query', 'newQuery', 'withTrashed', 'onlyTrashed', 'boot', 'initialize', 'getValidationRules', 'getValidationMessages', 'getValidationAttributes', 'scopePublished', 'resolveRouteBinding', 'resolveChildRouteBinding', 'forceFill', 'guard', 'isUnguarded', 'reguard', 'isGuarded', 'isFillable', 'isDateAttribute', 'getHidden', 'getVisible', 'getIncrementing', 'getPerPage', 'getTable', 'getKeyName', 'getQualifiedKeyName', 'getForeignKey', 'getMorphClass', 'getRelations', 'getTouchedRelations', 'touches', 'syncChanges', 'syncOriginal', 'syncOriginalAttribute', 'isDirty', 'isClean', 'wasChanged', 'hasChanges', 'getOriginal', 'getChanges', 'relationLoaded', 'load', 'loadMissing', 'loadCount', 'setRelation', 'setTouchedRelations', 'touchOwners', 'withoutRelations', 'setAppends'])) { continue; }
                        try {
                            $relationInstanceCheck = $tempInstance->{$method->name}();
                            if ($relationInstanceCheck instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                                $relationType = class_basename(get_class($relationInstanceCheck)); 
                                $relatedModel = $relationInstanceCheck->getRelated();
                                $relatedModelName = class_basename($relatedModel);
                                $relatedSchemaFields = [];

                                if ($relatedModel instanceof Model) {
                                    $relatedCasts = $relatedModel->getCasts();
                                    $relatedTable = $relatedModel->getTable();
                                    try {
                                        $relatedDbColumns = \Illuminate\Support\Facades\Schema::getColumnListing($relatedTable);
                                        foreach($relatedDbColumns as $relCol) {
                                            $relatedSchemaFields[$relCol] = $relatedCasts[$relCol] ?? \Illuminate\Support\Facades\Schema::getColumnType($relatedTable, $relCol);
                                        }
                                    } catch (\Exception $e) { /* ignore schema errors for related */ }
                                }
                                $availableRelations[$method->name] = [
                                    'type' => $relationType,
                                    'related_model' => $relatedModelName,
                                    'fields' => $relatedSchemaFields
                                ];
                            }
                        } catch (\Throwable $e) {  }
                    }
                    
                    $response = ['status' => 'success', 'model' => $finalClassNameForMsg, 'schema' => $schema, 'available_relations' => $availableRelations];
                    return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


                case 'create':
                case 'update':
                    if (!$modelClass) { return response()->json(['status' => 'error', 'message' => "Model not specified for {$action} action."], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }

                    $modelInstance = null;
                    $isUpdate = strtolower($action) === 'update';

                    if ($isUpdate) {
                        if (!$id && !$slug) { return response()->json(['status' => 'error', 'message' => 'Missing id or slug parameter for update action'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                        $modelInstance = $id ? $modelClass::find($id) : $modelClass::where('slug', $slug)->first();
                        if (!$modelInstance) { return response()->json(['status' => 'error', 'message' => 'Record not found for update'], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                    } else { 
                        $modelInstance = new $modelClass();
                    }

                    $requestData = $request->except(['model', 'action', 'id', 'slug', 'scope', 'task', 'fields', 'with', '_token', 'XDEBUG_SESSION_START']);

                    $rules = [];
                    $messages = [];
                    $attributes = [];

                    if (method_exists($modelInstance, 'getValidationRules')) {
                        $rules = $modelInstance->getValidationRules($isUpdate ? $modelInstance->getKey() : null, $requestData);
                    } elseif (property_exists($modelInstance, 'rules')) {
                        $rulesProperty = $modelInstance->rules; // Evita llamar a ->rules() si no es un método
                         if (is_callable([$modelInstance, 'rules'])) { 
                            $rules = $modelInstance->rules($isUpdate ? $modelInstance->getKey() : null, $requestData);
                        } else if (is_array($rulesProperty)) {
                            $rules = $rulesProperty;
                        }
                        if ($isUpdate && $modelInstance->getKey() && is_array($rules)) { 
                            foreach($rules as $field => &$ruleSet) {
                                if (is_string($ruleSet)) $ruleSet = explode('|', $ruleSet);
                                foreach($ruleSet as $key => $r) { 
                                    if (is_string($r) && Str::startsWith(strtolower($r), 'unique:')) {
                                        $parts = explode(',', $r); 
                                        if (count($parts) < ((isset($parts[2]) && $parts[2] !== 'NULL' && $parts[2] !== '') ? 4 : 3) ) { // Simple unique:table,column
                                             $ruleSet[$key] = $parts[0] . // unique:table
                                                            (isset($parts[1]) ? ',' . $parts[1] : '') . // ,column
                                                            ',' . $modelInstance->getKey() . // ,except_id
                                                            ',' . $modelInstance->getKeyName(); // ,idColumn
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (method_exists($modelInstance, 'getValidationMessages')) {
                        $messages = $modelInstance->getValidationMessages();
                    } elseif (property_exists($modelInstance, 'customMessages') && is_array($modelInstance->customMessages) ) {
                        $messages = $modelInstance->customMessages;
                    }
                     if (method_exists($modelInstance, 'getValidationAttributes')) {
                        $attributes = $modelInstance->getValidationAttributes();
                    } elseif (property_exists($modelInstance, 'validationAttributes') && is_array($modelInstance->validationAttributes) ) {
                        $attributes = $modelInstance->validationAttributes;
                    }


                    if (!empty($rules)) {
                        $validator = Validator::make($requestData, $rules, $messages, $attributes);
                        if ($validator->fails()) { return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                        $requestData = $validator->validated(); 
                    }

                    $modelInstance->fill($requestData);
                    $modelInstance->save();

                    if (!empty($parsedWithData['relations'])) {
                        $modelInstance->load(array_keys($parsedWithData['relations']));
                    }
                    $httpStatusCode = $isUpdate ? 200 : 201;
                    $messageAction = $isUpdate ? 'updated' : 'created';
                    return response()->json(['status' => 'success', 'message' => "Record {$messageAction} successfully", 'data' => $this->processRecord($modelInstance, $fields, $parsedWithData)], $httpStatusCode, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                case 'delete':
                    if (!$modelClass) { return response()->json(['status' => 'error', 'message' => 'Model not specified for delete action.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }
                    if (!$id && !$slug) { return response()->json(['status' => 'error', 'message' => 'Missing id or slug parameter for delete action'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }

                    $modelInstance = $id ? $modelClass::find($id) : $modelClass::where('slug', $slug)->first();
                    if (!$modelInstance) { return response()->json(['status' => 'error', 'message' => 'Record not found for delete'], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); }

                    $modelInstance->delete();
                    return response()->json(['status' => 'success', 'message' => 'Record deleted successfully'], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                case 'send_email':
                case 'email':
                    $to = $request->input('to');
                    $subject = $request->input('subject');
                    $template_code = $request->input('template_code');
                    $template_data_json = $request->input('template_data');
                    $body_text = $request->input('body');

                    if (!$to || !$subject) {
                        return response()->json(['status' => 'error', 'message' => 'Missing "to" or "subject" parameter for email action.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    try {
                        $template_data = null;
                        if ($template_code && $template_data_json) {
                            $template_data = json_decode($template_data_json, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                Log::error("[ApiDispatcher] Invalid JSON in template_data for email.", ['json_error' => json_last_error_msg(), 'data_received' => $template_data_json]);
                                return response()->json(['status' => 'error', 'message' => 'Invalid JSON in "template_data". Error: ' . json_last_error_msg()], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                            }
                            Log::info("[ApiDispatcher] Attempting to send email with template.", ['template_code' => $template_code, 'to' => $to, 'subject' => $subject, 'data_keys' => is_array($template_data) ? array_keys($template_data) : 'not_an_array']);
                            Mail::send($template_code, $template_data, function ($message) use ($to, $subject, $request) {
                                $message->to($to)->subject($subject);
                                if ($request->input('from')) { $message->from($request->input('from')); }
                                if ($request->input('cc')) { $message->cc(array_map('trim', explode(',', $request->input('cc')))); }
                                if ($request->input('bcc')) { $message->bcc(array_map('trim', explode(',', $request->input('bcc')))); }
                            });
                            Log::info("[ApiDispatcher] Email with template to '{$to}' (subject '{$subject}') should have been sent by Mail facade.");
                        } elseif ($body_text) {
                            Log::info("[ApiDispatcher] Attempting to send plain text email.", ['to' => $to, 'subject' => $subject]);
                            Mail::raw($body_text, function ($message) use ($to, $subject, $request) {
                                $message->to($to)->subject($subject);
                                if ($request->input('from')) { $message->from($request->input('from')); }
                                if ($request->input('cc')) { $message->cc(array_map('trim', explode(',', $request->input('cc')))); }
                                if ($request->input('bcc')) { $message->bcc(array_map('trim', explode(',', $request->input('bcc')))); }
                            });
                            Log::info("[ApiDispatcher] Plain text email to '{$to}' (subject '{$subject}') should have been sent by Mail facade.");
                        } else {
                            return response()->json(['status' => 'error', 'message' => 'Missing "body" (for plain text) or "template_code" & "template_data" (for template email) parameters.'], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }
                        if (Mail::failures()) {
                            Log::error("[ApiDispatcher] Mail::failures() reported issues after attempting to send email.", ['to' => $to, 'failures' => Mail::failures()]);
                            return response()->json(['status' => 'error', 'message' => 'Email sending failed. Check system logs. Failures: ' . implode(', ', Mail::failures())], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }
                        return response()->json(['status' => 'success', 'message' => 'Email processed for sending successfully.'], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } catch (\Exception $mailException) {
                        Log::error("[ApiDispatcher] Exception during email sending.", [
                            'to' => $to, 'subject' => $subject, 'template_code' => $template_code,
                            'error_message' => $mailException->getMessage(),
                            'trace_summary' => $mailException->getFile() . ':' . $mailException->getLine()
                        ]);
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Failed to send email: ' . $mailException->getMessage()
                        ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                default:
                    $errorMsg = $modelClass ? "Action '{$action}' not supported for model '{$finalClassNameForMsg}'." : "Action '{$action}' not supported or requires a model parameter.";
                    Log::warning("[ApiDispatcher] Unsupported action.", ['action' => $action, 'model_param' => $modelParam]);
                    return response()->json(['status' => 'error', 'message' => $errorMsg], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            $context = ['action' => $action, 'error_type' => get_class($e)];
            if ($modelClass) $context['model'] = $finalClassNameForMsg;
            Log::error("[ApiDispatcher] General API Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine(), $context);
            return response()->json([
                'status' => 'error',
                'message' => 'API Exception: ' . $e->getMessage(),
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    // ... (parseWithParameter, processRecord, extractRelationData, extractFieldsFromItem sin cambios respecto a la versión anterior que corregía las relaciones)
    // Asegúrate de que estas funciones estén aquí y sean las de la respuesta anterior.
    // Por completitud, las incluyo de nuevo sin cambios respecto a la última versión funcional para relaciones.
    protected function parseWithParameter(string $withParam = null): array
    {
        $parsed = ['relations' => [], 'relations_schema' => []];
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
        Log::debug("[ApiDispatcher - parseWithParameter] Parsed 'with' data: ", $parsed);
        return $parsed;
    }

    protected function processRecord(Model $record, ?string $fieldsCsv, array $parsedWithData = []): array
    {
        $data = [];
        $processValue = function ($value) use (&$processValue, $parsedWithData) { 
            if ($value instanceof \System\Models\File) { 
                return $value->getPath();
            } elseif ($value instanceof EloquentCollection && $value->count() > 0 && $value->first() instanceof \System\Models\File) {
                return $value->map(function ($fileItem) {
                    if ($fileItem instanceof \System\Models\File) return $fileItem->getPath();
                    return $fileItem;
                });
            }
            return $value;
        };

        $requestedRelationsFromWith = $parsedWithData['relations'] ?? [];
        $modelClassDebug = get_class($record);
        $recordIdDebug = $record->getKey();

        Log::debug("[ApiDispatcher processRecord] START for model: {$modelClassDebug} ID: {$recordIdDebug}. FieldsCSV: '{$fieldsCsv}'. WithRelations: " . implode(',', array_keys($requestedRelationsFromWith)));

        if (empty($fieldsCsv)) {
            foreach ($record->getAttributes() as $key => $value) {
                $data[$key] = $processValue($record->getAttribute($key));
            }
            Log::debug("[ApiDispatcher processRecord] No FieldsCSV. Added all attributes for {$modelClassDebug} ID: {$recordIdDebug}.", array_keys($data));

            if (method_exists($record, 'getAppends')) {
                foreach ($record->getAppends() as $appendedField) {
                    if (!array_key_exists($appendedField, $data)) {
                        $data[$appendedField] = $processValue($record->{$appendedField});
                    }
                }
                Log::debug("[ApiDispatcher processRecord] No FieldsCSV. Added appends for {$modelClassDebug} ID: {$recordIdDebug}.", $record->getAppends());
            }
        } else {
            $fieldList = array_map('trim', explode(',', $fieldsCsv));
            Log::debug("[ApiDispatcher processRecord] FieldsCSV provided for {$modelClassDebug} ID: {$recordIdDebug}. List: " . implode(', ', $fieldList));
            foreach ($fieldList as $fieldKey) {
                if (!isset($requestedRelationsFromWith[$fieldKey])) {
                    $value = null;
                    $found = false;
                    if (array_key_exists($fieldKey, $record->getAttributes())) {
                        $value = $record->getAttribute($fieldKey);
                        $found = true;
                    } elseif (method_exists($record, $fieldKey) && (new \ReflectionMethod($record, $fieldKey))->getNumberOfParameters() === 0) {
                         if (in_array($fieldKey, $record->getAppends()) || Str::endsWith((new \ReflectionMethod($record, $fieldKey))->getName(), 'Attribute')) {
                            $value = $record->{$fieldKey};
                            $found = true;
                        } else {
                            Log::debug("[ApiDispatcher processRecord] Field '{$fieldKey}' on {$modelClassDebug} ID: {$recordIdDebug} seems like a method but not an explicit attribute/append or pre-defined relation. Will be handled by relation loop if loaded.");
                        }
                    } elseif (in_array($fieldKey, $record->getAppends())) { 
                        $value = $record->{$fieldKey};
                        $found = true;
                    }

                    if ($found) {
                        $data[$fieldKey] = $processValue($value);
                        Log::debug("[ApiDispatcher processRecord] Added field '{$fieldKey}' from FieldsCSV for {$modelClassDebug} ID: {$recordIdDebug}.");
                    } else {
                        Log::debug("[ApiDispatcher processRecord] Field '{$fieldKey}' from FieldsCSV NOT FOUND as attribute/accessor for {$modelClassDebug} ID: {$recordIdDebug}. It might be a relation name to be processed later or an unknown field.");
                    }
                } else {
                    Log::debug("[ApiDispatcher processRecord] Field '{$fieldKey}' from FieldsCSV for {$modelClassDebug} ID: {$recordIdDebug} IS a relation defined in 'with'. Skipping attribute processing for it.");
                }
            }
        }

        Log::debug("[ApiDispatcher processRecord] Processing relations from 'with' for {$modelClassDebug} ID: {$recordIdDebug}. Relations to check: " . implode(', ', array_keys($requestedRelationsFromWith)));
        foreach ($requestedRelationsFromWith as $relationName => $relationSpecificFields) {
            if ($record->relationLoaded($relationName)) {
                Log::debug("[ApiDispatcher processRecord] Relation '{$relationName}' IS LOADED for {$modelClassDebug} ID: {$recordIdDebug}. Fields to select: " . implode(',', $relationSpecificFields));
                $relationData = $record->getRelation($relationName);
                $data[$relationName] = $this->extractRelationData($relationData, $relationSpecificFields, $processValue, $parsedWithData);
            } else {
                Log::debug("[ApiDispatcher processRecord] Relation '{$relationName}' IS NOT LOADED for {$modelClassDebug} ID: {$recordIdDebug}. Skipping.");
            }
        }
        Log::debug("[ApiDispatcher processRecord] END for model: {$modelClassDebug} ID: {$recordIdDebug}. Final data keys: " . implode(', ', array_keys($data)));
        return $data;
    }


    protected function extractRelationData($modelOrCollection, array $fieldsToSelect, callable $processValueCallback, array $parsedWithData)
    {
        if (is_null($modelOrCollection)) {
            return null;
        }

        $isAllFields = ($fieldsToSelect === ['*'] || empty($fieldsToSelect));
        $relationClassDebug = is_object($modelOrCollection) ? get_class($modelOrCollection) : gettype($modelOrCollection);
        Log::debug("[ApiDispatcher extractRelationData] Processing relation of type: {$relationClassDebug}. AllFields: " . ($isAllFields ? 'yes' : 'no') . ". Fields: " . implode(',', $fieldsToSelect));

        if ($modelOrCollection instanceof EloquentCollection) {
            return $modelOrCollection->map(function (Model $item) use ($isAllFields, $fieldsToSelect, $processValueCallback, $parsedWithData) {
                return $this->extractFieldsFromItem($item, $isAllFields, $fieldsToSelect, $processValueCallback, $parsedWithData);
            })->toArray(); 
        } elseif ($modelOrCollection instanceof Model) {
            return $this->extractFieldsFromItem($modelOrCollection, $isAllFields, $fieldsToSelect, $processValueCallback, $parsedWithData);
        }
        
        Log::warning("[ApiDispatcher extractRelationData] Unexpected data type for relation: {$relationClassDebug}. Passing through processValue.");
        return $processValueCallback($modelOrCollection);
    }

    private function extractFieldsFromItem(Model $item, bool $isAllFields, array $fieldsToSelect, callable $processValueCallback, array $parsedWithData): array
    {
        $itemData = [];
        $itemClassDebug = get_class($item);
        $itemIdDebug = $item->getKey();
        Log::debug("[ApiDispatcher extractFieldsFromItem] Item: {$itemClassDebug} ID: {$itemIdDebug}. AllFields: " . ($isAllFields ? 'yes' : 'no') . ". Fields: " . implode(',', $fieldsToSelect));

        if ($isAllFields) {
            foreach ($item->getAttributes() as $key => $value) {
                $itemData[$key] = $processValueCallback($item->getAttribute($key));
            }
            if (method_exists($item, 'getAppends')) {
                foreach ($item->getAppends() as $appendedField) {
                    if (!array_key_exists($appendedField, $itemData)) {
                        $itemData[$appendedField] = $processValueCallback($item->{$appendedField});
                    }
                }
            }
            foreach($item->getRelations() as $subRelationName => $subRelationData) {
                if ($item->relationLoaded($subRelationName)) {
                    Log::debug("[ApiDispatcher extractFieldsFromItem] Adding loaded sub-relation '{$subRelationName}' for item {$itemClassDebug} ID: {$itemIdDebug}");
                    $itemData[$subRelationName] = $processValueCallback($subRelationData);
                }
            }
        } else {
            foreach ($fieldsToSelect as $fieldKey) {
                $value = null;
                $found = false;
                if (array_key_exists($fieldKey, $item->getAttributes())) {
                    $value = $item->getAttribute($fieldKey);
                    $found = true;
                } elseif (method_exists($item, $fieldKey) && (new \ReflectionMethod($item, $fieldKey))->getNumberOfParameters() === 0) {
                     if (in_array($fieldKey, $item->getAppends()) || Str::endsWith((new \ReflectionMethod($item, $fieldKey))->getName(), 'Attribute')) {
                        $value = $item->{$fieldKey}; 
                        $found = true;
                    }
                } elseif (in_array($fieldKey, $item->getAppends())) {
                     $value = $item->{$fieldKey};
                     $found = true;
                }

                if ($found) {
                    $itemData[$fieldKey] = $processValueCallback($value);
                } else {
                    Log::debug("[ApiDispatcher extractFieldsFromItem] Field '{$fieldKey}' NOT FOUND on item {$itemClassDebug} ID: {$itemIdDebug}. Setting to null.");
                    $itemData[$fieldKey] = null; 
                }
            }
        }
        Log::debug("[ApiDispatcher extractFieldsFromItem] Data for item {$itemClassDebug} ID: {$itemIdDebug}. Keys: " . implode(', ', array_keys($itemData)));
        return $itemData;
    }

}