<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'url' => $this->url,
            'country' => $this->country,
            'published_at' => $this->published_at->format('Y-m-d'),
            'platform_type' => [
                'key' => $this->platform_type,
                'label' => match($this->platform_type) {
                    'web' => 'موقع ويب',
                    'mobile' => 'تطبيق موبايل',
                    'graphic' => 'تصميم جرافيك',
                    'ai' => 'ذكاء اصطناعي',
                    default => $this->platform_type,
                },
            ],
            'project_type' => [
                'key' => $this->project_type,
                'label' => match($this->project_type) {
                    'entertainment' => 'ترفيهي',
                    'commercial' => 'تجاري',
                    'ecommerce' => 'متجر إلكتروني',
                    'educational' => 'تعليمي',
                    'social' => 'اجتماعي',
                    'other' => 'آخر',
                    default => $this->project_type,
                },
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
