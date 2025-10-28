<?php namespace Aero\ApiHub\Jobs;

use Aero\ApiHub\Models\Api;
use Aero\ApiHub\Classes\RapidApiClient;
use Cache;
use Log;

/**
 * Sync API Job
 * Syncs a single API from RapidAPI
 */
class SyncApiJob
{
    /**
     * Execute the job
     */
    public function fire($job, $data)
    {
        $apiId = $data['api_id'];
        $lockKey = "apihub:sync:{$apiId}";

        // Prevent concurrent sync
        if (Cache::has($lockKey)) {
            Log::info('Sync already in progress for API ID: ' . $apiId);
            $job->delete();
            return;
        }

        try {
            // Acquire lock
            Cache::put($lockKey, true, 300); // 5 minutes

            $api = Api::find($apiId);

            if (!$api) {
                Log::error('API not found: ' . $apiId);
                $job->delete();
                return;
            }

            if (!$api->rapidapi_id) {
                Log::error('API missing RapidAPI ID: ' . $api->name);
                $job->delete();
                return;
            }

            $client = new RapidApiClient();

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

            Log::info('API synced: ' . $api->name);

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

                Log::info('Synced ' . count($endpoints) . ' endpoints for: ' . $api->name);
            }

            // Release lock
            Cache::forget($lockKey);

            // Job completed successfully
            $job->delete();

        } catch (\Exception $e) {
            Log::error('Failed to sync API', [
                'api_id' => $apiId,
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
