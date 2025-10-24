<?php namespace Aero\Clouds\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Response;

/**
 * CloudsAPI Component
 *
 * Proporciona endpoints RESTful para integraciones avanzadas y SPAs/microfrontends.
 * Usa el trait FormHandler para operaciones CRUD.
 *
 * Configuración en la página (ej: /api/clouds):
 * ```
 * title = "Clouds API"
 * url = "/api/:model/:action?/:id?"
 *
 * [cloudsAPI]
 * allowedModels[] = "Order"
 * allowedModels[] = "Invoice"
 * allowedModels[] = "Cloud"
 * allowedModels[] = "Ticket"
 * requireAuth = 1
 * ==
 * ```
 *
 * Endpoints disponibles:
 * - GET    /api/orders          - Listar órdenes
 * - GET    /api/orders/123      - Obtener orden específica
 * - POST   /api/orders          - Crear nueva orden
 * - PUT    /api/orders/123      - Actualizar orden
 * - DELETE /api/orders/123      - Eliminar orden
 *
 * Respuestas JSON consistentes:
 * ```json
 * {
 *   "success": true,
 *   "data": {...},
 *   "message": "Operación exitosa"
 * }
 * ```
 */
class CloudsAPI extends ComponentBase
{
    use \Aero\Clouds\Traits\FormHandler;

    /**
     * Modelos permitidos para la API
     * @var array
     */
    protected $allowedModels = [];

    /**
     * Namespace de modelos
     * @var string
     */
    protected $modelNamespace = 'Aero\Clouds\Models';

    public function componentDetails()
    {
        return [
            'name' => 'Clouds REST API',
            'description' => 'Proporciona endpoints RESTful para integraciones avanzadas'
        ];
    }

    public function defineProperties()
    {
        return [
            'allowedModels' => [
                'title' => 'Modelos Permitidos',
                'description' => 'Lista de modelos que pueden ser accedidos via API',
                'type' => 'stringList',
                'default' => ['Order', 'Invoice', 'Cloud', 'Ticket']
            ],
            'requireAuth' => [
                'title' => 'Requiere Autenticación',
                'description' => 'Si se requiere autenticación para todos los endpoints',
                'type' => 'checkbox',
                'default' => true
            ],
            'corsEnabled' => [
                'title' => 'Habilitar CORS',
                'description' => 'Permitir peticiones cross-origin',
                'type' => 'checkbox',
                'default' => false
            ],
            'rateLimit' => [
                'title' => 'Límite de Peticiones',
                'description' => 'Número máximo de peticiones por minuto (0 = sin límite)',
                'type' => 'string',
                'default' => '60'
            ]
        ];
    }

    /**
     * Inicializar componente
     */
    public function onRun()
    {
        // Configurar modelos permitidos
        $this->allowedModels = $this->property('allowedModels', []);

        // Aplicar CORS si está habilitado
        if ($this->property('corsEnabled')) {
            $this->applyCors();
        }

        // Obtener parámetros de la URL
        $model = $this->param('model');
        $action = $this->param('action');
        $id = $this->param('id');

        // Si action es numérico, entonces es el ID y no hay action específica
        if ($action && is_numeric($action)) {
            $id = $action;
            $action = null;
        }

        // Determinar el método HTTP
        $method = \Request::method();

        // Rutear la petición
        try {
            $response = $this->routeRequest($model, $method, $id, $action);
            return $this->jsonResponse($response);
        } catch (\Exception $e) {
            return $this->jsonError($e->getMessage(), 400);
        }
    }

    /**
     * Rutear la petición según el método HTTP
     *
     * @param string $model Nombre del modelo
     * @param string $method Método HTTP
     * @param int|null $id ID del registro
     * @param string|null $action Acción personalizada
     * @return array
     */
    protected function routeRequest($model, $method, $id = null, $action = null)
    {
        // Validar modelo permitido
        if (!in_array($model, $this->allowedModels)) {
            throw new \ApplicationException("Modelo no permitido: {$model}");
        }

        // Construir clase del modelo
        $modelClass = $this->modelNamespace . '\\' . ucfirst($model);

        if (!class_exists($modelClass)) {
            throw new \ApplicationException("Modelo no encontrado: {$modelClass}");
        }

        // Verificar autenticación
        if ($this->property('requireAuth') && !Auth::check()) {
            throw new \ApplicationException('Autenticación requerida', 401);
        }

        // Verificar rate limit
        if (!$this->checkRateLimit()) {
            throw new \ApplicationException('Límite de peticiones excedido', 429);
        }

        // Opciones comunes
        $options = [
            'requireAuth' => $this->property('requireAuth'),
            'checkOwnership' => true, // Por defecto verificar propiedad
            'logErrors' => true
        ];

        // Rutear según método HTTP
        switch ($method) {
            case 'GET':
                if ($id) {
                    // GET /api/orders/123 - Obtener un registro
                    $record = $this->findRecord($modelClass, $id, $options);
                    return [
                        'success' => true,
                        'data' => $record->toArray()
                    ];
                } else {
                    // GET /api/orders - Listar registros
                    return $this->listRecords($modelClass, $options);
                }

            case 'POST':
                // POST /api/orders - Crear registro
                $data = \Request::input();
                $record = $modelClass::create($data);

                return [
                    'success' => true,
                    'data' => $record->toArray(),
                    'message' => 'Registro creado exitosamente'
                ];

            case 'PUT':
            case 'PATCH':
                // PUT /api/orders/123 - Actualizar registro
                if (!$id) {
                    throw new \ApplicationException('ID requerido para actualizar');
                }

                $record = $this->findRecord($modelClass, $id, $options);
                $data = \Request::input();
                $record->update($data);

                return [
                    'success' => true,
                    'data' => $record->toArray(),
                    'message' => 'Registro actualizado exitosamente'
                ];

            case 'DELETE':
                // DELETE /api/orders/123 - Eliminar registro
                if (!$id) {
                    throw new \ApplicationException('ID requerido para eliminar');
                }

                $record = $this->findRecord($modelClass, $id, $options);
                $record->delete();

                return [
                    'success' => true,
                    'message' => 'Registro eliminado exitosamente'
                ];

            default:
                throw new \ApplicationException("Método no permitido: {$method}");
        }
    }

    /**
     * Listar registros con filtros y paginación
     *
     * @param string $modelClass Clase del modelo
     * @param array $options Opciones
     * @return array
     */
    protected function listRecords($modelClass, array $options = [])
    {
        $query = $modelClass::query();

        // Filtrar por usuario si se requiere ownership
        if ($options['checkOwnership']) {
            $user = Auth::getUser();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        // Aplicar filtros de query string
        $filters = \Request::input('filters', []);
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        // Ordenamiento
        $sortBy = \Request::input('sort_by', 'created_at');
        $sortOrder = \Request::input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $page = \Request::input('page', 1);
        $perPage = \Request::input('per_page', 15);
        $records = $query->paginate($perPage, $page);

        return [
            'success' => true,
            'data' => $records->toArray(),
            'pagination' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem()
            ]
        ];
    }

    /**
     * Respuesta JSON de éxito
     *
     * @param array $data Datos de respuesta
     * @param int $statusCode Código HTTP
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        return Response::json($data, $statusCode);
    }

    /**
     * Respuesta JSON de error
     *
     * @param string $message Mensaje de error
     * @param int $statusCode Código HTTP
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonError($message, $statusCode = 400)
    {
        return Response::json([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    /**
     * Aplicar headers CORS
     */
    protected function applyCors()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // Manejar preflight request
        if (\Request::method() === 'OPTIONS') {
            exit(0);
        }
    }

    /**
     * Verificar rate limit
     *
     * @return bool
     */
    protected function checkRateLimit()
    {
        $limit = (int) $this->property('rateLimit', 60);

        if ($limit === 0) {
            return true; // Sin límite
        }

        // Implementar rate limiting simple con cache
        $key = 'api_rate_limit:' . \Request::ip();
        $requests = \Cache::get($key, 0);

        if ($requests >= $limit) {
            return false;
        }

        \Cache::put($key, $requests + 1, 60); // 60 segundos

        return true;
    }
}
