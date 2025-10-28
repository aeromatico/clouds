<?php namespace Aero\ApiHub\Components;

use Cms\Classes\ComponentBase;
use Aero\ApiHub\Models\Api;
use Cache;

/**
 * ApiCatalog Component
 * Displays catalog of APIs with search and filtering
 */
class ApiCatalog extends ComponentBase
{
    /**
     * @var Collection APIs collection
     */
    public $apis;

    /**
     * @var array Categories list
     */
    public $categories;

    /**
     * @var int Current page
     */
    public $currentPage;

    /**
     * @var int Total pages
     */
    public $totalPages;

    /**
     * Component details
     */
    public function componentDetails()
    {
        return [
            'name' => 'API Catalog',
            'description' => 'Displays searchable API catalog with filtering and pagination',
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties()
    {
        return [
            'perPage' => [
                'title' => 'APIs per page',
                'type' => 'string',
                'default' => '12',
                'validationPattern' => '^[0-9]+$',
            ],
            'category' => [
                'title' => 'Filter by category',
                'type' => 'string',
                'default' => '',
            ],
            'showSearch' => [
                'title' => 'Show search',
                'type' => 'checkbox',
                'default' => true,
            ],
            'showFilters' => [
                'title' => 'Show filters',
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
        $this->loadApis();
        $this->loadCategories();
    }

    /**
     * Load APIs with pagination and filters
     */
    protected function loadApis()
    {
        $page = (int) $this->param('page', 1);
        $perPage = (int) $this->property('perPage', 12);
        $category = $this->property('category') ?: get('category');
        $search = get('search');

        $cacheKey = "apihub:catalog:{$page}:{$perPage}:{$category}:{$search}";

        $result = Cache::remember($cacheKey, 600, function () use ($page, $perPage, $category, $search) {
            $query = Api::with('endpoints');

            // Apply filters
            if ($category) {
                $query->category($category);
            }

            if ($search) {
                $query->search($search);
            }

            // Order by most recent
            $query->orderBy('created_at', 'desc');

            // Paginate
            return $query->paginate($perPage, $page);
        });

        $this->apis = $result;
        $this->currentPage = $page;
        $this->totalPages = $result->lastPage();
    }

    /**
     * Load categories
     */
    protected function loadCategories()
    {
        $this->categories = Api::getAllCategories();
    }

    /**
     * AJAX handler: Search
     */
    public function onSearch()
    {
        $this->loadApis();
        return [
            '#apiCatalog' => $this->renderPartial('@results'),
        ];
    }

    /**
     * AJAX handler: Filter by category
     */
    public function onFilter()
    {
        $this->loadApis();
        return [
            '#apiCatalog' => $this->renderPartial('@results'),
        ];
    }
}
