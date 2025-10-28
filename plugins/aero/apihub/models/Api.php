<?php namespace Aero\ApiHub\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use Illuminate\Support\Str;
use Cache;

/**
 * Api Model
 */
class Api extends Model
{
    use Validation;

    /**
     * @var string table associated with the model
     */
    public $table = 'aero_apihub_apis';

    /**
     * @var array guarded attributes aren't mass assignable
     */
    protected $guarded = ['*'];

    /**
     * @var array fillable attributes are mass assignable
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'source',
        'rapidapi_id',
        'rapidapi_version_id',
        'raw_data',
        'synced_at',
    ];

    /**
     * @var array rules for validation
     */
    public $rules = [
        'name' => 'required|string|max:255',
        'slug' => 'required|string|max:255|unique:aero_apihub_apis',
        'category' => 'nullable|string|max:100',
        'source' => 'required|in:apis_guru,apify,manual,legacy',
        'rapidapi_id' => 'nullable|string|max:255',
    ];

    /**
     * @var array attributes to be cast
     */
    protected $casts = [
        'synced_at' => 'datetime',
    ];

    /**
     * @var array dates attributes
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'synced_at',
    ];

    /**
     * @var array Relationships
     */
    public $hasMany = [
        'endpoints' => [
            Endpoint::class,
            'delete' => true, // Cascade delete
        ],
    ];

    /**
     * Before create event
     */
    public function beforeCreate()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Before update event
     */
    public function beforeUpdate()
    {
        if ($this->isDirty('name') && empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * After save event - invalidate cache
     */
    public function afterSave()
    {
        $this->invalidateCache();
    }

    /**
     * After delete event - invalidate cache
     */
    public function afterDelete()
    {
        $this->invalidateCache();
    }

    /**
     * Invalidate cache for this API
     */
    public function invalidateCache()
    {
        Cache::forget("apihub:api:{$this->slug}");
        Cache::forget("apihub:endpoints:{$this->id}");
        Cache::forget('apihub:stats');
        Cache::tags('apihub')->flush();
    }

    /**
     * Get API with cache
     */
    public static function getCached($slug)
    {
        return Cache::remember("apihub:api:{$slug}", 3600, function () use ($slug) {
            return static::where('slug', $slug)->with('endpoints')->first();
        });
    }

    /**
     * Scope: Filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by search term
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('category', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Scope: Recently synced
     */
    public function scopeRecentlySynced($query, $days = 7)
    {
        return $query->where('synced_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Needs sync (older than X days)
     */
    public function scopeNeedsSync($query, $days = 7)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('synced_at')
              ->orWhere('synced_at', '<', now()->subDays($days));
        });
    }

    /**
     * Get endpoints count attribute
     */
    public function getEndpointsCountAttribute()
    {
        return $this->endpoints()->count();
    }

    /**
     * Accessor for raw_data field (for CodeEditor widget)
     * Convert array to JSON string for display, or format JSON string
     */
    public function getRawDataAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's a string, try to decode and re-encode for pretty printing
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            return $value;
        }

        // If it's an array or object, encode it
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * Mutator for raw_data field
     * Keep it as JSON string in database
     */
    public function setRawDataAttribute($value)
    {
        if (is_string($value)) {
            // If it's already a string, decode to validate and re-encode
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['raw_data'] = json_encode($decoded);
            } else {
                $this->attributes['raw_data'] = $value;
            }
        } elseif (is_array($value) || is_object($value)) {
            $this->attributes['raw_data'] = json_encode($value);
        } else {
            $this->attributes['raw_data'] = $value;
        }
    }

    /**
     * Get all unique categories
     */
    public static function getAllCategories()
    {
        return static::distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    /**
     * Scope: Filter by source
     */
    public function scopeSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Get source badge color
     */
    public function getSourceColorAttribute()
    {
        return match ($this->source) {
            'apis_guru' => 'success',
            'apify' => 'primary',
            'manual' => 'warning',
            'legacy' => 'secondary',
            default => 'default',
        };
    }

    /**
     * Get source display name
     */
    public function getSourceNameAttribute()
    {
        return match ($this->source) {
            'apis_guru' => 'APIs.guru',
            'apify' => 'Apify',
            'manual' => 'Manual',
            'legacy' => 'Legacy',
            default => 'Unknown',
        };
    }

    /**
     * Get statistics
     */
    public static function getStats()
    {
        return Cache::remember('apihub:stats', 300, function () {
            return [
                'total_apis' => static::count(),
                'total_endpoints' => Endpoint::count(),
                'by_category' => static::selectRaw('category, COUNT(*) as count')
                    ->whereNotNull('category')
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->pluck('count', 'category')
                    ->toArray(),
                'recently_synced' => static::recentlySynced()->count(),
                'needs_sync' => static::needsSync()->count(),
            ];
        });
    }
}
