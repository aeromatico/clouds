<?php namespace Aero\Clouds\Models;

use Model;

/**
 * Activity Log Model
 */
class ActivityLog extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'aero_clouds_activity_logs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'domain',
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'ip_address',
        'user_agent'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'description' => 'required|string',
        'event' => 'nullable|string|max:255'
    ];

    /**
     * @var array Attributes that should be cast
     */
    protected $casts = [
        'properties' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * @var array Relations
     */
    public $morphTo = [
        'subject' => [],
        'causer' => []
    ];

    /**
     * Scope to filter by log name
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by event
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by subject
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_type', get_class($subject))
                    ->where('subject_id', $subject->id);
    }

    /**
     * Scope to filter by causer
     */
    public function scopeCausedBy($query, $causer)
    {
        return $query->where('causer_type', get_class($causer))
                    ->where('causer_id', $causer->id);
    }
}
