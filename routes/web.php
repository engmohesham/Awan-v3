<?php

use Illuminate\Support\Facades\Route;

// Web routes only for Filament admin panel
Route::get('/', function () {
    return redirect('/admin');
});