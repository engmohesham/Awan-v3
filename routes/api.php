<?php

use Illuminate\Support\Facades\Route;

// Test route to verify API is working
Route::get('test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Authentication Routes
Route::post('auth/login', 'AuthController@login');
Route::post('auth/register', 'AuthController@register');
Route::middleware('auth:sanctum')->post('auth/logout', 'AuthController@logout');

// Public Routes
Route::get('sections', 'SectionController@index');
Route::get('sections/{section}', 'SectionController@show');
Route::get('sections/{section}/courses', 'SectionController@courses');

Route::get('courses', 'CourseController@index');
Route::get('courses/{course}', 'CourseController@show');
Route::get('courses/{course}/lessons', 'CourseController@lessons');
Route::get('courses/{course}/free-lessons', 'CourseController@freeLessons');

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('courses/{course}/lessons/{lesson}', 'LessonController@show');
    Route::get('courses/{course}/lessons/{lesson}/attachments', 'LessonController@attachments');
});