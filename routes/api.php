<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SectionController;

// API Routes
// Test route to verify API is working
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Authentication Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
});

// Public Routes
Route::get('/sections', [SectionController::class, 'index']);
Route::get('/sections/{section}', [SectionController::class, 'show']);
Route::get('/sections/{section}/courses', [SectionController::class, 'courses']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::get('/courses/{course}/lessons', [CourseController::class, 'lessons']);
Route::get('/courses/{course}/free-lessons', [CourseController::class, 'freeLessons']);

// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);
    Route::get('/courses/{course}/lessons/{lesson}/attachments', [LessonController::class, 'attachments']);
});