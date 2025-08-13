<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'url',
        'country',
        'published_at',
        'platform_type',
        'project_type',
        'is_active',
    ];

    protected $casts = [
        'published_at' => 'date',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($project) {
            $project->slug = \Str::slug($project->name);
        });

        static::updating(function ($project) {
            if ($project->isDirty('name')) {
                $project->slug = \Str::slug($project->name);
            }
        });
    }
}
