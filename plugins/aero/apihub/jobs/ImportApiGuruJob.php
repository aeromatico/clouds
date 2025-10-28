<?php namespace Aero\ApiHub\Jobs;

use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Classes\ApiGuruClient;
use Aero\ApiHub\Classes\OpenApiParser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use Log;

/**
 * Import API from APIs.guru
 * Async job to fetch and parse OpenAPI specs
 */
class ImportApiGuruJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string Provider name
     */
    protected $provider;

    /**
     * @var string Version number
     */
    protected $version;

    /**
     * @var string API title
     */
    protected $title;

    /**
     * @var string Category
     */
    protected $category;

    /**
     * @var string Description
     */
    protected $description;

    /**
     * Number of times the job may be attempted
     */
    public $tries = 3;

    /**
     * Create a new job instance
     */
    public function __construct(string $provider, string $version, string $title, string $category, string $description = '')
    {
        $this->provider = $provider;
        $this->version = $version;
        $this->title = $title;
        $this->category = $category;
        $this->description = $description;
    }

    /**
     * Execute the job
     */
    public function handle()
    {
        $lockKey = "apihub:import_lock:{$this->provider}:{$this->version}";

        // Prevent concurrent imports
        if (Cache::has($lockKey)) {
            Log::info('Import already in progress', [
                'provider' => $this->provider,
                'version' => $this->version,
            ]);
            return;
        }

        // Acquire lock for 5 minutes
        Cache::put($lockKey, true, 300);

        try {
            // Check if already exists
            $slug = str_slug($this->title);
            $existing = Api::where('slug', $slug)->first();

            if ($existing) {
                Log::info('API already exists', ['slug' => $slug]);
                Cache::forget($lockKey);
                return;
            }

            // Fetch OpenAPI spec
            $client = new ApiGuruClient();
            $spec = $client->getOpenApiSpec($this->provider, $this->version);

            if (!$spec) {
                Log::error('Failed to fetch OpenAPI spec - BROKEN LINK', [
                    'provider' => $this->provider,
                    'version' => $this->version,
                    'title' => $this->title,
                ]);

                // Save failure info for user feedback
                Cache::put("apihub:import_failed:{$slug}", [
                    'title' => $this->title,
                    'provider' => $this->provider,
                    'version' => $this->version,
                    'reason' => 'Broken OpenAPI specification link (404)'
                ], 3600);

                Cache::forget($lockKey);
                return;
            }

            // Parse spec
            $parser = new OpenApiParser();
            $metadata = $parser->extractMetadata($spec);
            $endpoints = $parser->parseSpec($spec);

            // Create API record
            $api = Api::create([
                'name' => $this->title,
                'slug' => $slug,
                'description' => $this->description ?: $metadata['description'],
                'category' => $this->category,
                'source' => 'apis_guru',
                'rapidapi_id' => $this->provider, // Store provider as ID
                'rapidapi_version_id' => $this->version,
                'raw_data' => [
                    'provider' => $this->provider,
                    'version' => $this->version,
                    'base_url' => $metadata['base_url'],
                    'contact' => $metadata['contact'],
                    'license' => $metadata['license'],
                    'source' => 'apis.guru',
                ],
                'synced_at' => now(),
            ]);

            // Create endpoints
            foreach ($endpoints as $endpointData) {
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

            Log::info('API imported successfully', [
                'api_id' => $api->id,
                'title' => $this->title,
                'endpoints_count' => count($endpoints),
            ]);

        } catch (\Exception $e) {
            Log::error('Import job failed', [
                'provider' => $this->provider,
                'version' => $this->version,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Import job failed permanently', [
            'provider' => $this->provider,
            'version' => $this->version,
            'error' => $exception->getMessage(),
        ]);

        // Release lock
        Cache::forget("apihub:import_lock:{$this->provider}:{$this->version}");
    }
}
