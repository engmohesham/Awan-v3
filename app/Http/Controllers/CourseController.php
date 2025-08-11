<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\CourseResource;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with(['section', 'lessons' => function($query) {
            $query->where('is_published', true)->orderBy('order');
        }])
        ->where('is_published', true)
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => CourseResource::collection($courses)
        ]);
    }

    public function show($id)
    {
        $course = Course::with(['section', 'lessons' => function($query) {
            $query->where('is_published', true)->orderBy('order');
        }])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => new CourseResource($course)
        ]);
    }

    public function lessons($id)
    {
        $course = Course::findOrFail($id);
        $lessons = $course->lessons()
            ->where('is_published', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lessons
        ]);
    }

    public function freeLessons($id)
    {
        $course = Course::findOrFail($id);
        $lessons = $course->lessons()
            ->where('is_published', true)
            ->where('is_free', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lessons
        ]);
    }
}