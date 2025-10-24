<?php namespace Aero\Clouds\Models;

use Model;

/**
 * Setting Model
 *
 * Gestiona configuraciones globales del sitio (SEO, PWA, etc.)
 * con soporte para múltiples dominios.
 */
class Setting extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Aero\Clouds\Traits\DomainScoped;
    use \Aero\Clouds\Traits\LogsActivity;

    protected $table = 'aero_clouds_settings';

    protected $fillable = [
        'domain',
        // SEO
        'site_name',
        'site_description',
        'meta_keywords',
        'meta_author',
        'og_type',
        'twitter_card_type',
        'google_analytics_id',
        'google_tag_manager_id',
        // PWA
        'pwa_enabled',
        'pwa_name',
        'pwa_short_name',
        'pwa_description',
        'pwa_theme_color',
        'pwa_background_color',
        'pwa_show_install_prompt',
        'pwa_install_prompt_delay',
        // Email Notifications
        'email_notifications_enabled',
        'admin_emails',
    ];

    protected $casts = [
        'pwa_enabled' => 'boolean',
        'pwa_show_install_prompt' => 'boolean',
        'pwa_install_prompt_delay' => 'integer',
        'email_notifications_enabled' => 'boolean',
    ];

    public $rules = [
        'domain' => 'nullable|max:255',
        'site_name' => 'nullable|max:255',
        'site_description' => 'nullable|max:500',
        'meta_keywords' => 'nullable|max:500',
        'meta_author' => 'nullable|max:255',
        'og_type' => 'nullable|max:50',
        'twitter_card_type' => 'nullable|max:50',
        'google_analytics_id' => 'nullable|max:50',
        'google_tag_manager_id' => 'nullable|max:50',
        'pwa_enabled' => 'boolean',
        'pwa_name' => 'nullable|max:255',
        'pwa_short_name' => 'nullable|max:50',
        'pwa_description' => 'nullable|max:500',
        'pwa_theme_color' => 'nullable|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        'pwa_background_color' => 'nullable|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        'pwa_show_install_prompt' => 'boolean',
        'pwa_install_prompt_delay' => 'nullable|integer|min:0|max:30000',
        'email_notifications_enabled' => 'boolean',
        'admin_emails' => 'nullable',
    ];

    public $attachOne = [
        'og_image' => 'System\Models\File',
        'twitter_image' => 'System\Models\File',
        'pwa_icon_192' => 'System\Models\File',
        'pwa_icon_512' => 'System\Models\File',
        'favicon' => 'System\Models\File',
    ];

    /**
     * Obtener la configuración del dominio actual
     *
     * @return Setting|null
     */
    public static function getCurrentSettings()
    {
        return static::first();
    }

    /**
     * Obtener o crear la configuración del dominio actual
     *
     * @return Setting
     */
    public static function getOrCreateCurrentSettings()
    {
        $settings = static::first();

        if (!$settings) {
            $settings = new static();
            $settings->setDefaults();
            $settings->save();
        }

        return $settings;
    }

    /**
     * Establecer valores por defecto
     */
    public function setDefaults()
    {
        $this->site_name = $this->site_name ?? 'Clouds Hosting';
        $this->site_description = $this->site_description ?? 'Hosting profesional en Bolivia';
        $this->meta_author = $this->meta_author ?? 'Clouds';
        $this->og_type = $this->og_type ?? 'website';
        $this->twitter_card_type = $this->twitter_card_type ?? 'summary_large_image';

        $this->pwa_enabled = $this->pwa_enabled ?? true;
        $this->pwa_name = $this->pwa_name ?? $this->site_name ?? 'Clouds Hosting';
        $this->pwa_short_name = $this->pwa_short_name ?? 'Clouds';
        $this->pwa_description = $this->pwa_description ?? $this->site_description ?? 'Hosting profesional';
        $this->pwa_theme_color = $this->pwa_theme_color ?? '#0ea5e9';
        $this->pwa_background_color = $this->pwa_background_color ?? '#ffffff';
        $this->pwa_show_install_prompt = $this->pwa_show_install_prompt ?? true;
        $this->pwa_install_prompt_delay = $this->pwa_install_prompt_delay ?? 3000;
    }

    /**
     * Obtener un valor de configuración específico
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        $settings = static::getCurrentSettings();

        if (!$settings) {
            return $default;
        }

        return $settings->$key ?? $default;
    }

    /**
     * Establecer un valor de configuración
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set($key, $value)
    {
        $settings = static::getOrCreateCurrentSettings();
        $settings->$key = $value;

        return $settings->save();
    }
}
