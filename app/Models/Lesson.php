<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $casts = [
        'attachments' => 'array',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
        'video_url',
        'is_free',
        'is_published',
        'attachments',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
} 