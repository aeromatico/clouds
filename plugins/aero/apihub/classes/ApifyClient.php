<?php namespace Aero\ApiHub\Classes;

use Cache;
use Illuminate\Support\Facades\Http;
use Log;
use Exception;
use Aero\ApiHub\Models\Settings;

/**
 * Apify Client
 * Scrapes RapidAPI using Apify actor
 */
class ApifyClient
{
    protected $apiToken;
    protected $baseUrl = 'https://api.apify.com/v2';
    protected $actorId = 'yourapiservice~rapidapi-scraper';
    protected $timeout = 60; // seconds
    protected $maxWaitTime = 300; // 5 minutes max wait for scraper

    public function __construct()
    {
        $this->apiToken = Settings::getApifyToken();

        if (!$this->apiToken) {
            throw new Exception('Apify API token not configured. Please set it in Settings.');
        }
    }

    /**
     * Search RapidAPI via Apify scraper
     *
     * @param string $searchTerm Search term
     * @param int $maxItems Max items per category
     * @return array|null
     */
    public function searchRapidApi(string $searchTerm, int $maxItems = 10)
    {
        try {
            // Start the actor run
            $runId = $this->startActorRun($searchTerm, $maxItems);

            if (!$runId) {
                Log::error('Failed to start Apify actor run');
                return null;
            }

            Log::info('Apify actor started', ['run_id' => $runId, 'search_term' => $searchTerm]);

            // Wait for completion (with timeout)
            $completed = $this->waitForCompletion($runId);

            if (!$completed) {
                Log::error('Apify actor run timeout', ['run_id' => $runId]);
                return null;
            }

            // Get results
            $results = $this->getDatasetItems($runId);

            if ($results) {
                Log::info('Apify scraping completed', [
                    'run_id' => $runId,
                    'results_count' => count($results),
                ]);
            }

            return $results;

        } catch (Exception $e) {
            Log::error('Apify search failed', [
                'error' => $e->getMessage(),
                'search_term' => $searchTerm,
            ]);
            return null;
        }
    }

    /**
     * Start Apify actor run
     *
     * @param string $searchTerm Search term
     * @param int $maxItems Max items
     * @return string|null Run ID
     */
    protected function startActorRun(string $searchTerm, int $maxItems): ?string
    {
        $url = "{$this->baseUrl}/acts/{$this->actorId}/runs";

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'search_term' => $searchTerm,
                'maxItemsPerCategory' => $maxItems,
            ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';

            Log::error('Apify actor start failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'error_message' => $errorMessage,
            ]);

            // Throw exception with clear message
            if ($response->status() === 403) {
                throw new Exception('Apify Actor Rental Required: ' . $errorMessage);
            }

            throw new Exception('Apify actor failed to start: ' . $errorMessage);
        }

        $data = $response->json();
        return $data['data']['id'] ?? null;
    }

    /**
     * Wait for actor run completion
     *
     * @param string $runId Run ID
     * @return bool Success
     */
    protected function waitForCompletion(string $runId): bool
    {
        $startTime = time();
        $checkInterval = 5; // Check every 5 seconds

        while (true) {
            // Check timeout
            if (time() - $startTime > $this->maxWaitTime) {
                return false;
            }

            // Get run status
            $status = $this->getRunStatus($runId);

            if (!$status) {
                sleep($checkInterval);
                continue;
            }

            // Check if completed
            if (in_array($status, ['SUCCEEDED', 'FINISHED'])) {
                return true;
            }

            // Check if failed
            if (in_array($status, ['FAILED', 'ABORTED', 'TIMED-OUT'])) {
                Log::error('Apify actor run failed', ['run_id' => $runId, 'status' => $status]);
                return false;
            }

            // Still running, wait and check again
            sleep($checkInterval);
        }
    }

    /**
     * Get actor run status
     *
     * @param string $runId Run ID
     * @return string|null Status
     */
    protected function getRunStatus(string $runId): ?string
    {
        $url = "{$this->baseUrl}/acts/{$this->actorId}/runs/{$runId}";

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
            ])
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        return $data['data']['status'] ?? null;
    }

    /**
     * Get dataset items from completed run
     *
     * @param string $runId Run ID
     * @return array|null Results
     */
    protected function getDatasetItems(string $runId): ?array
    {
        $url = "{$this->baseUrl}/acts/{$this->actorId}/runs/{$runId}/dataset/items";

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
            ])
            ->get($url);

        if (!$response->successful()) {
            Log::error('Failed to get Apify dataset items', [
                'status' => $response->status(),
                'run_id' => $runId,
            ]);
            return null;
        }

        $items = $response->json();

        // Parse results
        return $this->parseResults($items);
    }

    /**
     * Parse Apify scraper results
     *
     * @param array $items Raw items
     * @return array Parsed results
     */
    protected function parseResults(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            // Skip if missing required fields
            if (empty($item['name']) || empty($item['category'])) {
                continue;
            }

            $results[] = [
                'name' => $item['name'],
                'description' => $item['description'] ?? '',
                'category' => $item['category'] ?? 'Other',
                'pricing' => $item['pricing'] ?? null,
                'endpoints' => $this->parseEndpoints($item['endpoints'] ?? []),
                'base_url' => $item['base_url'] ?? null,
                'rapidapi_id' => $item['id'] ?? null,
                'raw_data' => $item,
            ];
        }

        return $results;
    }

    /**
     * Parse endpoints from scraped data
     *
     * @param array $endpoints Raw endpoints
     * @return array Parsed endpoints
     */
    protected function parseEndpoints(array $endpoints): array
    {
        $parsed = [];

        foreach ($endpoints as $endpoint) {
            $parsed[] = [
                'name' => $endpoint['name'] ?? 'Unnamed Endpoint',
                'method' => strtoupper($endpoint['method'] ?? 'GET'),
                'route' => $endpoint['route'] ?? $endpoint['path'] ?? '/',
                'description' => $endpoint['description'] ?? null,
                'parameters' => $endpoint['parameters'] ?? [],
                'headers' => $endpoint['headers'] ?? [],
                'response_example' => $endpoint['response'] ?? null,
            ];
        }

        return $parsed;
    }

    /**
     * Test API connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $url = "{$this->baseUrl}/acts/{$this->actorId}";

            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                ])
                ->get($url);

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get recent runs
     *
     * @param int $limit Limit
     * @return array
     */
    public function getRecentRuns(int $limit = 10): array
    {
        $url = "{$this->baseUrl}/acts/{$this->actorId}/runs?limit={$limit}";

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
            ])
            ->get($url);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        return $data['data']['items'] ?? [];
    }
}
