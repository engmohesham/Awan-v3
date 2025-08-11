<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // تنفيذ Super Admin Seeder أولاً لإنشاء الأدوار والصلاحيات
        $this->call(SuperAdminSeeder::class);

        // تنفيذ باقي السيدرز بالترتيب
        $this->call([
            UserSeeder::class,
            SectionSeeder::class,
            CourseSeeder::class,
        ]);
    }
}