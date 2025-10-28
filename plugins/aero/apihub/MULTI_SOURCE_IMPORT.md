# Multi-Source API Import System ✅

**Version:** 1.0.2
**Date:** October 26, 2025
**Status:** Production Ready

## Overview

El plugin Api Hub ahora soporta **3 fuentes de importación:**

1. **APIs.guru** - Free, 2500+ APIs con OpenAPI specs completas
2. **Apify** - Scraping de RapidAPI para APIs específicas (paid)
3. **Manual** - Entrada manual de APIs

## Architecture

```
classes/
├── ApiGuruClient.php   (APIs.guru REST API)
├── ApifyClient.php     (Apify actor scraper)
└── ApiImporter.php     (Orchestrator)

jobs/
├── ImportApiGuruJob.php
└── ImportApifyJob.php

controllers/
└── Scanner.php         (Multi-source UI with tabs)
```

## Database Schema

### New Field: `source`

```sql
ALTER TABLE aero_apihub_apis
ADD COLUMN source ENUM('apis_guru', 'apify', 'manual', 'legacy')
DEFAULT 'manual'
AFTER category;
```

**Values:**
- `apis_guru` - Imported from APIs.guru
- `apify` - Scraped from RapidAPI via Apify
- `manual` - Created manually
- `legacy` - Existing APIs before migration

## 1. APIs.guru Import

### Features
✅ **FREE** - No API key required
✅ **2500+ APIs** with complete OpenAPI specs
✅ **Auto-parsing** of endpoints, parameters, responses
✅ **Redis caching** (24h for list, 1h for specs)
✅ **Fast** - Search in-memory, import in ~3-10s

### Usage

**Backend → Api Hub → Import → Tab "APIs.guru"**

```php
// Search APIs
$client = new ApiGuruClient();
$results = $client->searchApis('github', 20);

// Import
ApiImporter::importFromApisGuru(
    provider: 'github.com',
    version: '1.1.4',
    title: 'GitHub v3 REST API',
    category: 'collaboration'
);
```

**Bulk Import by Category:**
```bash
# Financial APIs (top 5)
POST /backend/aero/apihub/scanner/onImportPopular
{category: 'financial', limit: 5}
```

## 2. Apify Import (RapidAPI Scraper)

### Features
✅ **Search specific APIs** not in APIs.guru
✅ **Scrapes RapidAPI** marketplace
✅ **Auto-imports** with endpoints
⚠️ **PAID** - Requires Apify account (~$0.01-0.10 per search)
⏱️ **Slow** - Takes ~30-60 seconds per search

### Configuration

**1. Get Apify Token:**
- Go to https://console.apify.com/account/integrations
- Copy your API token
- Format: `apify_api_xxxxxxxxxxxxx`

**2. Configure in Settings:**
```
Backend → Settings → Api Hub Settings → Apify tab
- Apify API Token: apify_api_YOUR_TOKEN_HERE
- Save (will be encrypted)
```

### Usage

**Backend → Api Hub → Import → Tab "RapidAPI via Apify"**

```php
// Search & Import
ApiImporter::importFromApify(
    searchTerm: 'weather api',
    maxItems: 10,
    queue: true // Always true for Apify
);
```

**How it works:**
1. User searches for "weather api"
2. Apify actor starts scraping RapidAPI (~30-60s)
3. Results are parsed and imported automatically
4. APIs appear in the list with `source=apify`

### Apify Client Details

```php
// Start scraper
POST https://api.apify.com/v2/acts/yourapiservice~rapidapi-scraper/runs
Headers: {Authorization: Bearer APIFY_TOKEN}
Body: {
    search_term: "weather api",
    maxItemsPerCategory: 10
}

// Poll for completion
GET /runs/{runId} → status: "SUCCEEDED"

// Get results
GET /runs/{runId}/dataset/items
```

**Parsed Data:**
```json
{
  "name": "OpenWeather API",
  "description": "Weather data...",
  "category": "Weather",
  "pricing": {...},
  "endpoints": [
    {
      "name": "Get Current Weather",
      "method": "GET",
      "route": "/weather",
      "parameters": [...],
      "headers": [...]
    }
  ]
}
```

## 3. Manual Import

### Features
✅ **Simple form** for manual entry
✅ **Perfect for** custom/internal APIs
✅ **Add endpoints later** via edit page

### Usage

**Backend → Api Hub → Import → Tab "Manual Entry"**

```php
ApiImporter::createManual([
    'name' => 'My Custom API',
    'category' => 'Internal',
    'description' => 'Internal company API'
]);
```

## Backend UI - Scanner with Tabs

```
┌─────────────────────────────────────┐
│ Import APIs (Multi-Source)          │
├─────────────────────────────────────┤
│ Total: 3 │ Guru: 0 │ Apify: 0 │... │
├─────────────────────────────────────┤
│ [APIs.guru] [RapidAPI] [Manual]     │
├─────────────────────────────────────┤
│                                     │
│  Tab Content Here                   │
│                                     │
└─────────────────────────────────────┘
```

**Tab 1: APIs.guru**
- Search box
- Quick import by category buttons
- Results table with "Import" buttons

**Tab 2: RapidAPI via Apify**
- Search box
- Max items selector (5, 10, 20, 50)
- Connection status
- Cost warning

**Tab 3: Manual Entry**
- Simple form: name, category, description
- "Create API" button → redirects to edit page

## Settings Configuration

```yaml
General Tab:
  import_source:
    - apis_guru (default)
    - apify
    - manual

APIs.guru Tab:
  - Info panel (no config needed)

Apify Tab:
  apify_api_token: (encrypted)
  - Info panel with costs & instructions

Cache Tab:
  cache_ttl: 3600
  apis_list_cache_ttl: 86400

Import Tab:
  default_import_limit: 20
  auto_sync: false
  sync_frequency: daily
```

## API Model Updates

### New Attributes

```php
// Fillable
'source' => 'apis_guru|apify|manual|legacy'

// Accessors
$api->source_color  // 'success', 'primary', 'warning', 'secondary'
$api->source_name   // 'APIs.guru', 'Apify', 'Manual', 'Legacy'

// Scope
Api::source('apify')->get()
```

### List View

```
ID | Name | Category | Source | Endpoints | Last Sync | Created
1  | GitHub | Collab | [APIs.guru] | 845 | 2h ago | Today
2  | Weather | Weather | [Apify] | 12 | 30m ago | Today
3  | Custom | Internal | [Manual] | 5 | - | Yesterday
```

## Queue Jobs

### ImportApiGuruJob
- **Timeout:** 120s (2 minutes)
- **Tries:** 3
- **Lock:** `apihub:import_lock:{provider}:{version}`
- **Process:**
  1. Fetch OpenAPI spec from APIs.guru
  2. Parse with OpenApiParser
  3. Create API with `source=apis_guru`
  4. Create all endpoints

### ImportApifyJob
- **Timeout:** 300s (5 minutes)
- **Tries:** 2
- **Lock:** `apihub:apify_lock:{search_term}`
- **Process:**
  1. Start Apify actor run
  2. Poll for completion (~30-60s)
  3. Get dataset items
  4. Parse results
  5. Import each API with `source=apify`

## Cost Analysis

| Source | Cost | Speed | Quality | Quantity |
|--------|------|-------|---------|----------|
| **APIs.guru** | FREE | Fast (~5s) | Excellent | 2500+ |
| **Apify** | ~$0.01-0.10 | Slow (~60s) | Good | Unlimited |
| **Manual** | FREE | Instant | Varies | Manual |

**Recommendations:**
1. **Always search APIs.guru first** (free + fast)
2. **Use Apify for specific APIs** not in APIs.guru
3. **Manual entry** for internal/custom APIs

## Redis Cache Keys

```
# APIs.guru
apihub:apis_list → 24h
apihub:spec:{provider}:{version} → 1h

# Apify
apihub:apify_lock:{search_term} → 10min

# Import locks
apihub:import_lock:{provider}:{version} → 5min
```

## Statistics

```php
ApiImporter::getSourceStats()
// Returns:
[
  'apis_guru' => 15,
  'apify' => 3,
  'manual' => 5,
  'legacy' => 2,
  'total' => 25
]
```

## Testing

### Test APIs.guru Connection
```bash
$client = new ApiGuruClient();
$client->testConnection(); // true/false
```

### Test Apify Connection
```bash
$client = new ApifyClient();
$client->testConnection(); // true/false
```

### Test Import
```bash
# APIs.guru
php artisan tinker
>>> ApiImporter::importFromApisGuru('github.com', '1.1.4', 'GitHub API', 'developer_tools')

# Apify
>>> ApiImporter::importFromApify('weather api', 5, true)
```

## Error Handling

**APIs.guru Errors:**
- Connection failed → Show warning, allow offline
- Spec fetch failed → Log error, skip import
- Parse failed → Log error, import without endpoints

**Apify Errors:**
- Token not configured → Show error, disable tab
- Actor timeout → Retry once, then fail
- No results → Show warning, no import

**Manual Errors:**
- Name empty → Show error
- Slug exists → Show error

## Migration Notes

### Existing APIs
- Marked as `source=legacy`
- Can be updated to proper source later
- Still fully functional

### Backwards Compatibility
- ✅ All existing functionality preserved
- ✅ Old RapidApiClient still exists (unused)
- ✅ Database structure backward compatible

## Future Enhancements

1. **More sources:** Swagger Hub, Postman API Network
2. **Batch operations:** Select multiple APIs to import
3. **Source switching:** Re-import from different source
4. **Cost tracking:** Track Apify usage & costs
5. **Auto-categorization:** ML-based category detection

## Conclusion

El sistema multi-source proporciona:
- ✅ **Flexibilidad** - 3 opciones de importación
- ✅ **Economía** - Gratis para la mayoría de casos
- ✅ **Escalabilidad** - Scraping bajo demanda
- ✅ **Simplicidad** - UI con tabs intuitiva

**Status:** ✅ Production Ready
