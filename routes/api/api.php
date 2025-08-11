<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PurchaseController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Public Routes
Route::get('/sections', [SectionController::class, 'index']);
Route::get('/sections/{id}', [SectionController::class, 'show']);
Route::get('/sections/{id}/courses', [SectionController::class, 'courses']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::get('/courses/{id}/lessons', [CourseController::class, 'lessons']);
Route::get('/courses/{id}/free-lessons', [CourseController::class, 'freeLessons']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/courses/{courseId}/lessons/{lessonId}', [LessonController::class, 'show']);
    Route::get('/courses/{courseId}/lessons/{lessonId}/attachments', [LessonController::class, 'attachments']);
    
    Route::post('/courses/{courseId}/purchase', [PurchaseController::class, 'store']);
    Route::get('/purchases', [PurchaseController::class, 'index']);
});
