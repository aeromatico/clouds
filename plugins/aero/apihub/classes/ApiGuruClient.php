<?php namespace Aero\ApiHub\Classes;

use Cache;
use Illuminate\Support\Facades\Http;
use Log;
use Exception;

/**
 * APIs.guru Client
 * Handles communication with APIs.guru API
 */
class ApiGuruClient
{
    protected $baseUrl = 'https://api.apis.guru/v2';
    protected $timeout = 30; // seconds

    /**
     * Get all APIs from APIs.guru
     *
     * @param bool $useCache Use Redis cache
     * @return array|null
     */
    public function getAllApis(bool $useCache = true)
    {
        $cacheKey = 'apihub:apis_list';

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/list.json");

            if (!$response->successful()) {
                Log::error('APIs.guru list request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            if ($useCache && $data) {
                // Cache for 24 hours
                Cache::put($cacheKey, $data, 86400);
            }

            return $data;

        } catch (Exception $e) {
            Log::error('APIs.guru exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get OpenAPI spec for a specific API
     *
     * @param string $provider Provider name (e.g., 'github.com')
     * @param string $version Version (e.g., '1.1.4')
     * @param bool $useCache Use Redis cache
     * @return array|null
     */
    public function getOpenApiSpec(string $provider, string $version, bool $useCache = true)
    {
        $cacheKey = "apihub:spec:{$provider}:{$version}";

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Build URL: /specs/{provider}/{version}/openapi.json
            $url = "{$this->baseUrl}/specs/{$provider}/{$version}/openapi.json";

            $response = Http::timeout($this->timeout)->get($url);

            if (!$response->successful()) {
                Log::error('APIs.guru spec request failed', [
                    'provider' => $provider,
                    'version' => $version,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            if ($useCache && $data) {
                // Cache for 1 hour
                Cache::put($cacheKey, $data, 3600);
            }

            return $data;

        } catch (Exception $e) {
            Log::error('APIs.guru spec exception', [
                'provider' => $provider,
                'version' => $version,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Search APIs by term
     *
     * @param string $searchTerm Search term
     * @param int $limit Max results
     * @return array
     */
    public function searchApis(string $searchTerm, int $limit = 20): array
    {
        $allApis = $this->getAllApis();

        if (!$allApis) {
            return [];
        }

        $searchTerm = strtolower($searchTerm);
        $results = [];
        $count = 0;

        foreach ($allApis as $apiKey => $apiData) {
            if ($count >= $limit) {
                break;
            }

            // Get the latest version info
            $versions = $apiData['versions'] ?? [];
            $preferred = $apiData['preferred'] ?? array_key_first($versions);
            $info = $versions[$preferred] ?? null;

            if (!$info) {
                continue;
            }

            $title = $info['info']['title'] ?? '';
            $description = $info['info']['description'] ?? '';
            $category = $info['info']['x-apisguru-categories'][0] ?? 'Other';

            // Search in title, description, and provider
            $haystack = strtolower($title . ' ' . $description . ' ' . $apiKey);

            if (strpos($haystack, $searchTerm) !== false) {
                // Parse provider from key (e.g., "github.com" or "github.com:api.github.com")
                $parts = explode(':', $apiKey);
                $provider = $parts[0];

                $results[] = [
                    'key' => $apiKey,
                    'provider' => $provider,
                    'version' => $preferred,
                    'title' => $title,
                    'description' => substr($description, 0, 200),
                    'category' => $category,
                    'spec_url' => $info['swaggerUrl'] ?? null,
                ];

                $count++;
            }
        }

        return $results;
    }

    /**
     * Get popular APIs by category
     *
     * @param string|null $category Category filter
     * @param int $limit Max results
     * @return array
     */
    public function getPopularApis(?string $category = null, int $limit = 20): array
    {
        $allApis = $this->getAllApis();

        if (!$allApis) {
            return [];
        }

        $results = [];
        $count = 0;

        foreach ($allApis as $apiKey => $apiData) {
            if ($count >= $limit) {
                break;
            }

            $versions = $apiData['versions'] ?? [];
            $preferred = $apiData['preferred'] ?? array_key_first($versions);
            $info = $versions[$preferred] ?? null;

            if (!$info) {
                continue;
            }

            $apiCategory = $info['info']['x-apisguru-categories'][0] ?? 'Other';

            // Filter by category if specified
            if ($category && strtolower($apiCategory) !== strtolower($category)) {
                continue;
            }

            $parts = explode(':', $apiKey);
            $provider = $parts[0];

            $results[] = [
                'key' => $apiKey,
                'provider' => $provider,
                'version' => $preferred,
                'title' => $info['info']['title'] ?? '',
                'description' => substr($info['info']['description'] ?? '', 0, 200),
                'category' => $apiCategory,
                'spec_url' => $info['swaggerUrl'] ?? null,
            ];

            $count++;
        }

        return $results;
    }

    /**
     * Get available categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        $allApis = $this->getAllApis();

        if (!$allApis) {
            return [];
        }

        $categories = [];

        foreach ($allApis as $apiData) {
            $versions = $apiData['versions'] ?? [];
            $preferred = $apiData['preferred'] ?? array_key_first($versions);
            $info = $versions[$preferred] ?? null;

            if (!$info) {
                continue;
            }

            $apiCategories = $info['info']['x-apisguru-categories'] ?? ['Other'];
            foreach ($apiCategories as $cat) {
                if (!isset($categories[$cat])) {
                    $categories[$cat] = 0;
                }
                $categories[$cat]++;
            }
        }

        // Sort by count descending
        arsort($categories);

        return $categories;
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        Cache::forget('apihub:apis_list');

        // Clear all spec caches
        $pattern = 'apihub:spec:*';
        // Note: This is a simple approach. For production, use Redis SCAN
        Cache::flush();
    }

    /**
     * Test API connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/list.json");
            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }
}
