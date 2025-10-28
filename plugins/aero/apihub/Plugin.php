<?php namespace Aero\ApiHub;

use Backend;
use System\Classes\PluginBase;
use Aero\ApiHub\Models\Settings;

/**
 * ApiHub Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Api Hub',
            'description' => 'Comprehensive API catalog with RapidAPI integration, comparison, documentation, and analytics',
            'author'      => 'Aero',
            'icon'        => 'icon-cloud',
            'homepage'    => 'https://clouds.com.bo'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        // Register console commands
        $this->registerConsoleCommand('apihub:sync', \Aero\ApiHub\Console\SyncApis::class);
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        // Register cache configuration
        \Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            \Cache::extend('apihub', function ($app) {
                return \Cache::repository(
                    new \Illuminate\Cache\Repository(
                        new \Illuminate\Cache\RedisStore(
                            $app['redis'],
                            'apihub',
                            1 // Redis DB 1 for cache
                        )
                    )
                );
            });
        });
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return [
            'aero.apihub.access_apis' => [
                'tab'   => 'Api Hub',
                'label' => 'Manage APIs',
            ],
            'aero.apihub.access_scanner' => [
                'tab'   => 'Api Hub',
                'label' => 'Import APIs from APIs.guru',
            ],
            'aero.apihub.access_analytics' => [
                'tab'   => 'Api Hub',
                'label' => 'View Analytics',
            ],
            'aero.apihub.access_settings' => [
                'tab'   => 'Api Hub',
                'label' => 'Manage Settings',
            ],
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return [
            'apihub' => [
                'label'       => 'Api Hub',
                'url'         => Backend::url('aero/apihub/apis'),
                'icon'        => 'icon-cloud',
                'permissions' => ['aero.apihub.*'],
                'order'       => 500,
                'sideMenu' => [
                    'apis' => [
                        'label'       => 'APIs',
                        'icon'        => 'icon-list',
                        'url'         => Backend::url('aero/apihub/apis'),
                        'permissions' => ['aero.apihub.access_apis'],
                    ],
                    'scanner' => [
                        'label'       => 'Import',
                        'icon'        => 'icon-download',
                        'url'         => Backend::url('aero/apihub/scanner'),
                        'permissions' => ['aero.apihub.access_scanner'],
                    ],
                    'analytics' => [
                        'label'       => 'Analytics',
                        'icon'        => 'icon-bar-chart',
                        'url'         => Backend::url('aero/apihub/analytics'),
                        'permissions' => ['aero.apihub.access_analytics'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Registers any backend settings used by this plugin.
     */
    public function registerSettings(): array
    {
        return [
            'settings' => [
                'label'       => 'Api Hub Settings',
                'description' => 'Configure APIs.guru integration and cache settings',
                'category'    => 'Api Hub',
                'icon'        => 'icon-cog',
                'class'       => Settings::class,
                'order'       => 500,
                'permissions' => ['aero.apihub.access_settings'],
            ],
        ];
    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return [
            \Aero\ApiHub\Components\ApiCatalog::class => 'apiCatalog',
            \Aero\ApiHub\Components\EndpointList::class => 'endpointList',
        ];
    }

    /**
     * Registers scheduled tasks for this plugin.
     */
    public function registerSchedule($schedule): void
    {
        $settings = Settings::instance();

        if ($settings->auto_sync) {
            $frequency = $settings->sync_frequency ?? 'daily';

            if ($frequency === 'daily') {
                $schedule->command('apihub:sync')->daily();
            } elseif ($frequency === 'weekly') {
                $schedule->command('apihub:sync')->weekly();
            }
        }
    }
}
