<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

// Test route
Route::get('/test', function() {
    return response()->json(['message' => 'Test route works!']);
});