<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'api/*',
        'admin/login', // إضافة مسار تسجيل الدخول
        'admin/*', // إضافة جميع مسارات لوحة التحكم
    ];
}