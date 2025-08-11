<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'is_published' => $this->is_published,
            'section' => new SectionResource($this->whenLoaded('section')),
            'lessons_count' => $this->whenLoaded('lessons', function() {
                return $this->lessons->where('is_published', true)->count();
            }),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'thumbnail' => $this->thumbnail,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}