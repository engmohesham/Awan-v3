<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'name' => 'البرمجة',
                'description' => 'قسم خاص بدورات البرمجة وتطوير البرمجيات.',
                'is_active' => true,
            ],
            [
                'name' => 'التصميم',
                'description' => 'قسم خاص بدورات التصميم الجرافيكي وتصميم الويب.',
                'is_active' => true,
            ],
            [
                'name' => 'التسويق',
                'description' => 'قسم خاص بدورات التسويق الرقمي وإدارة الحملات.',
                'is_active' => true,
            ],
            [
                'name' => 'Programming',
                'description' => 'Programming and software development courses.',
                'is_active' => true,
            ],
            [
                'name' => 'Design',
                'description' => 'Graphic and web design courses.',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital marketing and campaign management courses.',
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            Section::create([
                'name' => $section['name'],
                'slug' => Str::slug($section['name']),
                'description' => $section['description'],
                'is_active' => $section['is_active'],
            ]);
        }
    }
}