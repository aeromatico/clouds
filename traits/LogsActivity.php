<?php namespace Aero\Clouds\Traits;

use Aero\Clouds\Models\ActivityLog;
use BackendAuth;

/**
 * Logs Activity Trait
 *
 * Automatically logs model events (create, update, delete)
 */
trait LogsActivity
{
    /**
     * Boot the trait
     */
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created', 'Created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', 'Updated', [
                'old' => $model->getOriginal(),
                'attributes' => $model->getAttributes()
            ]);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', 'Deleted');
        });
    }

    /**
     * Log an activity
     */
    public function logActivity($event, $description, $properties = [])
    {
        $causer = BackendAuth::getUser();

        $properties = array_merge($properties, [
            'attributes' => $this->attributesToArray()
        ]);

        ActivityLog::create([
            'log_name' => $this->getLogName(),
            'description' => $this->formatDescription($description),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
            'properties' => $properties,
            'event' => $event,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Get the log name for this model
     */
    protected function getLogName()
    {
        return property_exists($this, 'logName')
            ? $this->logName
            : strtolower(class_basename($this));
    }

    /**
     * Format the description
     */
    protected function formatDescription($description)
    {
        $modelName = class_basename($this);
        $identifier = $this->getLogIdentifier();

        return "{$description} {$modelName}" . ($identifier ? " ({$identifier})" : '');
    }

    /**
     * Get the identifier for logging
     */
    protected function getLogIdentifier()
    {
        if (property_exists($this, 'logIdentifier')) {
            $attribute = $this->logIdentifier;
            return $this->$attribute ?? $this->id;
        }

        // Try common identifier fields
        foreach (['name', 'title', 'email', 'number'] as $field) {
            if (isset($this->$field)) {
                return $this->$field;
            }
        }

        return $this->id;
    }

    /**
     * Get activity logs for this model
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    /**
     * Log a custom event
     */
    public function log($event, $description, $properties = [])
    {
        $this->logActivity($event, $description, $properties);
        return $this;
    }
}
