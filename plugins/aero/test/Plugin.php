<?php namespace Aero\Test;

use System\Classes\PluginBase;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {
        return [
            'test' => [
                'label' => 'Test',
                'url' => \Backend::url('aero/test/students'),
                'icon' => 'icon-graduation-cap',
                'order' => 500,
                'sideMenu' => [
                    'students' => [
                        'label' => 'Students',
                        'icon' => 'icon-user',
                        'url' => \Backend::url('aero/test/students'),
                    ],
                    'curses' => [
                        'label' => 'Curses',
                        'icon' => 'icon-book',
                        'url' => \Backend::url('aero/test/curses'),
                    ]
                ]
            ]
        ];
    }
}
