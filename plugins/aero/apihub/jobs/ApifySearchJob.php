<?php namespace Aero\ApiHub\Jobs;

use Aero\ApiHub\Classes\ApifyClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use Log;

/**
 * Search RapidAPI via Apify scraper (results only, no import)
 * Results are cached for 1 hour for user review
 */
class ApifySearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string Search term
     */
    protected $searchTerm;

    /**
     * @var int Max items per category
     */
    protected $maxItems;

    /**
     * Number of times the job may be attempted
     */
    public $tries = 2;

    /**
     * Timeout for the job (5 minutes)
     */
    public $timeout = 300;

    /**
     * Create a new job instance
     */
    public function __construct(string $searchTerm, int $maxItems = 10)
    {
        $this->searchTerm = $searchTerm;
        $this->maxItems = $maxItems;
    }

    /**
     * Execute the job
     */
    public function handle()
    {
        $cacheKey = $this->getCacheKey();
        $lockKey = "apihub:apify_lock:{$this->searchTerm}";

        // Prevent concurrent scraping of same term
        if (Cache::has($lockKey)) {
            Log::info('Apify scraping already in progress', [
                'search_term' => $this->searchTerm,
            ]);
            return;
        }

        // Mark as processing
        Cache::put($cacheKey, ['status' => 'processing'], 600);
        Cache::put($lockKey, true, 600);

        try {
            Log::info('Starting Apify search job', [
                'search_term' => $this->searchTerm,
                'max_items' => $this->maxItems,
            ]);

            // Create Apify client and search
            $client = new ApifyClient();
            $results = $client->searchRapidApi($this->searchTerm, $this->maxItems);

            if (!$results || empty($results)) {
                Log::warning('No results from Apify scraper', [
                    'search_term' => $this->searchTerm,
                ]);

                Cache::put($cacheKey, [
                    'status' => 'completed',
                    'results' => [],
                    'message' => 'No APIs found'
                ], 3600);

                Cache::forget($lockKey);
                return;
            }

            Log::info('Apify search completed', [
                'search_term' => $this->searchTerm,
                'results_count' => count($results),
            ]);

            // Cache results for 1 hour
            Cache::put($cacheKey, [
                'status' => 'completed',
                'results' => $results,
                'search_term' => $this->searchTerm,
                'count' => count($results)
            ], 3600);

        } catch (\Exception $e) {
            Log::error('Apify search job failed', [
                'search_term' => $this->searchTerm,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 3600);

        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Get cache key for this search
     */
    protected function getCacheKey(): string
    {
        return 'apihub:apify_search:' . md5($this->searchTerm);
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Apify search job failed permanently', [
            'search_term' => $this->searchTerm,
            'error' => $exception->getMessage(),
        ]);

        $cacheKey = $this->getCacheKey();
        Cache::put($cacheKey, [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ], 3600);

        // Release lock
        Cache::forget("apihub:apify_lock:{$this->searchTerm}");
    }
}
