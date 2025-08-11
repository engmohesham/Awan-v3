# Awan Platform

منصة تعليمية متكاملة تتيح للمدربين تقديم دوراتهم التدريبية وللمتعلمين الاستفادة منها.

## المميزات

- نظام إدارة متكامل للدورات والأقسام والدروس
- لوحة تحكم مميزة باستخدام Filament
- واجهة برمجة تطبيقات (API) كاملة
- نظام مصادقة آمن
- إدارة للمحتوى التعليمي والملفات المرفقة
- نظام للمدفوعات وشراء الدورات

## المتطلبات

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Node.js & NPM

## التثبيت

1. استنسخ المشروع:
```bash
git clone https://github.com/yourusername/awan-v3.git
cd awan-v3
```

2. قم بتثبيت اعتماديات PHP:
```bash
composer install
```

3. قم بنسخ ملف البيئة:
```bash
cp .env.example .env
```

4. قم بإنشاء مفتاح التطبيق:
```bash
php artisan key:generate
```

5. قم بتكوين قاعدة البيانات في ملف .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=awan_db
DB_USERNAME=root
DB_PASSWORD=
```

6. قم بتشغيل الترحيلات وزراعة البيانات:
```bash
php artisan migrate --seed
```

7. قم بتثبيت وبناء الأصول الأمامية:
```bash
npm install
npm run build
```

## الاستخدام

1. قم بتشغيل الخادم المحلي:
```bash
php artisan serve
```

2. قم بالوصول إلى لوحة التحكم:
```
http://localhost:8000/admin
```

بيانات الدخول الافتراضية:
- البريد الإلكتروني: admin@admin.com
- كلمة المرور: password

## واجهة برمجة التطبيقات (API)

تم توفير مجموعة Postman كاملة في ملف `Awan-Platform.postman_collection.json`.

النقاط النهائية الرئيسية:
- المصادقة: `/api/auth/*`
- الأقسام: `/api/sections/*`
- الدورات: `/api/courses/*`
- الدروس: `/api/courses/{course}/lessons/*`

## المساهمة

نرحب بمساهماتكم! يرجى اتباع الخطوات التالية:
1. قم بعمل Fork للمشروع
2. قم بإنشاء فرع للميزة: `git checkout -b feature/amazing-feature`
3. قم بإجراء تغييراتك: `git commit -m 'Add amazing feature'`
4. قم بدفع الفرع: `git push origin feature/amazing-feature`
5. قم بفتح طلب سحب

## الترخيص

هذا المشروع مرخص تحت [MIT License](LICENSE).