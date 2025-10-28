<?php namespace Aero\ApiHub\Classes;

use Cache;
use Illuminate\Support\Facades\Http;
use Log;
use Aero\ApiHub\Models\Settings;
use Exception;

/**
 * RapidAPI GraphQL Client
 * Handles communication with RapidAPI GraphQL endpoint
 */
class RapidApiClient
{
    protected $apiKey;
    protected $endpoint = 'https://rapidapi.com/graphql';
    protected $maxRetries = 3;
    protected $retryDelay = 1000; // milliseconds

    public function __construct()
    {
        $this->apiKey = Settings::getApiKey();

        if (!$this->apiKey) {
            throw new Exception('RapidAPI key not configured. Please set it in Settings.');
        }
    }

    /**
     * Execute GraphQL query with caching and retry logic
     */
    public function query(string $query, array $variables = [], bool $useCache = true, int $cacheTtl = 3600)
    {
        $cacheKey = $this->getCacheKey($query, $variables);

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = $this->executeWithRetry($query, $variables);

        if ($useCache && $result) {
            Cache::put($cacheKey, $result, $cacheTtl);
        }

        return $result;
    }

    /**
     * Search APIs on RapidAPI
     */
    public function searchApis(string $searchTerm, int $limit = 20)
    {
        $query = <<<'GRAPHQL'
        query SearchApis($term: String!, $limit: Int!) {
            apis(
                where: {
                    visibility: PUBLIC,
                    name: [$term]
                },
                first: $limit
            ) {
                edges {
                    node {
                        id
                        name
                        description
                        category {
                            name
                        }
                        currentVersion {
                            id
                            versionStatus
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $variables = [
            'term' => $searchTerm,
            'limit' => $limit,
        ];

        return $this->query($query, $variables);
    }

    /**
     * Get API details by ID
     */
    public function getApiDetails(string $apiId)
    {
        $query = <<<'GRAPHQL'
        query GetApiDetails($apiId: ID!) {
            api(id: $apiId) {
                id
                name
                description
                category {
                    name
                }
                currentVersion {
                    id
                    versionStatus
                }
            }
        }
        GRAPHQL;

        $variables = ['apiId' => $apiId];

        return $this->query($query, $variables);
    }

    /**
     * Get API endpoints by version ID
     */
    public function getEndpoints(string $versionId)
    {
        $query = <<<'GRAPHQL'
        query GetEndpoints($versionId: ID!) {
            apiVersion(id: $versionId) {
                id
                endpoints {
                    name
                    route
                    method
                    description
                    parameters {
                        name
                        type
                        required
                        description
                    }
                    headers {
                        name
                        type
                        required
                        description
                    }
                }
            }
        }
        GRAPHQL;

        $variables = ['versionId' => $versionId];

        return $this->query($query, $variables);
    }

    /**
     * Get popular APIs by category
     */
    public function getPopularApis(string $category = null, int $limit = 20)
    {
        $query = <<<'GRAPHQL'
        query GetPopularApis($category: String, $limit: Int!) {
            apis(
                where: {
                    visibility: PUBLIC
                    category: [$category]
                },
                orderBy: POPULARITY_DESC,
                first: $limit
            ) {
                edges {
                    node {
                        id
                        name
                        description
                        category {
                            name
                        }
                        currentVersion {
                            id
                        }
                    }
                }
            }
        }
        GRAPHQL;

        $variables = [
            'category' => $category,
            'limit' => $limit,
        ];

        return $this->query($query, $variables);
    }

    /**
     * Execute GraphQL request with retry logic
     */
    protected function executeWithRetry(string $query, array $variables = [], int $attempt = 1)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-rapidapi-key' => $this->apiKey,
                'x-rapidapi-host' => 'rapidapi.com',
            ])->post($this->endpoint, [
                'query' => $query,
                'variables' => $variables,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['errors'])) {
                    Log::error('RapidAPI GraphQL error', [
                        'errors' => $data['errors'],
                        'query' => $query,
                    ]);
                    return null;
                }

                return $data['data'] ?? null;
            }

            // Handle rate limiting or server errors with retry
            if ($response->status() === 429 || $response->status() >= 500) {
                if ($attempt < $this->maxRetries) {
                    usleep($this->retryDelay * 1000 * $attempt); // Exponential backoff
                    return $this->executeWithRetry($query, $variables, $attempt + 1);
                }
            }

            Log::error('RapidAPI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('RapidAPI exception', [
                'message' => $e->getMessage(),
                'attempt' => $attempt,
            ]);

            if ($attempt < $this->maxRetries) {
                usleep($this->retryDelay * 1000 * $attempt);
                return $this->executeWithRetry($query, $variables, $attempt + 1);
            }

            return null;
        }
    }

    /**
     * Generate cache key for query
     */
    protected function getCacheKey(string $query, array $variables): string
    {
        $hash = md5($query . json_encode($variables));
        return "apihub:rapidapi:{$hash}";
    }

    /**
     * Clear all RapidAPI cache
     */
    public function clearCache()
    {
        Cache::flush();
    }

    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->searchApis('weather', 1);
            return $result !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}
