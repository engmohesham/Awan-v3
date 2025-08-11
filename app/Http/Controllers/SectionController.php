<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Resources\SectionResource;

class SectionController extends Controller
{
    public function index()
    {
        $sections = Section::with('courses')->where('is_active', true)->get();
        return response()->json([
            'status' => 'success',
            'data' => SectionResource::collection($sections)
        ]);
    }

    public function show($id)
    {
        $section = Section::with('courses')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => new SectionResource($section)
        ]);
    }

    public function courses($id)
    {
        $section = Section::findOrFail($id);
        $courses = $section->courses()
            ->where('is_published', true)
            ->with(['lessons' => function($query) {
                $query->where('is_published', true)
                    ->orderBy('order');
            }])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $courses
        ]);
    }
}