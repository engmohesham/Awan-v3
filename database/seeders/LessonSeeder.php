<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        // الحصول على جميع الكورسات
        $courses = Course::all();

        foreach ($courses as $course) {
            // إنشاء دروس لكل كورس
            $lessons = [
                [
                    'title' => 'مقدمة ' . $course->title,
                    'description' => 'درس تمهيدي عن ' . $course->title,
                    'order' => 1,
                    'video_url' => 'https://www.youtube.com/watch?v=example1',
                    'is_free' => true,
                    'is_published' => true,
                    'attachments' => ['intro.pdf', 'notes.pdf'],
                ],
                [
                    'title' => 'أساسيات ' . $course->title,
                    'description' => 'تعلم أساسيات ' . $course->title,
                    'order' => 2,
                    'video_url' => 'https://www.youtube.com/watch?v=example2',
                    'is_free' => false,
                    'is_published' => true,
                    'attachments' => ['basics.pdf', 'exercises.pdf'],
                ],
                [
                    'title' => 'مستوى متقدم في ' . $course->title,
                    'description' => 'تعلم المستوى المتقدم في ' . $course->title,
                    'order' => 3,
                    'video_url' => 'https://www.youtube.com/watch?v=example3',
                    'is_free' => false,
                    'is_published' => true,
                    'attachments' => ['advanced.pdf', 'project.pdf'],
                ],
                [
                    'title' => 'مشروع عملي - ' . $course->title,
                    'description' => 'تطبيق عملي على ما تم تعلمه في ' . $course->title,
                    'order' => 4,
                    'video_url' => 'https://www.youtube.com/watch?v=example4',
                    'is_free' => false,
                    'is_published' => true,
                    'attachments' => ['project_guide.pdf', 'resources.pdf'],
                ],
                [
                    'title' => 'اختبار نهائي - ' . $course->title,
                    'description' => 'اختبار شامل لقياس مستوى التعلم في ' . $course->title,
                    'order' => 5,
                    'video_url' => 'https://www.youtube.com/watch?v=example5',
                    'is_free' => false,
                    'is_published' => false, // الاختبار غير منشور افتراضياً
                    'attachments' => ['exam.pdf', 'solutions.pdf'],
                ],
            ];

            // إنشاء الدروس للكورس الحالي
            foreach ($lessons as $lesson) {
                Lesson::create([
                    'course_id' => $course->id,
                    'title' => $lesson['title'],
                    'description' => $lesson['description'],
                    'order' => $lesson['order'],
                    'video_url' => $lesson['video_url'],
                    'is_free' => $lesson['is_free'],
                    'is_published' => $lesson['is_published'],
                    'attachments' => $lesson['attachments'],
                ]);
            }
        }
    }
}
