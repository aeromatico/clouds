<?php namespace Aero\ApiHub\Jobs;

use Aero\ApiHub\Classes\ApifyClient;
use Aero\ApiHub\Classes\ApiImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Cache;
use Log;

/**
 * Import APIs from RapidAPI via Apify scraper
 * This job handles async scraping which can take 30+ seconds
 */
class ImportApifyJob implements ShouldQueue
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
    public $tries = 2; // Apify is slower, only 2 tries

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
        $lockKey = "apihub:apify_lock:{$this->searchTerm}";

        // Prevent concurrent scraping of same term
        if (Cache::has($lockKey)) {
            Log::info('Apify scraping already in progress', [
                'search_term' => $this->searchTerm,
            ]);
            return;
        }

        // Acquire lock for 10 minutes (scraping can take a while)
        Cache::put($lockKey, true, 600);

        try {
            Log::info('Starting Apify scraping job', [
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
                Cache::forget($lockKey);
                return;
            }

            Log::info('Apify scraping completed', [
                'search_term' => $this->searchTerm,
                'results_count' => count($results),
            ]);

            // Import each API
            $imported = 0;
            $skipped = 0;

            foreach ($results as $apiData) {
                $api = ApiImporter::createApiFromApifyData($apiData);

                if ($api) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            Log::info('Apify import job completed', [
                'search_term' => $this->searchTerm,
                'imported' => $imported,
                'skipped' => $skipped,
            ]);

        } catch (\Exception $e) {
            Log::error('Apify import job failed', [
                'search_term' => $this->searchTerm,
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
        Log::error('Apify import job failed permanently', [
            'search_term' => $this->searchTerm,
            'error' => $exception->getMessage(),
        ]);

        // Release lock
        Cache::forget("apihub:apify_lock:{$this->searchTerm}");
    }
}
