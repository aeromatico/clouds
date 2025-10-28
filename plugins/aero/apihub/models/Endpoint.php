<?php namespace Aero\ApiHub\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use Cache;

/**
 * Endpoint Model
 */
class Endpoint extends Model
{
    use Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'aero_apihub_endpoints';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'api_id',
        'name',
        'method',
        'route',
        'description',
        'parameters',
        'headers',
        'response_example',
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'api_id' => 'required|exists:aero_apihub_apis,id',
        'name' => 'required|string|max:255',
        'method' => 'required|in:GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS',
        'route' => 'required|string|max:500',
    ];

    /**
     * @var array Relationships
     */
    public $belongsTo = [
        'api' => [
            Api::class,
        ],
    ];

    /**
     * After save event - invalidate cache
     */
    public function afterSave()
    {
        Cache::forget("apihub:endpoints:{$this->api_id}");
        Cache::forget('apihub:stats');
        Cache::tags('apihub')->flush();
    }

    /**
     * After delete event - invalidate cache
     */
    public function afterDelete()
    {
        Cache::forget("apihub:endpoints:{$this->api_id}");
        Cache::forget('apihub:stats');
        Cache::tags('apihub')->flush();
    }

    /**
     * Scope: Filter by method
     */
    public function scopeMethod($query, $method)
    {
        return $query->where('method', strtoupper($method));
    }

    /**
     * Scope: Search endpoints
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('route', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get method badge color
     */
    public function getMethodColorAttribute()
    {
        return match ($this->method) {
            'GET' => 'success',
            'POST' => 'primary',
            'PUT', 'PATCH' => 'warning',
            'DELETE' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get common endpoint patterns across APIs
     */
    public static function getCommonPatterns()
    {
        return Cache::remember('apihub:common_patterns', 3600, function () {
            return static::selectRaw('route, COUNT(DISTINCT api_id) as api_count, COUNT(*) as endpoint_count')
                ->groupBy('route')
                ->having('api_count', '>', 1)
                ->orderBy('api_count', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'route' => $item->route,
                        'apis' => $item->api_count,
                        'endpoints' => $item->endpoint_count,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get method distribution statistics
     */
    public static function getMethodStats()
    {
        return Cache::remember('apihub:method_stats', 300, function () {
            return static::selectRaw('method, COUNT(*) as count')
                ->groupBy('method')
                ->orderBy('count', 'desc')
                ->pluck('count', 'method')
                ->toArray();
        });
    }

    /**
     * Accessor for JSON fields (parameters, headers, response_example)
     * Convert array to JSON string for CodeEditor widget
     */
    protected function formatJsonField($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            return $value;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * Mutator for JSON fields
     */
    protected function setJsonField($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded);
            }
            return $value;
        } elseif (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        return $value;
    }

    public function getParametersAttribute($value)
    {
        return $this->formatJsonField($value);
    }

    public function setParametersAttribute($value)
    {
        $this->attributes['parameters'] = $this->setJsonField($value);
    }

    public function getHeadersAttribute($value)
    {
        return $this->formatJsonField($value);
    }

    public function setHeadersAttribute($value)
    {
        $this->attributes['headers'] = $this->setJsonField($value);
    }

    public function getResponseExampleAttribute($value)
    {
        return $this->formatJsonField($value);
    }

    public function setResponseExampleAttribute($value)
    {
        $this->attributes['response_example'] = $this->setJsonField($value);
    }
}
