<?php namespace Aero\Clouds;

use System\Classes\PluginBase;
use Backend;
use Event;
use Aero\Clouds\Models\ActivityLog;

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
                'label'       => 'Clouds Manager',
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
                    ],
                    'activitylogs' => [
                        'label'       => 'Activity Logs',
                        'icon'        => 'icon-history',
                        'url'         => Backend::url('aero/clouds/activitylogs'),
                        // 'permissions' => ['aero.clouds.access_logs']
                    ]
                ]
            ],
            'commerce' => [
                'label'       => 'Cloud Commerce',
                'url'         => Backend::url('aero/clouds/orders'),
                'icon'        => 'icon-shopping-cart',
                // 'permissions' => ['aero.clouds.access_commerce'],
                'order'       => 501,

                'sideMenu' => [
                    'orders' => [
                        'label'       => 'Orders',
                        'icon'        => 'icon-file-text',
                        'url'         => Backend::url('aero/clouds/orders'),
                        // 'permissions' => ['aero.clouds.access_orders']
                    ],
                    'invoices' => [
                        'label'       => 'Invoices',
                        'icon'        => 'icon-file-invoice',
                        'url'         => Backend::url('aero/clouds/invoices'),
                        // 'permissions' => ['aero.clouds.access_invoices']
                    ],
                    'paymentgateways' => [
                        'label'       => 'Payment Gateways',
                        'icon'        => 'icon-credit-card',
                        'url'         => Backend::url('aero/clouds/paymentgateways'),
                        // 'permissions' => ['aero.clouds.access_payment_gateways']
                    ],
                    'cloudservers' => [
                        'label'       => 'Cloud Servers',
                        'icon'        => 'icon-server',
                        'url'         => Backend::url('aero/clouds/clouds'),
                        // 'permissions' => ['aero.clouds.access_cloud_servers']
                    ]
                ]
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            'Aero\Clouds\Components\InvoicePDF' => 'invoicePDF'
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

    public function boot()
    {
        // Log backend user authentication events
        Event::listen('backend.user.login', function ($user) {
            ActivityLog::create([
                'log_name' => 'authentication',
                'description' => 'User logged in',
                'causer_type' => get_class($user),
                'causer_id' => $user->id,
                'event' => 'login',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'properties' => [
                    'user_email' => $user->email,
                    'login_time' => now()->toDateTimeString()
                ]
            ]);
        });

        Event::listen('backend.user.logout', function ($user) {
            ActivityLog::create([
                'log_name' => 'authentication',
                'description' => 'User logged out',
                'causer_type' => get_class($user),
                'causer_id' => $user->id,
                'event' => 'logout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'properties' => [
                    'user_email' => $user->email,
                    'logout_time' => now()->toDateTimeString()
                ]
            ]);
        });

        // Log file uploads
        Event::listen('system.file.upload', function ($path, $file) {
            ActivityLog::create([
                'log_name' => 'file_operations',
                'description' => 'File uploaded',
                'event' => 'file_upload',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'properties' => [
                    'path' => $path,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType()
                ]
            ]);
        });
    }
}