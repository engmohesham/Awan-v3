<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for various payment gateways
    | used in the application.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'paytabs'),

    'gateways' => [
        'paytabs' => [
            'profile_id' => env('PAYTABS_PROFILE_ID'),
            'server_key' => env('PAYTABS_SERVER_KEY'),
            'client_key' => env('PAYTABS_CLIENT_KEY'),
            'base_url' => env('PAYTABS_BASE_URL', 'https://secure.paytabs.com'),
            'currency' => 'EGP',
            'language' => 'ar',
        ],

        'fawry' => [
            'merchant_code' => env('FAWRY_MERCHANT_CODE'),
            'security_key' => env('FAWRY_SECURITY_KEY'),
            'base_url' => env('FAWRY_BASE_URL', 'https://www.atfawry.com'),
            'currency' => 'EGP',
        ],

        'vodafone_cash' => [
            'merchant_id' => env('VODAFONE_MERCHANT_ID'),
            'api_key' => env('VODAFONE_API_KEY'),
            'base_url' => env('VODAFONE_BASE_URL', 'https://api.vodafone.com.eg'),
            'currency' => 'EGP',
        ],

        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => 'EGP',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods and their configurations
    |
    */

    'methods' => [
        'card' => [
            'name' => 'بطاقة ائتمان',
            'gateways' => ['paytabs', 'stripe'],
            'requires_gateway' => true,
        ],

        'vodafone_cash' => [
            'name' => 'فودافون كاش',
            'gateways' => ['vodafone_cash'],
            'requires_gateway' => true,
        ],

        'bank_transfer' => [
            'name' => 'تحويل بنكي',
            'gateways' => [],
            'requires_gateway' => false,
            'requires_proof' => true,
        ],

        'cash' => [
            'name' => 'دفع نقدي',
            'gateways' => [],
            'requires_gateway' => false,
            'requires_manual_verification' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Configuration
    |--------------------------------------------------------------------------
    |
    | Order-related settings
    |
    */

    'order' => [
        'expiration_hours' => env('ORDER_EXPIRATION_HOURS', 24),
        'currency' => 'EGP',
        'number_prefix' => 'ORD',
        'auto_expire' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for payment proof uploads
    |
    */

    'uploads' => [
        'disk' => 'public',
        'path' => 'payment-proofs',
        'max_size' => 2048, // KB
        'allowed_types' => ['jpeg', 'png', 'jpg'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Payment notification configurations
    |
    */

    'notifications' => [
        'email' => [
            'enabled' => env('PAYMENT_EMAIL_NOTIFICATIONS', true),
            'admin_email' => env('ADMIN_EMAIL'),
        ],
        'sms' => [
            'enabled' => env('PAYMENT_SMS_NOTIFICATIONS', false),
            'provider' => env('SMS_PROVIDER'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configurations
    |
    */

    'security' => [
        'verify_signature' => env('VERIFY_PAYMENT_SIGNATURE', true),
        'allowed_ips' => env('PAYMENT_ALLOWED_IPS', ''),
        'webhook_timeout' => 30, // seconds
    ],
];

