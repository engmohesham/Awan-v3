<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()->where('is_active', true);

        // Filter by platform type
        if ($request->has('platform_type')) {
            $query->where('platform_type', $request->platform_type);
        }

        // Filter by project type
        if ($request->has('project_type')) {
            $query->where('project_type', $request->project_type);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', 'like', '%' . $request->country . '%');
        }

        // Search by name or description
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortField = $request->get('sort_by', 'published_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $projects = $query->paginate($perPage);

        return [
            'projects' => ProjectResource::collection($projects->items()),
            'total' => $projects->total(),
        ];
    }

    public function show($slug)
    {
        $project = Project::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return ['project' => new ProjectResource($project)];
    }

    public function latest()
    {
        $projects = Project::where('is_active', true)
            ->orderBy('published_at', 'desc')
            ->take(6)
            ->get();

        return ['projects' => ProjectResource::collection($projects)];
    }

    public function random()
    {
        $projects = Project::where('is_active', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return ['projects' => ProjectResource::collection($projects)];
    }
}
