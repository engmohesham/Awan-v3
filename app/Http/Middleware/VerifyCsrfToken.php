<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '*'
    ];

    protected function shouldPassThrough($request)
    {
        return $request->attributes->get('skip-csrf', false) || parent::shouldPassThrough($request);
    }
}