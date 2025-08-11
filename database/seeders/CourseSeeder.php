<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('courses')->insert([
            [
                'section_id' => 1,
                'title' => 'مقدمة في البرمجة',
                'slug' => 'intro-to-programming-ar',
                'description' => 'تعلم أساسيات البرمجة باستخدام لغة بايثون.',
                'price' => 100.00,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_id' => 2,
                'title' => 'أساسيات التصميم الجرافيكي',
                'slug' => 'graphic-design-basics-ar',
                'description' => 'دورة شاملة في مبادئ التصميم الجرافيكي.',
                'price' => 120.00,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_id' => 3,
                'title' => 'مبادئ التسويق الرقمي',
                'slug' => 'digital-marketing-ar',
                'description' => 'تعلم استراتيجيات التسويق الرقمي الحديثة.',
                'price' => 90.00,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_id' => 4,
                'title' => 'Introduction to Programming',
                'slug' => 'intro-to-programming-en',
                'description' => 'Learn programming basics with Python.',
                'price' => 100.00,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_id' => 5,
                'title' => 'Graphic Design Basics',
                'slug' => 'graphic-design-basics-en',
                'description' => 'Comprehensive course on graphic design principles.',
                'price' => 120.00,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section_id' => 6,
                'title' => 'Digital Marketing Principles',
                'slug' => 'digital-marketing-en',
                'description' => 'Learn modern digital marketing strategies.',
                'price' => 90.00,
                'is_published' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
