<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($section) {
            $section->slug = \Str::slug($section->name);
        });

        static::updating(function ($section) {
            if ($section->isDirty('name')) {
                $section->slug = \Str::slug($section->name);
            }
        });
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
} 