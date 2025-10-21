<?php namespace Aero\Manager\Models;

use October\Rain\Database\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Model
 */
class Services extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Sortable;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false; // Se mantiene como estaba

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_manager_services';

    /**
     * @var array Validation rules
     */
    public $rules = [
        // Tus reglas de validación aquí
    ];

    public function beforeCreate()
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'undefined_http_host_fallback';
        Log::info("[Services Model - beforeCreate] Hook triggered. HTTP_HOST original: " . $httpHost);

        // Normalizar: quitar 'www.' para almacenar solo el dominio base.
        $normalizedDomain = str_replace('www.', '', $httpHost);
        $this->domain = $normalizedDomain;

        Log::info("[Services Model - beforeCreate] Dominio a guardar en 'domain' column: " . $this->domain);
    }

    public $attachOne = [
        'img' => 'System\Models\File',
        'img_og' => 'System\Models\File',
        'banner' => 'System\Models\File',
        'img_mobile' => 'System\Models\File',
    ];

    public $belongsToMany =[
        'features' => [
            'Aero\Manager\Models\Features',
            'table'      => 'aero_manager_services_features',
            // 'name'    => 'name' // Considera si 'name' es el key correcto o si debería ser 'order', 'pivot[sort_order]', etc.
        ],
        'plans' => [
            'Aero\Manager\Models\Plans',
            'table'      => 'aero_manager_services_plans',
            // 'name'    => 'name', // Similar al anterior
            'scope'      => 'public', // Este scope 'public' es para la relación, no para el modelo Services directamente
        ],
        'faqs' => [
            'Aero\Manager\Models\Faqs',
            'table'      => 'aero_manager_services_faqs',
            // 'question' => 'question'
        ],
        'docs' => [
            'Aero\Manager\Models\Docs',
            'table'      => 'aero_manager_services_docs',
            // 'question' => 'question'
        ],
        'categories' => [
            'Aero\Manager\Models\ServicesCategories',
            'table'      => 'aero_manager_services_services_categories',
            // 'question' => 'question' // Probablemente aquí debería ser 'name' o similar, no 'question'
        ],
    ];

    public $attachMany = [
        'gallery' => 'System\Models\File',
        'avatar' => 'System\Models\File', // 'avatar' usualmente es attachOne, pero lo dejo como estaba
    ];

    protected $jsonable = ['addons','comparison','software','reviews','appareance_services','gallery_complex','features_sections','ai_train','parameters','buttons'];

    /**
     * Scope para obtener solo registros públicos y ordenados.
     */
    public function scopePublic($query)
    {
        Log::info("[Services Model - scopePublic] Aplicando scope Public.");
        return $query->where('aero_manager_services.public', 1)->orderBy('order', 'asc');
    }

    /**
     * Scope para filtrar registros por el dominio actual (normalizado, sin 'www.').
     */
    public function scopeDomain($query)
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'undefined_http_host_fallback';
        Log::info("[Services Model - scopeDomain] Hook triggered. HTTP_HOST original para scope: " . $httpHost);

        // Normalizar el host de la petición actual para la consulta (quitar 'www.')
        $normalizedHostForQuery = str_replace('www.', '', $httpHost);
        Log::info("[Services Model - scopeDomain] Comparando columna 'domain' con: " . $normalizedHostForQuery);

        return $query->where('aero_manager_services.domain', $normalizedHostForQuery)->where('aero_manager_services.public', 1);
    }
    
public function scopeCloud($query)
{
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'undefined_http_host_fallback';
    Log::info("[Services Model - scopeDomain] Hook triggered. HTTP_HOST original para scope: " . $httpHost);

    // Normalizar el host de la petición actual para la consulta (quitar 'www.')
    $normalizedHostForQuery = str_replace('www.', '', $httpHost);
    Log::info("[Services Model - scopeDomain] Comparando columna 'domain' con: " . $normalizedHostForQuery);

    return $query
        ->where('aero_manager_services.domain', $normalizedHostForQuery)
        ->where('aero_manager_services.public', 1)
        ->whereHas('categories', function ($q) {
            $q->where('services_categories_id', 1);
        });
}
 

        public function scopeSlug($query)
    {
        $httpHost = $_SERVER['HTTP_HOST'] ?? 'undefined_http_host_fallback';
        $normalizedHostForQuery = str_replace('www.', '', $httpHost);

        return $query->where('aero_manager_services.domain', $normalizedHostForQuery)->where('aero_manager_services.slug', request()->get('slug'))->where('aero_manager_services.public', 1);
    }
}