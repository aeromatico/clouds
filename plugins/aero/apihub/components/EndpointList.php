<?php namespace Aero\ApiHub\Components;

use Cms\Classes\ComponentBase;
use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Models\Endpoint;
use Cache;

/**
 * EndpointList Component
 * Displays endpoints for an API
 */
class EndpointList extends ComponentBase
{
    /**
     * @var Api The API
     */
    public $api;

    /**
     * @var Collection Endpoints collection
     */
    public $endpoints;

    /**
     * @var array Method filter
     */
    public $methods;

    /**
     * Component details
     */
    public function componentDetails()
    {
        return [
            'name' => 'Endpoint List',
            'description' => 'Displays endpoints for an API with filtering',
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties()
    {
        return [
            'apiSlug' => [
                'title' => 'API Slug',
                'type' => 'string',
                'default' => '{{ :slug }}',
            ],
            'groupByMethod' => [
                'title' => 'Group by method',
                'type' => 'checkbox',
                'default' => true,
            ],
            'showParameters' => [
                'title' => 'Show parameters',
                'type' => 'checkbox',
                'default' => true,
            ],
        ];
    }

    /**
     * Run on component initialization
     */
    public function onRun()
    {
        $this->loadApi();
        $this->loadEndpoints();
        $this->loadMethods();
    }

    /**
     * Load API
     */
    protected function loadApi()
    {
        $slug = $this->property('apiSlug');
        $this->api = Api::getCached($slug);

        if (!$this->api) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
    }

    /**
     * Load endpoints
     */
    protected function loadEndpoints()
    {
        if (!$this->api) {
            return;
        }

        $method = get('method');
        $search = get('search');

        $cacheKey = "apihub:endpoints_list:{$this->api->id}:{$method}:{$search}";

        $this->endpoints = Cache::remember($cacheKey, 600, function () use ($method, $search) {
            $query = $this->api->endpoints();

            if ($method) {
                $query->method($method);
            }

            if ($search) {
                $query->search($search);
            }

            if ($this->property('groupByMethod')) {
                return $query->orderBy('method')->orderBy('name')->get()->groupBy('method');
            }

            return $query->orderBy('name')->get();
        });
    }

    /**
     * Load available methods
     */
    protected function loadMethods()
    {
        if (!$this->api) {
            return;
        }

        $this->methods = $this->api->endpoints()
            ->distinct()
            ->orderBy('method')
            ->pluck('method')
            ->toArray();
    }

    /**
     * AJAX handler: Filter
     */
    public function onFilter()
    {
        $this->loadEndpoints();
        return [
            '#endpointList' => $this->renderPartial('@list'),
        ];
    }
}
