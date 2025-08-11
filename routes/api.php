<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SectionController;

// Authentication Routes
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

// Public Routes
Route::get('sections', [SectionController::class, 'index']);
Route::get('sections/{section}', [SectionController::class, 'show']);
Route::get('sections/{section}/courses', [SectionController::class, 'courses']);

Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{course}', [CourseController::class, 'show']);
Route::get('courses/{course}/lessons', [CourseController::class, 'lessons']);
Route::get('courses/{course}/free-lessons', [CourseController::class, 'freeLessons']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show']);
    Route::get('courses/{course}/lessons/{lesson}/attachments', [LessonController::class, 'attachments']);
});