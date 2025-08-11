<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('users')->insert([
            [
                'name' => 'مدير النظام',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'language' => 'ar',
                'session_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                // 'role' => 'admin', // Uncomment if you add a role column
            ],
            [
                'name' => 'User Test',
                'email' => 'user@example.com',
                'password' => bcrypt('password'),
                'language' => 'en',
                'session_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                // 'role' => 'user', // Uncomment if you add a role column
            ],
        ]);
    }
}
