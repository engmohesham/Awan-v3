<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ProjectController;

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

// Projects Routes
Route::get('/projects', [ProjectController::class, 'index']);
// GET /api/projects?platform_type=web&project_type=ecommerce&country=مصر&search=متجر&sort_by=published_at&sort_direction=desc&per_page=10
Route::get('/projects/latest', [ProjectController::class, 'latest']);
Route::get('/projects/random', [ProjectController::class, 'random']);
Route::get('/projects/{slug}', [ProjectController::class, 'show']);
// GET /api/projects/my-awesome-project


// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);
    Route::get('/courses/{course}/lessons/{lesson}/attachments', [LessonController::class, 'attachments']);
});