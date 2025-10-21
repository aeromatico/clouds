<?php namespace Aero\Connector;

use System\Classes\PluginBase;
use Backend;

class Plugin extends PluginBase
{

    public function registerComponents()
    {
        return [];
    }

    public function registerSettings()
    {
        return [];
    }

    public function registerNavigation()
    {
        return [
            'aero_connector' => [
                'label' => 'Connector',
                'url' => Backend::url('aero/connector/services'),
                'icon' => 'icon-plug',
                'permissions' => ['aero.connector.access'],
                'sideMenu' => [
                    'services' => [
                        'label' => 'Services',
                        'url' => Backend::url('aero/connector/services'),
                        'icon' => 'icon-server',
                        'permissions' => ['aero.connector.services']
                    ],
                    'plans' => [
                        'label' => 'Plans',
                        'url' => Backend::url('aero/connector/plans'),
                        'icon' => 'icon-list',
                        'permissions' => ['aero.connector.plans']
                    ]
                ]
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'aero.connector.access' => [
                'tab' => 'Connector',
                'label' => 'Access Connector'
            ],
            'aero.connector.services' => [
                'tab' => 'Connector',
                'label' => 'Manage Services'
            ],
            'aero.connector.plans' => [
                'tab' => 'Connector',
                'label' => 'Manage Plans'
            ]
        ];
    }
}