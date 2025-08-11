<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('sections')->insert([
            [
                'name' => 'البرمجة',
                'description' => 'قسم خاص بدورات البرمجة وتطوير البرمجيات.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التصميم',
                'description' => 'قسم خاص بدورات التصميم الجرافيكي وتصميم الويب.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التسويق',
                'description' => 'قسم خاص بدورات التسويق الرقمي وإدارة الحملات.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Programming',
                'description' => 'Programming and software development courses.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Design',
                'description' => 'Graphic and web design courses.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Marketing',
                'description' => 'Digital marketing and campaign management courses.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
