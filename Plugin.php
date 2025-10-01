<?php namespace Aero\Clouds;

use System\Classes\PluginBase;
use Backend;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Clouds Hosting Manager',
            'description' => 'Complete hosting management system for clouds.com.bo',
            'author'      => 'Aero',
            'icon'        => 'icon-cloud',
            'homepage'    => 'https://clouds.com.bo'
        ];
    }

    public function registerNavigation()
    {
        return [
            'clouds' => [
                'label'       => 'Hosting Manager',
                'url'         => Backend::url('aero/clouds/services'),
                'icon'        => 'icon-cloud',
                'iconSvg'     => 'plugins/aero/clouds/assets/images/icon.svg',
                // 'permissions' => ['aero.clouds.access_services'],
                'order'       => 500,

                'sideMenu' => [
                    'services' => [
                        'label'       => 'Services',
                        'icon'        => 'icon-cogs',
                        'url'         => Backend::url('aero/clouds/services'),
                        // 'permissions' => ['aero.clouds.access_services']
                    ],
                    'plans' => [
                        'label'       => 'Plans',
                        'icon'        => 'icon-list',
                        'url'         => Backend::url('aero/clouds/plans'),
                        // 'permissions' => ['aero.clouds.access_plans']
                    ],
                    'features' => [
                        'label'       => 'Features',
                        'icon'        => 'icon-star',
                        'url'         => Backend::url('aero/clouds/features'),
                        // 'permissions' => ['aero.clouds.access_features']
                    ],
                    'addons' => [
                        'label'       => 'Addons',
                        'icon'        => 'icon-puzzle-piece',
                        'url'         => Backend::url('aero/clouds/addons'),
                        // 'permissions' => ['aero.clouds.access_addons']
                    ],
                    'faqs' => [
                        'label'       => 'FAQs',
                        'icon'        => 'icon-question-circle',
                        'url'         => Backend::url('aero/clouds/faqs'),
                        // 'permissions' => ['aero.clouds.access_faqs']
                    ],
                    'docs' => [
                        'label'       => 'Documentation',
                        'icon'        => 'icon-book',
                        'url'         => Backend::url('aero/clouds/docs'),
                        // 'permissions' => ['aero.clouds.access_docs']
                    ]
                ]
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'aero.clouds.access_services' => [
                'tab' => 'Clouds Hosting',
                'label' => 'Access Services'
            ],
            'aero.clouds.access_plans' => [
                'tab' => 'Clouds Hosting',
                'label' => 'Access Plans'
            ]
        ];
    }
}