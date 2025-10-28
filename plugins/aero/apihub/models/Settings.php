<?php namespace Aero\ApiHub\Models;

use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    public $implement = [\System\Behaviors\SettingsModel::class];

    // A unique code
    public $settingsCode = 'aero_apihub_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
        'cache_ttl' => 'required|integer|min:60|max:86400',
        'apis_list_cache_ttl' => 'required|integer|min:3600|max:604800',
        'default_import_limit' => 'required|integer|min:5|max:100',
        'apify_api_token' => 'nullable|string',
        'import_source' => 'in:apis_guru,apify,manual',
        'auto_sync' => 'boolean',
        'sync_frequency' => 'in:daily,weekly',
    ];

    /**
     * Get encrypted Apify token attribute
     */
    public function getApifyApiTokenAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Set encrypted Apify token
     */
    public function setApifyApiTokenAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['apify_api_token'] = null;
            return;
        }

        try {
            decrypt($value);
            $this->attributes['apify_api_token'] = $value;
        } catch (\Exception $e) {
            $this->attributes['apify_api_token'] = encrypt($value);
        }
    }

    /**
     * Get decrypted Apify token for use
     */
    public static function getApifyToken()
    {
        $settings = static::instance();
        if (empty($settings->apify_api_token)) {
            return null;
        }

        try {
            return decrypt($settings->apify_api_token);
        } catch (\Exception $e) {
            return $settings->apify_api_token;
        }
    }
}
