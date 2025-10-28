<?php namespace Aero\ApiHub\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Classes\ApiGuruClient;
use Aero\ApiHub\Classes\ApifyClient;
use Aero\ApiHub\Classes\ApiImporter;
use Flash;
use Queue;

/**
 * Scanner Backend Controller
 * Handles importing APIs from APIs.guru
 */
class Scanner extends Controller
{
    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['aero.apihub.access_scanner'];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Aero.ApiHub', 'apihub', 'scanner');
    }

    /**
     * Index action
     */
    public function index()
    {
        $this->pageTitle = 'Import APIs (Multi-Source)';
        $this->vars['stats'] = Api::getStats();
        $this->vars['sourceStats'] = ApiImporter::getSourceStats();

        // APIs.guru
        try {
            $guruClient = new ApiGuruClient();
            $this->vars['guruCategories'] = array_keys($guruClient->getCategories());
            $this->vars['guruConnected'] = $guruClient->testConnection();
        } catch (\Exception $e) {
            $this->vars['guruCategories'] = [];
            $this->vars['guruConnected'] = false;
        }

        // Apify
        try {
            $apifyClient = new ApifyClient();
            $this->vars['apifyConnected'] = $apifyClient->testConnection();
        } catch (\Exception $e) {
            $this->vars['apifyConnected'] = false;
            $this->vars['apifyError'] = $e->getMessage();
        }
    }

    /**
     * Search APIs on APIs.guru
     */
    public function onSearch()
    {
        $searchTerm = post('search_term');
        $limit = post('limit', 50); // Increased default limit

        if (empty($searchTerm)) {
            Flash::error('Please enter a search term');
            return ['#searchResults' => $this->makePartial('search_results', ['results' => []])];
        }

        try {
            $client = new ApiGuruClient();
            $results = $client->searchApis($searchTerm, $limit);

            \Log::info('APIs.guru search completed', [
                'search_term' => $searchTerm,
                'results_count' => count($results)
            ]);

            if (empty($results)) {
                Flash::warning('No APIs found for: ' . $searchTerm);
                return ['#searchResults' => $this->makePartial('search_results', ['results' => []])];
            }

            Flash::success('✓ Found ' . count($results) . ' API(s) for: ' . $searchTerm);

            return [
                '#searchResults' => $this->makePartial('search_results', ['results' => $results])
            ];

        } catch (\Exception $e) {
            \Log::error('APIs.guru search error: ' . $e->getMessage(), [
                'search_term' => $searchTerm,
                'trace' => $e->getTraceAsString()
            ]);
            Flash::error('Search failed: ' . $e->getMessage());
            return ['#searchResults' => $this->makePartial('search_results', ['results' => []])];
        }
    }

    /**
     * Import selected API from APIs.guru
     */
    public function onImport()
    {
        $provider = post('provider');
        $version = post('version');
        $title = post('title');
        $description = post('description', '');
        $category = post('category');

        \Log::info('onImport called', [
            'provider' => $provider,
            'version' => $version,
            'title' => $title,
            'category' => $category,
        ]);

        if (empty($provider) || empty($version)) {
            Flash::error('Provider and version are required');
            \Log::warning('Import failed: missing provider or version');
            return;
        }

        try {
            // Check if already exists
            $slug = str_slug($title);
            $existing = Api::where('slug', $slug)->first();

            if ($existing) {
                \Log::info('API already exists, skipping import', ['slug' => $slug, 'title' => $title]);
                Flash::warning('API already imported: ' . $existing->name);
                return;
            }

            // Queue the import job (always async for OpenAPI parsing)
            \Aero\ApiHub\Jobs\ImportApiGuruJob::dispatch(
                $provider,
                $version,
                $title,
                $category,
                $description
            );

            \Log::info('APIs.guru import queued', [
                'provider' => $provider,
                'version' => $version,
                'title' => $title,
            ]);

            Flash::success('✓ ' . $title . ' encolado. Procesando en segundo plano (~15 segundos). Actualiza la lista de APIs para ver el resultado.');

        } catch (\Exception $e) {
            \Log::error('Import dispatch failed', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);
            Flash::error('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk import popular APIs by category (APIs.guru)
     */
    public function onImportPopular()
    {
        $category = post('category');
        $limit = post('limit', 10);

        try {
            $client = new ApiGuruClient();
            $results = $client->getPopularApis($category, $limit);

            if (empty($results)) {
                Flash::warning('No popular APIs found for category: ' . $category);
                return;
            }

            $imported = 0;
            $skipped = 0;

            foreach ($results as $apiData) {
                // Skip if already exists
                $slug = str_slug($apiData['title']);
                if (Api::where('slug', $slug)->exists()) {
                    $skipped++;
                    continue;
                }

                \Aero\ApiHub\Jobs\ImportApiGuruJob::dispatch(
                    $apiData['provider'],
                    $apiData['version'],
                    $apiData['title'],
                    $apiData['category'],
                    $apiData['description']
                );

                $imported++;
            }

            Flash::success("Queued {$imported} APIs for import. Skipped {$skipped} existing APIs.");

        } catch (\Exception $e) {
            Flash::error('Bulk import failed: ' . $e->getMessage());
        }
    }

    /**
     * Test simple AJAX response (diagnostic)
     */
    public function onTestApifySimple()
    {
        \Log::info('🟢 onTestApifySimple called - ultra simple test');

        $searchTerm = post('search_term', 'test');

        \Log::info('🟢 Generating simple partial');

        $response = [
            'apify_results' => $this->makePartial('apify_results', [
                'status' => 'idle',
                'search_term' => $searchTerm
            ])
        ];

        \Log::info('🟢 Response ready to send', [
            'has_partial' => isset($response['apify_results']),
            'partial_length' => strlen($response['apify_results'])
        ]);

        return $response;
    }

    /**
     * Search RapidAPI via Apify (search only, no import)
     */
    public function onSearchApify()
    {
        \Log::info('🔵 onSearchApify called');

        $searchTerm = post('search_term');
        $maxItems = post('max_items', 10);

        \Log::info('🔵 Search params', ['term' => $searchTerm, 'max' => $maxItems]);

        if (empty($searchTerm)) {
            \Log::warning('🔵 No search term provided');
            Flash::error('Please enter a search term');
            return ['error' => 'No search term'];
        }

        try {
            \Log::info('🔵 Dispatching ApifySearchJob');

            // Queue the Apify search job (results only)
            \Aero\ApiHub\Jobs\ApifySearchJob::dispatch($searchTerm, $maxItems);

            \Log::info('🔵 Job dispatched, preparing response');

            Flash::info("Apify scraping started for: {$searchTerm}. This may take 30-60 seconds. Please wait...");

            // Generate the partial HTML
            $partialHtml = $this->makePartial('apify_results', [
                'status' => 'processing',
                'search_term' => $searchTerm
            ]);

            \Log::info('🔵 Partial generated', [
                'html_length' => strlen($partialHtml),
                'html_preview' => substr($partialHtml, 0, 100)
            ]);

            $response = [
                'apify_results' => $partialHtml
            ];

            \Log::info('🔵 Response prepared', [
                'has_key' => isset($response['apify_results']),
                'response_json' => json_encode(array_keys($response))
            ]);

            \Log::info('🔵 About to return response');

            return $response;

        } catch (\Exception $e) {
            \Log::error('🔵 Exception in onSearchApify: ' . $e->getMessage());
            \Log::error('🔵 Stack trace: ' . $e->getTraceAsString());
            Flash::error('Search failed: ' . $e->getMessage());

            return [
                'error' => $e->getMessage(),
                'apify_results' => $this->makePartial('apify_results', [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ])
            ];
        }
    }

    /**
     * Check Apify search results (polling endpoint)
     */
    public function onCheckApifyResults()
    {
        $searchTerm = post('search_term');

        if (empty($searchTerm)) {
            \Log::warning('onCheckApifyResults: No search term provided');
            return ['status' => 'error', 'message' => 'No search term provided'];
        }

        $cacheKey = 'apihub:apify_search:' . md5($searchTerm);
        $cached = \Cache::get($cacheKey);

        \Log::info('Apify results check', [
            'search_term' => $searchTerm,
            'cache_key' => $cacheKey,
            'cached_status' => $cached['status'] ?? 'not_found',
        ]);

        if (!$cached) {
            return ['status' => 'not_found'];
        }

        if ($cached['status'] === 'processing') {
            return ['status' => 'processing'];
        }

        if ($cached['status'] === 'failed') {
            return [
                'status' => 'failed',
                '#apifyResults' => $this->makePartial('apify_results', [
                    'status' => 'failed',
                    'error' => $cached['error'] ?? 'Search failed',
                    'search_term' => $searchTerm
                ])
            ];
        }

        if ($cached['status'] === 'completed') {
            return [
                'status' => 'completed',
                '#apifyResults' => $this->makePartial('apify_results', [
                    'status' => 'completed',
                    'results' => $cached['results'] ?? [],
                    'search_term' => $searchTerm
                ])
            ];
        }

        return ['status' => 'unknown'];
    }

    /**
     * Import single API from Apify results
     */
    public function onImportApifySingle()
    {
        $apiData = post();

        if (empty($apiData['name'])) {
            Flash::error('API name is required');
            return;
        }

        try {
            $api = ApiImporter::createApiFromApifyData($apiData);

            if ($api) {
                Flash::success("API imported: {$apiData['name']}");
            } else {
                Flash::warning("API already exists or could not be imported");
            }

        } catch (\Exception $e) {
            \Log::error('Apify single import failed: ' . $e->getMessage());
            Flash::error('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Check import status (for user feedback)
     */
    public function onCheckImportStatus()
    {
        $title = post('title');
        $slug = str_slug($title);

        // Check if it exists
        $api = Api::where('slug', $slug)->first();
        if ($api) {
            return [
                'status' => 'success',
                'message' => "✓ {$title} imported successfully with {$api->endpoints()->count()} endpoints"
            ];
        }

        // Check if it failed
        $failed = \Cache::get("apihub:import_failed:{$slug}");
        if ($failed) {
            return [
                'status' => 'failed',
                'message' => "✗ {$title} - {$failed['reason']}"
            ];
        }

        // Still processing
        return [
            'status' => 'processing',
            'message' => "⏳ {$title} - Processing..."
        ];
    }

    /**
     * Test AJAX endpoint
     */
    public function onTestAjax()
    {
        \Log::info('🧪 TEST AJAX CALLED!');

        return [
            'status' => 'success',
            'message' => '✅ AJAX funciona correctamente!',
            'timestamp' => now()->toDateTimeString(),
            'test' => $this->makePartial('test_result')
        ];
    }

    /**
     * Create API manually
     */
    public function onCreateManual()
    {
        $name = post('name');
        $category = post('category');
        $description = post('description');

        if (empty($name)) {
            Flash::error('API name is required');
            return;
        }

        try {
            $api = ApiImporter::createManual([
                'name' => $name,
                'category' => $category,
                'description' => $description,
            ]);

            if ($api) {
                Flash::success("API created: {$name}");
                return redirect('aero/apihub/apis/update/' . $api->id);
            } else {
                Flash::error('Failed to create API');
            }

        } catch (\Exception $e) {
            Flash::error('Create failed: ' . $e->getMessage());
        }
    }
}
