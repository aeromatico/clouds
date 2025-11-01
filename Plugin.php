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
                    'domainproviders' => [
                        'label'       => 'Domain Providers',
                        'icon'        => 'icon-globe',
                        'url'         => Backend::url('aero/clouds/domainproviders'),
                        // 'permissions' => ['aero.clouds.access_domain_providers']
                    ],
                    'domainextensions' => [
                        'label'       => 'Domain Extensions',
                        'icon'        => 'icon-tag',
                        'url'         => Backend::url('aero/clouds/domainextensions'),
                        // 'permissions' => ['aero.clouds.access_domain_extensions']
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
                    ],
                    'domains' => [
                        'label'       => 'Domains',
                        'icon'        => 'icon-globe',
                        'url'         => Backend::url('aero/clouds/domains'),
                        // 'permissions' => ['aero.clouds.access_domains']
                    ],
                    'emails' => [
                        'label'       => 'Email Logs',
                        'icon'        => 'icon-envelope',
                        'url'         => Backend::url('aero/clouds/emaillogs'),
                        // 'permissions' => ['aero.clouds.manage_emails']
                    ]
                ]
            ],
            'clouds-support' => [
                'label'       => 'Clouds Support',
                'url'         => Backend::url('aero/clouds/tickets'),
                'icon'        => 'icon-headset',
                // 'permissions' => ['aero.clouds.support'],
                'order'       => 502,

                'sideMenu' => [
                    'support-tickets' => [
                        'label'       => 'Tickets',
                        'icon'        => 'icon-ticket',
                        'url'         => Backend::url('aero/clouds/tickets'),
                        // 'permissions' => ['aero.clouds.support']
                    ],
                    'support-departments' => [
                        'label'       => 'Departamentos',
                        'icon'        => 'icon-folder',
                        'url'         => Backend::url('aero/clouds/supportdepartments'),
                        // 'permissions' => ['aero.clouds.support']
                    ]
                ]
            ],
            'cloud-teams' => [
                'label'       => 'Cloud Teams',
                'url'         => Backend::url('aero/clouds/tasks'),
                'icon'        => 'icon-users',
                // 'permissions' => ['aero.clouds.access_tasks'],
                'order'       => 503,

                'sideMenu' => [
                    'tasks' => [
                        'label'       => 'Task Manager',
                        'icon'        => 'icon-check-square',
                        'url'         => Backend::url('aero/clouds/tasks'),
                        // 'permissions' => ['aero.clouds.access_tasks']
                    ],
                    'kanban' => [
                        'label'       => 'Kanban Board',
                        'icon'        => 'icon-th-large',
                        'url'         => Backend::url('aero/clouds/tasks/kanban'),
                        // 'permissions' => ['aero.clouds.access_tasks']
                    ],
                    'archived' => [
                        'label'       => 'Archived Tasks',
                        'icon'        => 'icon-archive',
                        'url'         => Backend::url('aero/clouds/tasks/archived'),
                        // 'permissions' => ['aero.clouds.access_tasks']
                    ],
                    'reports' => [
                        'label'       => 'Performance Reports',
                        'icon'        => 'icon-bar-chart',
                        'url'         => Backend::url('aero/clouds/tasks/reports'),
                        // 'permissions' => ['aero.clouds.access_tasks']
                    ]
                ]
            ],
            'clouds-setup' => [
                'label'       => 'Clouds Setup',
                'url'         => Backend::url('aero/clouds/settings'),
                'icon'        => 'icon-cog',
                // 'permissions' => ['aero.clouds.manage_settings'],
                'order'       => 504,

                'sideMenu' => [
                    'settings' => [
                        'label'       => 'Global Settings',
                        'icon'        => 'icon-sliders',
                        'url'         => Backend::url('aero/clouds/settings'),
                        // 'permissions' => ['aero.clouds.manage_settings']
                    ],
                    'email-events' => [
                        'label'       => 'Email Events',
                        'icon'        => 'icon-envelope-open',
                        'url'         => Backend::url('aero/clouds/emailevents'),
                        // 'permissions' => ['aero.clouds.manage_email_events']
                    ]
                ]
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            'Aero\Clouds\Components\InvoicePDF' => 'invoicePDF',
            'Aero\Clouds\Components\Cart' => 'cart',
            'Aero\Clouds\Components\Tickets' => 'tickets',
            'Aero\Clouds\Components\CloudsForm' => 'cloudsForm'
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('aero.sync-domain-pricing', \Aero\Clouds\Console\SyncDomainPricing::class);
        $this->registerConsoleCommand('aero.test-email-verification', \Aero\Clouds\Console\TestEmailVerification::class);
        $this->registerConsoleCommand('aero.test-registration', \Aero\Clouds\Console\TestRegistration::class);
        $this->registerConsoleCommand('aero.seed-email-events', \Aero\Clouds\Console\SeedEmailEvents::class);
        $this->registerConsoleCommand('aero.create-missing-tables', \Aero\Clouds\Console\CreateMissingTables::class);
        $this->registerConsoleCommand('aero.seed-mail-templates', \Aero\Clouds\Console\SeedMailTemplates::class);
        $this->registerConsoleCommand('aero.test-invoice-email', \Aero\Clouds\Console\TestInvoiceEmail::class);
        $this->registerConsoleCommand('aero.add-domain-columns', \Aero\Clouds\Console\AddDomainColumns::class);
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
            ],
            'aero.clouds.support' => [
                'tab' => 'Clouds Support',
                'label' => 'Manage Support Tickets'
            ],
            'aero.clouds.manage_emails' => [
                'tab' => 'Clouds Commerce',
                'label' => 'Manage Email Logs'
            ],
            'aero.clouds.access_tasks' => [
                'tab' => 'Cloud Teams',
                'label' => 'Manage Team Tasks'
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'user:invoice-paid' => 'rainlab.user::mail.invoice-paid',
            'backend:invoice-paid' => 'rainlab.user::mail.invoice-paid-admin',
        ];
    }

    public function boot()
    {
        // Cargar rutas de la API
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Send email verification after user registration
        Event::listen('rainlab.user.register', function ($component, $user) {
            \Log::info('Registration event fired for user: ' . ($user ? $user->email : 'null'));

            // Verificar que tengamos un usuario válido
            if (!$user || !is_object($user)) {
                \Log::error('Invalid user object in registration event');
                return;
            }

            // Send email verification notification
            if (!$user->hasVerifiedEmail()) {
                try {
                    // Set the verification URL to our custom page
                    $user->setUrlForEmailVerification(\Cms::url('/verify-email'));

                    // Send the verification email
                    $user->sendEmailVerificationNotification();

                    \Log::info('Email verification sent successfully to: ' . $user->email);

                    // Log in our email logs table
                    \Aero\Clouds\Models\EmailLog::create([
                        'template_code' => 'user:verify_email',
                        'recipient_email' => $user->email,
                        'recipient_name' => $user->full_name,
                        'user_id' => $user->id,
                        'data' => json_encode([
                            'first_name' => $user->first_name,
                            'email' => $user->email,
                        ]),
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    \Log::info('Email log saved for user: ' . $user->email);

                } catch (\Exception $e) {
                    \Log::error('Failed to send verification email: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            } else {
                \Log::info('User already verified, skipping verification email: ' . $user->email);
            }

            // Send welcome email to all new users
            try {
                $data = $user->getNotificationVars();
                $data['name'] = $user->full_name;

                \Mail::send('user:welcome_email', $data, function($message) use ($user) {
                    $message->to($user->email, $user->full_name);
                });

                \Log::info('Welcome email sent successfully to: ' . $user->email);

                // Log in our email logs table
                \Aero\Clouds\Models\EmailLog::create([
                    'template_code' => 'user:welcome_email',
                    'recipient_email' => $user->email,
                    'recipient_name' => $user->full_name,
                    'user_id' => $user->id,
                    'data' => json_encode([
                        'name' => $user->full_name,
                        'email' => $user->email,
                    ]),
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                \Log::info('Welcome email log saved for user: ' . $user->email);

            } catch (\Exception $e) {
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        });

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