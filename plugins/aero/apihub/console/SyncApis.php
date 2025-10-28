<?php namespace Aero\ApiHub\Console;

use Illuminate\Console\Command;
use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Classes\RapidApiClient;
use Cache;
use Queue;

/**
 * SyncApis Console Command
 * Syncs APIs from RapidAPI
 */
class SyncApis extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'apihub:sync';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync APIs from RapidAPI';

    /**
     * @var string Command signature
     */
    protected $signature = 'apihub:sync
                            {--id= : Sync specific API by ID}
                            {--all : Sync all APIs}
                            {--queue : Queue sync jobs instead of running immediately}
                            {--force : Force sync even if recently synced}';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting API sync...');

        try {
            $client = new RapidApiClient();

            // Test connection
            if (!$client->testConnection()) {
                $this->error('Failed to connect to RapidAPI. Please check your API key.');
                return 1;
            }

            $this->info('RapidAPI connection successful.');

            // Sync specific API
            if ($apiId = $this->option('id')) {
                return $this->syncSpecificApi($apiId, $client);
            }

            // Sync all APIs
            if ($this->option('all')) {
                return $this->syncAllApis($client);
            }

            // Default: sync APIs that need sync
            return $this->syncNeededApis($client);

        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Sync specific API
     */
    protected function syncSpecificApi($apiId, $client)
    {
        $api = Api::find($apiId);

        if (!$api) {
            $this->error("API not found: {$apiId}");
            return 1;
        }

        if (!$api->rapidapi_id) {
            $this->error("API missing RapidAPI ID: {$api->name}");
            return 1;
        }

        $this->info("Syncing API: {$api->name}");

        if ($this->option('queue')) {
            $this->queueSync($api);
        } else {
            $this->syncApi($api, $client);
        }

        $this->info('Sync completed successfully.');
        return 0;
    }

    /**
     * Sync all APIs
     */
    protected function syncAllApis($client)
    {
        $apis = Api::whereNotNull('rapidapi_id')->get();
        $total = $apis->count();

        if ($total === 0) {
            $this->warn('No APIs found to sync.');
            return 0;
        }

        $this->info("Found {$total} APIs to sync.");

        $progress = $this->output->createProgressBar($total);
        $synced = 0;
        $failed = 0;

        foreach ($apis as $api) {
            try {
                if ($this->option('queue')) {
                    $this->queueSync($api);
                } else {
                    $this->syncApi($api, $client);
                }
                $synced++;
            } catch (\Exception $e) {
                $this->error("\nFailed to sync {$api->name}: " . $e->getMessage());
                $failed++;
            }
            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("Sync completed. Success: {$synced}, Failed: {$failed}");
        return 0;
    }

    /**
     * Sync APIs that need sync
     */
    protected function syncNeededApis($client)
    {
        $days = $this->option('force') ? 0 : 7;
        $apis = Api::needsSync($days)->whereNotNull('rapidapi_id')->get();
        $total = $apis->count();

        if ($total === 0) {
            $this->info('All APIs are up to date.');
            return 0;
        }

        $this->info("Found {$total} APIs that need sync.");

        $progress = $this->output->createProgressBar($total);
        $synced = 0;
        $failed = 0;

        foreach ($apis as $api) {
            try {
                if ($this->option('queue')) {
                    $this->queueSync($api);
                } else {
                    $this->syncApi($api, $client);
                }
                $synced++;
            } catch (\Exception $e) {
                $this->error("\nFailed to sync {$api->name}: " . $e->getMessage());
                $failed++;
            }
            $progress->advance();
        }

        $progress->finish();
        $this->newLine(2);
        $this->info("Sync completed. Success: {$synced}, Failed: {$failed}");
        return 0;
    }

    /**
     * Sync single API
     */
    protected function syncApi($api, $client)
    {
        $lockKey = "apihub:sync:{$api->id}";

        // Prevent concurrent sync
        if (Cache::has($lockKey)) {
            $this->warn("Sync already in progress for: {$api->name}");
            return;
        }

        Cache::put($lockKey, true, 300); // 5 minutes

        try {
            // Get API details
            $result = $client->getApiDetails($api->rapidapi_id);
            $apiData = $result['api'] ?? null;

            if (!$apiData) {
                throw new \Exception('API not found on RapidAPI');
            }

            // Update API
            $api->name = $apiData['name'];
            $api->description = $apiData['description'] ?? null;
            $api->category = $apiData['category']['name'] ?? null;
            $api->rapidapi_version_id = $apiData['currentVersion']['id'] ?? null;
            $api->raw_data = $apiData;
            $api->synced_at = now();
            $api->save();

            // Sync endpoints
            if ($api->rapidapi_version_id) {
                $endpointsResult = $client->getEndpoints($api->rapidapi_version_id);
                $endpoints = $endpointsResult['apiVersion']['endpoints'] ?? [];

                // Delete old endpoints
                $api->endpoints()->delete();

                // Create new endpoints
                foreach ($endpoints as $endpoint) {
                    $api->endpoints()->create([
                        'name' => $endpoint['name'],
                        'method' => $endpoint['method'],
                        'route' => $endpoint['route'],
                        'description' => $endpoint['description'] ?? null,
                        'parameters' => $endpoint['parameters'] ?? [],
                        'headers' => $endpoint['headers'] ?? [],
                    ]);
                }
            }

        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Queue sync job
     */
    protected function queueSync($api)
    {
        Queue::push(\Aero\ApiHub\Jobs\SyncApiJob::class, [
            'api_id' => $api->id,
        ]);
    }
}
