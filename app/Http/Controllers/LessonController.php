<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\LessonResource;

class LessonController extends Controller
{
    public function show($courseId, $lessonId)
    {
        $course = Course::findOrFail($courseId);
        $lesson = $course->lessons()
            ->where('id', $lessonId)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if lesson is free or user has purchased the course
        if (!$lesson->is_free && !auth()->user()?->purchases()->where('course_id', $courseId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب شراء الدورة للوصول إلى هذا الدرس'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => new LessonResource($lesson)
        ]);
    }

    public function attachments($courseId, $lessonId)
    {
        $course = Course::findOrFail($courseId);
        $lesson = $course->lessons()
            ->where('id', $lessonId)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if lesson is free or user has purchased the course
        if (!$lesson->is_free && !auth()->user()?->purchases()->where('course_id', $courseId)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'يجب شراء الدورة للوصول إلى مرفقات هذا الدرس'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson->attachments
        ]);
    }
}