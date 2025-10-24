<?php namespace Aero\Clouds\Components;

use Cms\Classes\ComponentBase;
use Auth;

/**
 * CloudsForm Component
 *
 * Componente genérico y configurable para manejar formularios de cualquier modelo.
 * Usa el trait FormHandler para operaciones CRUD.
 *
 * Configuración en la página:
 * ```
 * [cloudsForm myForm]
 * model = "Order"
 * modelNamespace = "Aero\Clouds\Models"
 * redirectTo = "/dashboard/orders"
 * requireAuth = 1
 * checkOwnership = 1
 * successMessage = "Orden creada exitosamente"
 * ==
 * ```
 *
 * Uso en el template:
 * ```html
 * <form data-request="myForm::onCreate">
 *     <input name="field1" />
 *     <button type="submit">Crear</button>
 * </form>
 * ```
 */
class CloudsForm extends ComponentBase
{
    use \Aero\Clouds\Traits\FormHandler;

    /**
     * Modelo a manejar
     * @var string
     */
    public $modelClass;

    /**
     * Opciones del componente
     * @var array
     */
    public $options = [];

    public function componentDetails()
    {
        return [
            'name' => 'Clouds Form Handler',
            'description' => 'Componente genérico para manejar formularios CRUD de cualquier modelo'
        ];
    }

    public function defineProperties()
    {
        return [
            'model' => [
                'title' => 'Modelo',
                'description' => 'Nombre del modelo (ej: Order, Invoice, Cloud)',
                'type' => 'string',
                'required' => true
            ],
            'modelNamespace' => [
                'title' => 'Namespace del Modelo',
                'description' => 'Namespace completo del modelo',
                'type' => 'string',
                'default' => 'Aero\Clouds\Models'
            ],
            'redirectTo' => [
                'title' => 'Redirigir a',
                'description' => 'URL de redirección después de guardar (usa {id} para el ID del registro)',
                'type' => 'string',
                'default' => ''
            ],
            'requireAuth' => [
                'title' => 'Requiere Autenticación',
                'description' => 'Si se requiere que el usuario esté autenticado',
                'type' => 'checkbox',
                'default' => true
            ],
            'checkOwnership' => [
                'title' => 'Verificar Propietario',
                'description' => 'Verificar que el registro pertenece al usuario (campo user_id)',
                'type' => 'checkbox',
                'default' => false
            ],
            'successMessage' => [
                'title' => 'Mensaje de Éxito',
                'description' => 'Mensaje personalizado de éxito',
                'type' => 'string',
                'default' => ''
            ],
            'errorMessage' => [
                'title' => 'Mensaje de Error',
                'description' => 'Mensaje personalizado de error',
                'type' => 'string',
                'default' => ''
            ],
            'withRelations' => [
                'title' => 'Cargar Relaciones',
                'description' => 'Relaciones a cargar (separadas por coma)',
                'type' => 'string',
                'default' => ''
            ]
        ];
    }

    /**
     * Inicializar componente
     */
    public function onRun()
    {
        // Construir clase del modelo
        $namespace = $this->property('modelNamespace', 'Aero\Clouds\Models');
        $model = $this->property('model');
        $this->modelClass = $namespace . '\\' . $model;

        // Verificar que la clase existe
        if (!class_exists($this->modelClass)) {
            throw new \ApplicationException("Modelo no encontrado: {$this->modelClass}");
        }

        // Configurar opciones
        $this->options = [
            'requireAuth' => $this->property('requireAuth', true),
            'checkOwnership' => $this->property('checkOwnership', false),
            'successMessage' => $this->property('successMessage'),
            'errorMessage' => $this->property('errorMessage'),
            'logErrors' => true
        ];

        // Cargar relaciones si están especificadas
        $withRelations = $this->property('withRelations');
        if ($withRelations) {
            $this->options['with'] = array_map('trim', explode(',', $withRelations));
        }

        // Hacer disponible el usuario actual
        $this->page['user'] = Auth::getUser();
    }

    /**
     * Crear nuevo registro
     */
    public function onCreate()
    {
        $data = post();
        $redirectTo = $this->property('redirectTo');

        return $this->handleCreate(
            $this->modelClass,
            $data,
            $redirectTo,
            $this->options
        );
    }

    /**
     * Actualizar registro existente
     */
    public function onUpdate()
    {
        $id = post('id');
        $data = post();
        $redirectTo = $this->property('redirectTo');

        if (!$id) {
            throw new \ApplicationException('ID no proporcionado');
        }

        return $this->handleUpdate(
            $this->modelClass,
            $id,
            $data,
            $redirectTo,
            $this->options
        );
    }

    /**
     * Eliminar registro
     */
    public function onDelete()
    {
        $id = post('id');
        $redirectTo = $this->property('redirectTo');

        if (!$id) {
            throw new \ApplicationException('ID no proporcionado');
        }

        return $this->handleDelete(
            $this->modelClass,
            $id,
            $redirectTo,
            $this->options
        );
    }

    /**
     * Obtener un registro por ID
     */
    public function onLoad()
    {
        $id = post('id');

        if (!$id) {
            throw new \ApplicationException('ID no proporcionado');
        }

        $record = $this->findRecord($this->modelClass, $id, $this->options);

        return [
            'success' => true,
            'data' => $record->toArray()
        ];
    }

    /**
     * Listar registros con paginación
     */
    public function onList()
    {
        $page = post('page', 1);
        $perPage = post('per_page', 15);
        $filters = post('filters', []);

        $query = $this->modelClass::query();

        // Aplicar filtros si el usuario es propietario
        if ($this->options['checkOwnership']) {
            $user = Auth::getUser();
            if ($user) {
                $query->where('user_id', $user->id);
            }
        }

        // Aplicar filtros adicionales
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        // Cargar relaciones
        if (isset($this->options['with'])) {
            $query->with($this->options['with']);
        }

        // Paginar
        $records = $query->paginate($perPage, $page);

        return [
            'success' => true,
            'data' => $records->toArray(),
            'pagination' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage()
            ]
        ];
    }
}
