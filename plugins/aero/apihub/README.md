# Api Hub Plugin for OctoberCMS 4

Comprehensive API catalog plugin with RapidAPI integration, Redis caching, and full management features.

## Features

- **RapidAPI Integration**: Search and import APIs directly from RapidAPI using GraphQL
- **API Catalog**: Searchable catalog with filtering and pagination
- **Endpoint Management**: Full CRUD operations for APIs and endpoints
- **Analytics Dashboard**: Visual statistics with Chart.js
- **Redis Caching**: Aggressive caching strategy for performance
- **Queue Support**: Background jobs for importing and syncing
- **Console Commands**: CLI tools for automation
- **Frontend Components**: Ready-to-use components for displaying APIs

## Installation

### 1. Plugin Installation

Copy the plugin to your OctoberCMS installation:

```bash
cp -r plugins/aero/apihub /path/to/october/plugins/aero/
```

### 2. Database Migration

Run the plugin migrations:

```bash
cd /path/to/october
php artisan october:migrate
```

This will create the following tables:
- `aero_apihub_apis` - API records
- `aero_apihub_endpoints` - Endpoint records

### 3. Seed Example Data (Optional)

Load example APIs:

```bash
php artisan db:seed --class="Aero\ApiHub\Updates\SeedExampleApis"
```

### 4. Redis Configuration

Ensure Redis is configured in your `.env`:

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 5. RapidAPI Configuration

1. Get your RapidAPI key from https://rapidapi.com
2. Go to **Settings → Api Hub Settings** in the backend
3. Enter your RapidAPI key (it will be encrypted)
4. Configure cache TTL (default: 3600 seconds)
5. Optionally enable auto-sync

## Backend Usage

### API Management

Navigate to **Api Hub → APIs** to:
- View all imported APIs
- Create APIs manually
- Edit API details
- Manage endpoints
- Sync from RapidAPI
- Delete APIs

### Import from RapidAPI

Navigate to **Api Hub → Import** to:
- Search RapidAPI catalog
- Import individual APIs
- Bulk import popular APIs by category
- Queue imports for background processing

### Analytics

Navigate to **Api Hub → Analytics** to view:
- Total APIs and endpoints
- APIs by category (pie chart)
- Endpoints by method (bar chart)
- Common endpoint patterns
- Sync statistics

## Console Commands

### Sync APIs

Sync APIs from RapidAPI:

```bash
# Sync APIs that need updating (default: older than 7 days)
php artisan apihub:sync

# Sync all APIs
php artisan apihub:sync --all

# Sync specific API by ID
php artisan apihub:sync --id=1

# Queue sync jobs instead of running immediately
php artisan apihub:sync --all --queue

# Force sync even if recently synced
php artisan apihub:sync --all --force
```

### Schedule Automatic Sync

The plugin supports automatic scheduled syncing. Configure in **Settings → Api Hub Settings**:
- Enable Auto Sync
- Choose frequency (daily or weekly)

The scheduler will automatically run the sync command.

## Frontend Components

### ApiCatalog Component

Display a searchable catalog of APIs:

```twig
[apiCatalog]
perPage = 12
showSearch = 1
showFilters = 1
==

{% component 'apiCatalog' %}
```

**Properties:**
- `perPage` - Number of APIs per page (default: 12)
- `category` - Filter by specific category
- `showSearch` - Show search box (default: true)
- `showFilters` - Show category filters (default: true)

### EndpointList Component

Display endpoints for a specific API:

```twig
[endpointList]
apiSlug = "{{ :slug }}"
groupByMethod = 1
showParameters = 1
==

{% component 'endpointList' %}
```

**Properties:**
- `apiSlug` - API slug (can use URL parameter)
- `groupByMethod` - Group endpoints by HTTP method (default: true)
- `showParameters` - Show parameter details (default: true)

## Cache Strategy

The plugin uses an aggressive Redis caching strategy:

### Cache Keys

- `apihub:api:{slug}` - Cached API with endpoints (TTL: 1 hour)
- `apihub:endpoints:{api_id}` - Cached endpoints for API (TTL: 1 hour)
- `apihub:stats` - Analytics statistics (TTL: 5 minutes)
- `apihub:rapidapi:{hash}` - RapidAPI responses (TTL: 1 hour)
- `apihub:sync:{api_id}` - Sync lock (TTL: 5 minutes)
- `apihub:import:{api_id}` - Import lock (TTL: 5 minutes)

### Cache Invalidation

Cache is automatically invalidated when:
- API is created, updated, or deleted
- Endpoints are modified
- Sync operation completes

### Manual Cache Clearing

Clear all cache from backend:
- Navigate to **Api Hub → APIs**
- Click **Clear Cache** button

Or via console:

```bash
php artisan cache:clear
```

## Queue Configuration

The plugin uses Laravel queues for background processing:

### Start Queue Worker

```bash
php artisan queue:work redis --queue=default
```

### Queued Jobs

- `ImportApiJob` - Import API from RapidAPI
- `SyncApiJob` - Sync existing API

### Job Features

- **Locking**: Prevents concurrent imports/syncs
- **Retry Logic**: Automatic retry with exponential backoff
- **Error Handling**: Comprehensive logging
- **Max Attempts**: 3 retries before failing

## API Structure

### Api Model

```php
$api = Api::create([
    'name' => 'Weather API',
    'slug' => 'weather-api',
    'description' => 'Weather data API',
    'category' => 'Weather',
    'rapidapi_id' => 'openweathermap',
    'rapidapi_version_id' => 'v1',
    'raw_data' => [], // Full RapidAPI response
    'synced_at' => now(),
]);
```

### Endpoint Model

```php
$endpoint = Endpoint::create([
    'api_id' => 1,
    'name' => 'Get Weather',
    'method' => 'GET',
    'route' => '/weather',
    'description' => 'Get current weather',
    'parameters' => [],
    'headers' => [],
    'response_example' => [],
]);
```

## Model Scopes

### Api Scopes

```php
// Filter by category
Api::category('Weather')->get();

// Search APIs
Api::search('weather')->get();

// Recently synced (last 7 days)
Api::recentlySynced()->get();

// Needs sync (older than 7 days)
Api::needsSync()->get();
```

### Endpoint Scopes

```php
// Filter by method
Endpoint::method('GET')->get();

// Search endpoints
Endpoint::search('weather')->get();
```

## Permissions

The plugin defines the following permissions:

- `aero.apihub.access_apis` - Manage APIs
- `aero.apihub.access_scanner` - Import from RapidAPI
- `aero.apihub.access_analytics` - View analytics
- `aero.apihub.access_settings` - Manage settings

## RapidAPI GraphQL Queries

### Search APIs

```graphql
query SearchApis($term: String!, $limit: Int!) {
    apis(
        where: { visibility: PUBLIC, name: [$term] },
        first: $limit
    ) {
        edges {
            node {
                id
                name
                description
                category { name }
                currentVersion { id }
            }
        }
    }
}
```

### Get Endpoints

```graphql
query GetEndpoints($versionId: ID!) {
    apiVersion(id: $versionId) {
        endpoints {
            name
            route
            method
            description
            parameters { name type required description }
            headers { name type required description }
        }
    }
}
```

## Troubleshooting

### RapidAPI Connection Failed

Check:
1. API key is correct in settings
2. Redis is running
3. Internet connection is available

### Cache Issues

Clear cache:
```bash
php artisan cache:clear
redis-cli FLUSHDB
```

### Queue Not Processing

Ensure queue worker is running:
```bash
php artisan queue:work redis --queue=default --tries=3
```

### Permission Denied

Check:
1. User has required permissions
2. Files have correct ownership (775)

## Performance Tips

1. **Enable Auto-Sync**: Keep APIs updated automatically
2. **Use Queue**: Always queue imports for better performance
3. **Monitor Cache**: Check cache hit rates in analytics
4. **Optimize Queries**: Use scopes and eager loading

## Development

### File Structure

```
plugins/aero/apihub/
├── Plugin.php              # Plugin registration
├── classes/
│   └── RapidApiClient.php  # RapidAPI GraphQL client
├── components/
│   ├── ApiCatalog.php      # Catalog component
│   └── EndpointList.php    # Endpoint list component
├── console/
│   └── SyncApis.php        # Sync command
├── controllers/
│   ├── Apis.php            # API CRUD controller
│   ├── Scanner.php         # Import controller
│   └── Analytics.php       # Analytics controller
├── jobs/
│   ├── ImportApiJob.php    # Import background job
│   └── SyncApiJob.php      # Sync background job
├── models/
│   ├── Api.php             # API model
│   ├── Endpoint.php        # Endpoint model
│   └── Settings.php        # Settings model
└── updates/
    ├── version.yaml
    ├── create_apis_table.php
    ├── create_endpoints_table.php
    └── seed_example_apis.php
```

## Support

For issues or questions:
- GitHub: https://github.com/aeromatico/apihub
- Email: support@clouds.com.bo

## License

MIT License

## Credits

Developed by Aero for Clouds Hosting Platform
