<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مستخدم مشترك في كورس
        $subscribedUser = User::create([
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0123456789',
        ]);

        // إنشاء مستخدم غير مشترك
        $nonSubscribedUser = User::create([
            'name' => 'محمد علي',
            'email' => 'mohamed@example.com',
            'password' => Hash::make('password123'),
            'phone' => '0987654321',
        ]);

        // الحصول على بعض الكورسات لإضافة المشتريات
        $courses = Course::take(3)->get();

        // إنشاء مشتريات للمستخدم المشترك
        foreach ($courses as $index => $course) {
            Purchase::create([
                'user_id' => $subscribedUser->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'approved',
                'proof_image_path' => 'payments/proof' . ($index + 1) . '.jpg',
                'sender_name_or_phone' => 'أحمد محمد - 0123456789',
            ]);
        }
    }
}
