<?php namespace Aero\ApiHub\Updates;

use Aero\ApiHub\Models\Api;
use October\Rain\Database\Updates\Seeder;

/**
 * Seed Example APIs
 */
class SeedExampleApis extends Seeder
{
    public function run()
    {
        // Weather API
        $weatherApi = Api::create([
            'name' => 'OpenWeather API',
            'slug' => 'openweather-api',
            'description' => 'Access current weather data for any location including over 200,000 cities.',
            'category' => 'Weather',
            'rapidapi_id' => 'openweathermap',
            'synced_at' => now(),
        ]);

        $weatherApi->endpoints()->createMany([
            [
                'name' => 'Current Weather',
                'method' => 'GET',
                'route' => '/weather',
                'description' => 'Get current weather data for a city',
                'parameters' => [
                    ['name' => 'q', 'type' => 'string', 'required' => true, 'description' => 'City name'],
                    ['name' => 'units', 'type' => 'string', 'required' => false, 'description' => 'Units of measurement (metric, imperial)'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => '5 Day Forecast',
                'method' => 'GET',
                'route' => '/forecast',
                'description' => 'Get 5 day weather forecast',
                'parameters' => [
                    ['name' => 'q', 'type' => 'string', 'required' => true, 'description' => 'City name'],
                    ['name' => 'cnt', 'type' => 'integer', 'required' => false, 'description' => 'Number of timestamps'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Air Pollution',
                'method' => 'GET',
                'route' => '/air_pollution',
                'description' => 'Get current air pollution data',
                'parameters' => [
                    ['name' => 'lat', 'type' => 'number', 'required' => true, 'description' => 'Latitude'],
                    ['name' => 'lon', 'type' => 'number', 'required' => true, 'description' => 'Longitude'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
        ]);

        // Cryptocurrency API
        $cryptoApi = Api::create([
            'name' => 'CoinGecko API',
            'slug' => 'coingecko-api',
            'description' => 'Comprehensive cryptocurrency data including prices, market data, and more.',
            'category' => 'Finance',
            'rapidapi_id' => 'coingecko',
            'synced_at' => now(),
        ]);

        $cryptoApi->endpoints()->createMany([
            [
                'name' => 'Coin List',
                'method' => 'GET',
                'route' => '/coins/list',
                'description' => 'Get list of all supported coins',
                'parameters' => [
                    ['name' => 'include_platform', 'type' => 'boolean', 'required' => false, 'description' => 'Include platform info'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Coin Price',
                'method' => 'GET',
                'route' => '/simple/price',
                'description' => 'Get current price of cryptocurrencies',
                'parameters' => [
                    ['name' => 'ids', 'type' => 'string', 'required' => true, 'description' => 'Coin IDs (comma separated)'],
                    ['name' => 'vs_currencies', 'type' => 'string', 'required' => true, 'description' => 'Target currencies'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Market Chart',
                'method' => 'GET',
                'route' => '/coins/{id}/market_chart',
                'description' => 'Get historical market data',
                'parameters' => [
                    ['name' => 'id', 'type' => 'string', 'required' => true, 'description' => 'Coin ID'],
                    ['name' => 'vs_currency', 'type' => 'string', 'required' => true, 'description' => 'Target currency'],
                    ['name' => 'days', 'type' => 'integer', 'required' => true, 'description' => 'Data up to number of days ago'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Trending Coins',
                'method' => 'GET',
                'route' => '/search/trending',
                'description' => 'Get trending search coins',
                'parameters' => [],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
        ]);

        // News API
        $newsApi = Api::create([
            'name' => 'News API',
            'slug' => 'news-api',
            'description' => 'Locate articles and breaking news headlines from news sources and blogs.',
            'category' => 'News',
            'rapidapi_id' => 'newsapi',
            'synced_at' => now(),
        ]);

        $newsApi->endpoints()->createMany([
            [
                'name' => 'Top Headlines',
                'method' => 'GET',
                'route' => '/top-headlines',
                'description' => 'Get breaking news headlines',
                'parameters' => [
                    ['name' => 'country', 'type' => 'string', 'required' => false, 'description' => 'Country code'],
                    ['name' => 'category', 'type' => 'string', 'required' => false, 'description' => 'Category (business, sports, etc)'],
                    ['name' => 'q', 'type' => 'string', 'required' => false, 'description' => 'Search keywords'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Everything',
                'method' => 'GET',
                'route' => '/everything',
                'description' => 'Search through millions of articles',
                'parameters' => [
                    ['name' => 'q', 'type' => 'string', 'required' => true, 'description' => 'Search query'],
                    ['name' => 'from', 'type' => 'date', 'required' => false, 'description' => 'From date'],
                    ['name' => 'to', 'type' => 'date', 'required' => false, 'description' => 'To date'],
                    ['name' => 'sortBy', 'type' => 'string', 'required' => false, 'description' => 'Sort by (publishedAt, relevancy, popularity)'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
            [
                'name' => 'Sources',
                'method' => 'GET',
                'route' => '/sources',
                'description' => 'Get available news sources',
                'parameters' => [
                    ['name' => 'category', 'type' => 'string', 'required' => false, 'description' => 'Filter by category'],
                    ['name' => 'language', 'type' => 'string', 'required' => false, 'description' => 'Filter by language'],
                    ['name' => 'country', 'type' => 'string', 'required' => false, 'description' => 'Filter by country'],
                ],
                'headers' => [
                    ['name' => 'x-rapidapi-key', 'type' => 'string', 'required' => true, 'description' => 'API key'],
                ],
            ],
        ]);
    }
}
