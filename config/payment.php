<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Methods Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for payment methods
    |
    */

    'methods' => [
        'vodafone_cash' => [
            'name' => 'فودافون كاش',
            'phone_number' => '01234567890', // رقم فودافون كاش الخاص بكم
            'description' => 'قم بالتحويل إلى رقم فودافون كاش التالي',
            'enabled' => true,
        ],
        'instapay' => [
            'name' => 'إنستا باي',
            'username' => '@your_instapay_username', // يوزر إنستا باي الخاص بكم
            'description' => 'قم بالتحويل إلى يوزر إنستا باي التالي',
            'enabled' => true,
        ],
    ],

    'order' => [
        'expiration_hours' => 24, // مدة صلاحية الطلب بالساعات
    ],

    'uploads' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'path' => 'payment-proofs',
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

    'notifications' => [
        'admin_email' => 'admin@example.com',
        'admin_phone' => '01234567890',
    ],
];

