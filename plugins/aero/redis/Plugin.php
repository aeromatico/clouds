<?php

namespace Aero\Redis;

use System\Classes\PluginBase;
use System\Classes\PluginManager;

/**
 * Redis Monitor Plugin for OctoberCMS
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Redis Monitor',
            'description' => 'Monitor y gestión de Redis para Master Theme',
            'author'      => 'aero',
            'icon'        => 'icon-database',
            'homepage'    => 'https://clouds.com.bo'
        ];
    }

    public function register()
    {
        // Register routes
        $this->app['router']->group(['prefix' => 'api/redis'], function ($router) {
            $router->get('stats', 'Aero\Redis\Controllers\RedisController@getStats');
            $router->post('flush/{db}', 'Aero\Redis\Controllers\RedisController@flushDatabase');
            $router->get('keys/{db}', 'Aero\Redis\Controllers\RedisController@getKeys');
        });
    }

    public function boot()
    {
        // Boot plugin
    }
}