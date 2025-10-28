<?php namespace Aero\ApiHub\Classes;

use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Models\Settings;
use Log;
use Queue;

/**
 * API Importer Orchestrator
 * Handles importing from multiple sources
 */
class ApiImporter
{
    /**
     * Import from APIs.guru
     *
     * @param string $provider Provider name
     * @param string $version Version
     * @param string $title Title
     * @param string $category Category
     * @param string $description Description
     * @param bool $queue Queue the job
     * @return bool Success
     */
    public static function importFromApisGuru(
        string $provider,
        string $version,
        string $title,
        string $category,
        string $description = '',
        bool $queue = true
    ): bool {
        try {
            // Check if already exists
            $slug = str_slug($title);
            if (Api::where('slug', $slug)->exists()) {
                Log::info('API already exists', ['slug' => $slug, 'source' => 'apis_guru']);
                return false;
            }

            if ($queue) {
                \Aero\ApiHub\Jobs\ImportApiGuruJob::dispatch(
                    $provider,
                    $version,
                    $title,
                    $category,
                    $description
                );

                Log::info('APIs.guru import queued', ['title' => $title]);
                return true;
            }

            // Direct import (not recommended for APIs.guru due to large specs)
            return false;

        } catch (\Exception $e) {
            Log::error('APIs.guru import failed', [
                'error' => $e->getMessage(),
                'title' => $title,
            ]);
            return false;
        }
    }

    /**
     * Import from Apify (RapidAPI scraper)
     *
     * @param string $searchTerm Search term
     * @param int $maxItems Max items
     * @param bool $queue Queue the job
     * @return bool|string Success or Run ID
     */
    public static function importFromApify(
        string $searchTerm,
        int $maxItems = 10,
        bool $queue = true
    ) {
        try {
            if ($queue) {
                // Queue the import job
                \Aero\ApiHub\Jobs\ImportApifyJob::dispatch($searchTerm, $maxItems);

                Log::info('Apify import queued', ['search_term' => $searchTerm]);
                return true;
            }

            // Direct import (synchronous)
            $client = new ApifyClient();
            $results = $client->searchRapidApi($searchTerm, $maxItems);

            if (!$results) {
                return false;
            }

            $imported = 0;
            foreach ($results as $apiData) {
                if (static::createApiFromApifyData($apiData)) {
                    $imported++;
                }
            }

            Log::info('Apify import completed', [
                'search_term' => $searchTerm,
                'imported' => $imported,
            ]);

            return $imported > 0;

        } catch (\Exception $e) {
            Log::error('Apify import failed', [
                'error' => $e->getMessage(),
                'search_term' => $searchTerm,
            ]);
            return false;
        }
    }

    /**
     * Create API from Apify scraped data
     *
     * @param array $data Scraped data
     * @return Api|null
     */
    public static function createApiFromApifyData(array $data): ?Api
    {
        try {
            $slug = str_slug($data['name']);

            // Check if already exists
            if (Api::where('slug', $slug)->exists()) {
                Log::info('API already exists', ['slug' => $slug, 'source' => 'apify']);
                return null;
            }

            // Create API record
            $api = Api::create([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'],
                'category' => $data['category'],
                'source' => 'apify',
                'rapidapi_id' => $data['rapidapi_id'] ?? null,
                'raw_data' => $data['raw_data'] ?? $data,
                'synced_at' => now(),
            ]);

            // Create endpoints
            foreach ($data['endpoints'] as $endpointData) {
                $api->endpoints()->create([
                    'name' => $endpointData['name'],
                    'method' => $endpointData['method'],
                    'route' => $endpointData['route'],
                    'description' => $endpointData['description'],
                    'parameters' => $endpointData['parameters'],
                    'headers' => $endpointData['headers'],
                    'response_example' => $endpointData['response_example'],
                ]);
            }

            Log::info('API created from Apify', [
                'api_id' => $api->id,
                'name' => $api->name,
                'endpoints_count' => count($data['endpoints']),
            ]);

            return $api;

        } catch (\Exception $e) {
            Log::error('Failed to create API from Apify data', [
                'error' => $e->getMessage(),
                'name' => $data['name'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * Create API manually
     *
     * @param array $data API data
     * @return Api|null
     */
    public static function createManual(array $data): ?Api
    {
        try {
            $api = Api::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? str_slug($data['name']),
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
                'source' => 'manual',
                'synced_at' => now(),
            ]);

            Log::info('API created manually', [
                'api_id' => $api->id,
                'name' => $api->name,
            ]);

            return $api;

        } catch (\Exception $e) {
            Log::error('Failed to create API manually', [
                'error' => $e->getMessage(),
                'name' => $data['name'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * Get preferred import source from settings
     *
     * @return string
     */
    public static function getPreferredSource(): string
    {
        $settings = Settings::instance();
        return $settings->import_source ?? 'apis_guru';
    }

    /**
     * Check if source is available
     *
     * @param string $source Source name
     * @return bool
     */
    public static function isSourceAvailable(string $source): bool
    {
        switch ($source) {
            case 'apis_guru':
                try {
                    $client = new ApiGuruClient();
                    return $client->testConnection();
                } catch (\Exception $e) {
                    return false;
                }

            case 'apify':
                try {
                    $client = new ApifyClient();
                    return $client->testConnection();
                } catch (\Exception $e) {
                    return false;
                }

            case 'manual':
                return true;

            default:
                return false;
        }
    }

    /**
     * Get source statistics
     *
     * @return array
     */
    public static function getSourceStats(): array
    {
        return [
            'apis_guru' => Api::where('source', 'apis_guru')->count(),
            'apify' => Api::where('source', 'apify')->count(),
            'manual' => Api::where('source', 'manual')->count(),
            'legacy' => Api::where('source', 'legacy')->count(),
            'total' => Api::count(),
        ];
    }
}
