<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'slug',
        'description',
        'price',
        'is_published',
        'admin_id',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($course) {
            $course->slug = \Str::slug($course->title);
        });

        static::updating(function ($course) {
            if ($course->isDirty('title')) {
                $course->slug = \Str::slug($course->title);
            }
        });
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
} 