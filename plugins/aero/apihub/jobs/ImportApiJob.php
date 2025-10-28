<?php namespace Aero\ApiHub\Jobs;

use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Classes\RapidApiClient;
use Cache;
use Log;

/**
 * Import API Job
 * Handles background import of APIs from RapidAPI
 */
class ImportApiJob
{
    /**
     * Execute the job
     */
    public function fire($job, $data)
    {
        $lockKey = "apihub:import:{$data['rapidapi_id']}";

        // Prevent concurrent imports of the same API
        if (Cache::has($lockKey)) {
            Log::info('Import already in progress for API: ' . $data['rapidapi_id']);
            $job->delete();
            return;
        }

        try {
            // Acquire lock
            Cache::put($lockKey, true, 300); // 5 minutes

            // Check if already exists
            if (Api::where('rapidapi_id', $data['rapidapi_id'])->exists()) {
                Log::info('API already exists: ' . $data['rapidapi_id']);
                $job->delete();
                return;
            }

            $client = new RapidApiClient();

            // Create API record
            $api = Api::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'category' => $data['category'] ?? 'Uncategorized',
                'rapidapi_id' => $data['rapidapi_id'],
                'rapidapi_version_id' => $data['version_id'] ?? null,
                'synced_at' => now(),
            ]);

            Log::info('API created: ' . $api->name);

            // Import endpoints if version ID is available
            if (!empty($data['version_id'])) {
                $result = $client->getEndpoints($data['version_id']);
                $endpoints = $result['apiVersion']['endpoints'] ?? [];

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

                Log::info('Imported ' . count($endpoints) . ' endpoints for API: ' . $api->name);
            }

            // Release lock
            Cache::forget($lockKey);

            // Job completed successfully
            $job->delete();

        } catch (\Exception $e) {
            Log::error('Failed to import API', [
                'rapidapi_id' => $data['rapidapi_id'],
                'error' => $e->getMessage(),
            ]);

            // Release lock
            Cache::forget($lockKey);

            // Retry or fail
            if ($job->attempts() < 3) {
                $job->release(60); // Retry after 60 seconds
            } else {
                $job->delete();
            }
        }
    }
}
