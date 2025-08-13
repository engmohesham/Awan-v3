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
            'image' => $this->image ? url('storage/' . $this->image) : null,
            'url' => $this->url,
            'description' => $this->description,
            'platform_type' => match($this->platform_type) {
                'web' => 'موقع ويب',
                'mobile' => 'تطبيق موبايل',
                'graphic' => 'تصميم جرافيك',
                'ai' => 'ذكاء اصطناعي',
                default => $this->platform_type,
            },
            'project_type' => match($this->project_type) {
                'entertainment' => 'ترفيهي',
                'commercial' => 'تجاري',
                'ecommerce' => 'متجر إلكتروني',
                'educational' => 'تعليمي',
                'social' => 'اجتماعي',
                'other' => 'آخر',
                default => $this->project_type,
            },
        ];
    }
}
