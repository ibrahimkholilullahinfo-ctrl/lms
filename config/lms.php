<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'AEC LMS'),
    'support_phone' => '+8801761957584',
    'support_email' => env('LMS_SUPPORT_EMAIL', 'info@aeclms.com'),
    'address_bn' => 'ইসিবি চত্বর, ঢাকা।',

    /*
    |--------------------------------------------------------------------------
    | Multi-tenancy
    |--------------------------------------------------------------------------
    | The central domain serves the default institute website. Institute admins
    | may attach a subdomain ({subdomain}.CENTRAL_DOMAIN) or a fully custom
    | domain. Tenant resolution happens in App\Http\Middleware\ResolveTenant.
    */
    'central_domain' => env('CENTRAL_DOMAIN', 'localhost'),
    'default_institute_slug' => env('LMS_DEFAULT_INSTITUTE', 'aec'),

    'locales' => [
        'en' => ['label' => 'English', 'native' => 'English'],
        'bn' => ['label' => 'Bengali', 'native' => 'বাংলা'],
    ],

    'supported_locales' => explode(',', env('APP_LOCALES', 'en,bn')),

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */
    'roles' => [
        'super_admin' => 'Super Admin',
        'institute_admin' => 'Institute Admin',
        'teacher' => 'Teacher',
        'student' => 'Student',
        'guardian' => 'Guardian',
    ],

    'panel_prefix' => [
        'super_admin' => 'super-admin',
        'institute_admin' => 'admin',
        'teacher' => 'teacher',
        'student' => 'student',
        'guardian' => 'guardian',
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin / integration defaults
    |--------------------------------------------------------------------------
    */
    'plugin_types' => [
        'payment' => 'Payment Gateway',
        'live_class' => 'Live Class Provider',
    ],

    'payment_currency' => 'BDT',

    /*
    |--------------------------------------------------------------------------
    | Attendance / academic defaults
    |--------------------------------------------------------------------------
    */
    'attendance_statuses' => ['present', 'absent', 'late', 'leave'],
    'enrollment_statuses' => ['pending', 'active', 'completed', 'cancelled'],
    'class_modes' => ['online', 'offline', 'hybrid'],

    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
];
