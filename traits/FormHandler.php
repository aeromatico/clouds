<?php namespace Aero\Clouds\Traits;

use Flash;
use Redirect;
use ValidationException;
use Auth;
use ApplicationException;

/**
 * FormHandler Trait
 *
 * Proporciona funcionalidad reutilizable para manejar formularios en componentes.
 * Simplifica CRUD operations, validación, y respuestas consistentes.
 *
 * Uso:
 * ```php
 * class MyComponent extends ComponentBase
 * {
 *     use \Aero\Clouds\Traits\FormHandler;
 *
 *     public function onCreateRecord()
 *     {
 *         return $this->handleCreate(
 *             'Aero\Clouds\Models\Order',
 *             post(),
 *             '/dashboard/orders'
 *         );
 *     }
 * }
 * ```
 */
trait FormHandler
{
    /**
     * Crear un nuevo registro
     *
     * @param string $modelClass Clase del modelo (ej: 'Aero\Clouds\Models\Order')
     * @param array $data Datos del formulario
     * @param string|null $redirectTo URL de redirección después de crear
     * @param array $options Opciones adicionales
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function handleCreate($modelClass, array $data, $redirectTo = null, array $options = [])
    {
        try {
            // Verificar autenticación si se requiere
            if ($this->requiresAuth($options)) {
                $this->checkAuth();
            }

            // Obtener usuario autenticado si existe
            $user = Auth::getUser();

            // Pre-procesar datos
            $data = $this->preprocessData($data, $options);

            // Auto-agregar user_id si el modelo lo tiene y no está presente
            if ($user && !isset($data['user_id'])) {
                $model = new $modelClass;
                if (in_array('user_id', $model->getFillable())) {
                    $data['user_id'] = $user->id;
                }
            }

            // Validar datos usando las reglas del modelo
            $this->validateData($modelClass, $data);

            // Crear el registro
            $record = $modelClass::create($data);

            // Manejar relaciones si existen
            if (isset($options['relations'])) {
                $this->handleRelations($record, $options['relations']);
            }

            // Ejecutar callback si existe
            if (isset($options['afterCreate']) && is_callable($options['afterCreate'])) {
                $options['afterCreate']($record);
            }

            // Mensaje de éxito
            $successMessage = $options['successMessage'] ??
                'Registro creado exitosamente' .
                (isset($record->id) ? ' (ID: ' . $record->id . ')' : '');

            Flash::success($successMessage);

            // Responder según el tipo de request
            return $this->respondSuccess($record, $redirectTo, $options);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->respondError($e, $options);
        }
    }

    /**
     * Actualizar un registro existente
     *
     * @param string $modelClass Clase del modelo
     * @param int|string $id ID del registro
     * @param array $data Datos del formulario
     * @param string|null $redirectTo URL de redirección
     * @param array $options Opciones adicionales
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function handleUpdate($modelClass, $id, array $data, $redirectTo = null, array $options = [])
    {
        try {
            // Verificar autenticación si se requiere
            if ($this->requiresAuth($options)) {
                $this->checkAuth();
            }

            // Buscar el registro
            $record = $this->findRecord($modelClass, $id, $options);

            // Pre-procesar datos
            $data = $this->preprocessData($data, $options);

            // Validar datos
            $this->validateData($modelClass, $data, $record);

            // Actualizar el registro
            $record->update($data);

            // Manejar relaciones si existen
            if (isset($options['relations'])) {
                $this->handleRelations($record, $options['relations']);
            }

            // Ejecutar callback si existe
            if (isset($options['afterUpdate']) && is_callable($options['afterUpdate'])) {
                $options['afterUpdate']($record);
            }

            // Mensaje de éxito
            $successMessage = $options['successMessage'] ?? 'Registro actualizado exitosamente';
            Flash::success($successMessage);

            // Responder según el tipo de request
            return $this->respondSuccess($record, $redirectTo, $options);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->respondError($e, $options);
        }
    }

    /**
     * Eliminar un registro
     *
     * @param string $modelClass Clase del modelo
     * @param int|string $id ID del registro
     * @param string|null $redirectTo URL de redirección
     * @param array $options Opciones adicionales
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function handleDelete($modelClass, $id, $redirectTo = null, array $options = [])
    {
        try {
            // Verificar autenticación si se requiere
            if ($this->requiresAuth($options)) {
                $this->checkAuth();
            }

            // Buscar el registro
            $record = $this->findRecord($modelClass, $id, $options);

            // Ejecutar callback antes de eliminar si existe
            if (isset($options['beforeDelete']) && is_callable($options['beforeDelete'])) {
                $options['beforeDelete']($record);
            }

            // Eliminar el registro
            $record->delete();

            // Mensaje de éxito
            $successMessage = $options['successMessage'] ?? 'Registro eliminado exitosamente';
            Flash::success($successMessage);

            // Responder según el tipo de request
            return $this->respondSuccess(null, $redirectTo, $options);

        } catch (\Exception $e) {
            return $this->respondError($e, $options);
        }
    }

    /**
     * Buscar un registro por ID
     *
     * @param string $modelClass Clase del modelo
     * @param int|string $id ID del registro
     * @param array $options Opciones (incluye 'checkOwnership' para verificar propietario)
     * @return \Model
     * @throws ApplicationException
     */
    protected function findRecord($modelClass, $id, array $options = [])
    {
        $query = $modelClass::query();

        // Agregar relaciones si están especificadas
        if (isset($options['with'])) {
            $query->with($options['with']);
        }

        // Buscar el registro
        $record = $query->find($id);

        if (!$record) {
            throw new ApplicationException('Registro no encontrado');
        }

        // Verificar propiedad si se requiere
        if (isset($options['checkOwnership']) && $options['checkOwnership']) {
            $user = Auth::getUser();
            if (!$user || $record->user_id !== $user->id) {
                throw new ApplicationException('No tienes permiso para acceder a este registro');
            }
        }

        return $record;
    }

    /**
     * Validar datos usando las reglas del modelo
     *
     * @param string $modelClass Clase del modelo
     * @param array $data Datos a validar
     * @param \Model|null $record Registro existente (para updates)
     * @throws ValidationException
     */
    protected function validateData($modelClass, array $data, $record = null)
    {
        $model = $record ?: new $modelClass;

        // Obtener reglas de validación del modelo
        $rules = $model->rules ?? [];

        if (empty($rules)) {
            return; // No hay reglas de validación
        }

        // Si es update, adaptar reglas para unique
        if ($record) {
            foreach ($rules as $field => $rule) {
                if (is_string($rule) && strpos($rule, 'unique:') !== false) {
                    $rules[$field] = $rule . ',' . $record->id;
                }
            }
        }

        // Validar
        $validator = \Validator::make($data, $rules, $model->customMessages ?? []);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Pre-procesar datos antes de guardar
     *
     * @param array $data Datos del formulario
     * @param array $options Opciones con transformaciones
     * @return array
     */
    protected function preprocessData(array $data, array $options = [])
    {
        // Aplicar transformaciones si existen
        if (isset($options['transform']) && is_callable($options['transform'])) {
            $data = $options['transform']($data);
        }

        // Remover campos no deseados
        if (isset($options['except'])) {
            $data = array_diff_key($data, array_flip($options['except']));
        }

        // Mantener solo campos específicos
        if (isset($options['only'])) {
            $data = array_intersect_key($data, array_flip($options['only']));
        }

        return $data;
    }

    /**
     * Manejar relaciones (attach, sync, etc.)
     *
     * @param \Model $record Registro principal
     * @param array $relations Configuración de relaciones
     */
    protected function handleRelations($record, array $relations)
    {
        foreach ($relations as $relation => $config) {
            $method = $config['method'] ?? 'sync'; // sync, attach, detach
            $ids = $config['ids'] ?? [];

            if (method_exists($record, $relation)) {
                $relationObject = $record->$relation();

                switch ($method) {
                    case 'sync':
                        $relationObject->sync($ids);
                        break;
                    case 'attach':
                        $relationObject->attach($ids);
                        break;
                    case 'detach':
                        $relationObject->detach($ids);
                        break;
                }
            }
        }
    }

    /**
     * Verificar si se requiere autenticación
     *
     * @param array $options Opciones
     * @return bool
     */
    protected function requiresAuth(array $options = [])
    {
        return $options['requireAuth'] ?? true; // Por defecto requiere auth
    }

    /**
     * Verificar autenticación
     *
     * @throws ApplicationException
     */
    protected function checkAuth()
    {
        if (!Auth::check()) {
            throw new ApplicationException('Debes iniciar sesión para realizar esta acción');
        }
    }

    /**
     * Responder con éxito
     *
     * @param \Model|null $record Registro creado/actualizado
     * @param string|null $redirectTo URL de redirección
     * @param array $options Opciones adicionales
     * @return array|\Illuminate\Http\RedirectResponse
     */
    protected function respondSuccess($record, $redirectTo = null, array $options = [])
    {
        // Si es AJAX y se especifica un partial, renderizarlo
        if (\Request::ajax() && isset($options['updatePartial'])) {
            $response = [
                $options['updatePartial'] => $this->renderPartial($options['partial'] ?? '@default')
            ];

            if ($record) {
                $response['data'] = $record->toArray();
            }

            return $response;
        }

        // Si hay redirección
        if ($redirectTo) {
            // Reemplazar {id} con el ID del registro
            if ($record && strpos($redirectTo, '{id}') !== false) {
                $redirectTo = str_replace('{id}', $record->id, $redirectTo);
            }

            return Redirect::to($redirectTo);
        }

        // Respuesta AJAX simple
        if (\Request::ajax()) {
            return [
                'success' => true,
                'data' => $record ? $record->toArray() : null
            ];
        }

        return [];
    }

    /**
     * Responder con error
     *
     * @param \Exception $e Excepción
     * @param array $options Opciones
     * @return array
     * @throws \Exception
     */
    protected function respondError(\Exception $e, array $options = [])
    {
        $message = $options['errorMessage'] ?? 'Error: ' . $e->getMessage();

        Flash::error($message);

        // Log del error si se especifica
        if (isset($options['logErrors']) && $options['logErrors']) {
            \Log::error('FormHandler Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        if (\Request::ajax()) {
            return [
                'success' => false,
                'error' => $message
            ];
        }

        throw $e;
    }
}
