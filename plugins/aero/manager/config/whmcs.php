<?php
return [
    // Configuración de conexión WHMCS
    'url' => env('WHMCS_URL', 'https://my.clouds.com.bo/api_october.php'),
    'identifier' => '', // No necesario para localAPI
    'secret' => '', // No necesario para localAPI
    'api_token' => '5096eff26bd565cab693db213b9fce88b8e3d124cbcc3c85f20c4a2d7022f38d',
    'debug' => env('WHMCS_DEBUG', false),
    
    // Configuración de cache
    'cache' => [
        'enabled' => true,
        'ttl' => 300, // 5 minutos
        'prefix' => 'whmcs_'
    ],
    
    // Configuración de retry
    'retry' => [
        'attempts' => 3,
        'delay' => 1 // segundos
    ],
    
    // Rate limiting
    'rate_limit' => [
        'max_requests_per_minute' => 60,
        'enabled' => true
    ],
    
    // Timeouts
    'timeout' => 30,
    
    // Mapeo de campos
    'field_mapping' => [
        'client' => [
            'first_name' => 'firstname',
            'last_name' => 'lastname',
            'email' => 'email',
            'phone' => 'phonenumber',
            'address' => 'address1',
            'city' => 'city',
            'state' => 'state',
            'postal_code' => 'postcode',
            'country' => 'country'
        ]
    ],
    
    // Departamentos de tickets por defecto
    'ticket_departments' => [
        'general' => 1,
        'technical' => 2,
        'billing' => 3,
        'sales' => 4
    ],
    
    // Configuración de facturación
    'billing' => [
        'default_currency' => 1, // ID de moneda en WHMCS
        'default_payment_method' => '',
        'auto_apply_credit' => false,
        'send_invoice_email' => true
    ]
];